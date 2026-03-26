<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class LibraryController extends BaseController
{
    public function index(): void
    {
        $this->authorize('library.view');

        // Self-healing migration
        try {
            $db = db();
            $db->query("SHOW COLUMNS FROM library_books LIKE 'status'");
            if (!$db->fetch()) {
                $db->query("ALTER TABLE library_books ADD COLUMN status ENUM('active', 'inactive', 'disposed') NOT NULL DEFAULT 'active' AFTER available_copies");
            }
        } catch (\Exception $e) {}

        $institutionId = session('institution_id');
        $search = $this->input('search', '');
        
        $where = "institution_id = ?";
        $params = [$institutionId];

        if ($search) {
            $where .= " AND (title LIKE ? OR author LIKE ? OR isbn LIKE ? OR publisher LIKE ? OR category LIKE ?)";
            $term = "%{$search}%";
            $params = array_merge($params, [$term, $term, $term, $term, $term]);
        }

        $page = (int)($this->input('page') ?: 1);
        $sql = "SELECT * FROM library_books WHERE {$where} ORDER BY title";
        $books = db()->paginate($sql, $params, $page, config('app.per_page', 15));

        $this->view('library/index', compact('books', 'search'));
    }

    public function create(): void
    {
        $this->authorize('library.manage');
        $this->view('library/create');
    }

    public function store(): void
    {
        $this->authorize('library.manage');

        $data = $this->postData();
        $errors = $this->validate($data, [
            'title' => 'required',
            'total_copies' => 'required|numeric'
        ]);

        if ($errors) {
            $this->backWithErrors($errors);
            return;
        }

        $copies = (int)$data['total_copies'];

        $id = db()->insert('library_books', [
            'institution_id'   => session('institution_id'),
            'title'            => sanitize($data['title']),
            'author'           => sanitize($data['author'] ?? ''),
            'isbn'             => sanitize($data['isbn'] ?? ''),
            'publisher'        => sanitize($data['publisher'] ?? ''),
            'category'         => sanitize($data['category'] ?? ''),
            'total_copies'     => $copies,
            'available_copies' => $copies,
            'status'           => $data['status'] ?? 'active'
        ]);

        $this->logAudit('library_book_added', 'library_book', $id);
        $this->redirectWith('library', 'Book added to library successfully.', 'success');
    }

    public function issues(): void
    {
        $this->authorize('library.issue');

        $institutionId = session('institution_id');
        $status = $this->input('status', 'issued');

        $where = "b.institution_id = ?";
        $params = [$institutionId];

        if ($status !== 'all') {
            $where .= " AND li.status = ?";
            $params[] = $status;
        }

        // Fetch issues joining books and students.
        $page = (int)($this->input('page') ?: 1);
        
        $sql = "
            SELECT li.*, b.title, b.isbn,
            (SELECT CONCAT(first_name, ' ', last_name, ' (', student_id_number, ')') FROM students WHERE id = li.student_id) as borrower_name
            FROM library_issues li
            JOIN library_books b ON b.id = li.book_id
            WHERE {$where}
            ORDER BY li.issue_date DESC, li.id DESC
        ";
        
        $issues = db()->paginate($sql, $params, $page, config('app.per_page', 20));

        // Get books with available copies for new issue form
        $books = db()->query("SELECT id, title, available_copies FROM library_books WHERE institution_id = ? AND status = 'active' AND available_copies > 0 ORDER BY title", [$institutionId])->fetchAll();

        $this->view('library/issues', compact('issues', 'status', 'books'));
    }

    public function storeIssue(): void
    {
        $this->authorize('library.issue');

        $data = $this->postData();
        $errors = $this->validate($data, [
            'book_id' => 'required|numeric',
            'student_id' => 'required|numeric',
            'due_date' => 'required|date'
        ]);

        if ($errors) {
            $this->backWithErrors($errors);
            return;
        }

        $bookId = (int)$data['book_id'];

        // Verify availability
        $book = db()->query("SELECT available_copies FROM library_books WHERE id = ? FOR UPDATE", [$bookId])->fetch();
        if (!$book || $book['available_copies'] <= 0) {
            $this->backWithErrors(['error' => 'Book not available for issuance.']);
            return;
        }

        db()->beginTransaction();
        try {
            $id = db()->insert('library_issues', [
                'book_id'    => $bookId,
                'student_id' => $data['student_id'],
                'issue_date' => date('Y-m-d'),
                'due_date'   => $data['due_date'],
                'status'     => 'issued',
                'issued_by'  => auth()['id']
            ]);

            db()->query("UPDATE library_books SET available_copies = available_copies - 1 WHERE id = ?", [$bookId]);
            db()->commit();

            $this->logAudit('library_book_issued', 'library_issue', $id);
            $this->redirectWith('library/issues', 'Book issued successfully.', 'success');

        } catch (\Exception $e) {
            db()->rollBack();
            $this->backWithErrors(['error' => 'Failed to issue book.']);
        }
    }

    public function processReturn(int $issueId): void
    {
        $this->authorize('library.issue');

        $data = $this->postData();
        $issue = db()->query("SELECT * FROM library_issues WHERE id = ?", [$issueId])->fetch();
        
        if (!$issue || $issue['status'] !== 'issued') {
            $this->backWithErrors(['error' => 'Invalid or already processed issue record.']);
            return;
        }

        $status = $data['status'] ?? 'returned'; // returned or lost
        $fine = (float)($data['fine_amount'] ?? 0);

        db()->beginTransaction();
        try {
            db()->update('library_issues', [
                'return_date' => date('Y-m-d'),
                'fine_amount' => $fine,
                'status'      => $status
            ], '`id` = ?', [$issueId]);

            // If returned, increment available copies
            // If lost, total capacity effectively reduces, though we can decrement total_copies inside the book, or leave available copies as is
            if ($status === 'returned') {
                db()->query("UPDATE library_books SET available_copies = available_copies + 1 WHERE id = ?", [$issue['book_id']]);
            } else if ($status === 'lost') {
                db()->query("UPDATE library_books SET total_copies = total_copies - 1 WHERE id = ?", [$issue['book_id']]);
            }

            db()->commit();
            $this->logAudit("library_book_{$status}", 'library_issue', $issueId);
            $this->backWithSuccess('Book return processed.');

        } catch (\Exception $e) {
            db()->rollBack();
            $this->backWithErrors(['error' => 'Failed to process return.']);
        }
    }
}

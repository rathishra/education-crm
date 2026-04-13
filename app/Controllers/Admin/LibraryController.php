<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class LibraryController extends BaseController
{
    // ─── Books Index ───────────────────────────────────────────────────────────

    public function index(): void
    {
        $this->authorize('library.view');

        $institutionId = $this->institutionId;
        $search   = sanitize($_GET['search']   ?? '');
        $category = sanitize($_GET['category'] ?? '');
        $status   = sanitize($_GET['status']   ?? '');

        $where  = ['b.institution_id = ?', 'b.deleted_at IS NULL'];
        $params = [$institutionId];

        if ($search !== '') {
            $where[] = '(b.title LIKE ? OR b.author LIKE ? OR b.accession_number LIKE ? OR b.isbn LIKE ?)';
            $like = '%' . $search . '%';
            array_push($params, $like, $like, $like, $like);
        }
        if ($category !== '') {
            $where[]  = 'b.category = ?';
            $params[] = $category;
        }
        if ($status !== '') {
            $where[]  = 'b.status = ?';
            $params[] = $status;
        }

        $whereClause = implode(' AND ', $where);

        $books = db()->query("
            SELECT b.*,
                   (b.quantity - b.available_quantity) AS issued_count
            FROM books b
            WHERE {$whereClause}
            ORDER BY b.title ASC
        ", $params)->fetchAll();

        // KPI stats
        $stats = db()->query("
            SELECT
                COUNT(*)             AS total_books,
                SUM(quantity)        AS total_copies,
                SUM(available_quantity) AS available_copies,
                COUNT(DISTINCT category) AS total_categories
            FROM books
            WHERE institution_id = ? AND deleted_at IS NULL
        ", [$institutionId])->fetch();

        $overdueRow = db()->query("
            SELECT COUNT(*) AS overdue_count
            FROM issued_books
            WHERE institution_id = ? AND status = 'issued' AND due_date < CURDATE()
        ", [$institutionId])->fetch();

        $stats['overdue_count'] = $overdueRow['overdue_count'] ?? 0;

        $categories = db()->query("
            SELECT DISTINCT category FROM books
            WHERE institution_id = ? AND category IS NOT NULL AND deleted_at IS NULL
            ORDER BY category
        ", [$institutionId])->fetchAll();

        $this->view('library/index', compact('books', 'stats', 'categories', 'search', 'category', 'status'));
    }

    // ─── Add Book Form ─────────────────────────────────────────────────────────

    public function create(): void
    {
        $this->authorize('library.manage');
        $this->view('library/form', ['book' => null, 'editMode' => false]);
    }

    // ─── Store New Book ────────────────────────────────────────────────────────

    public function store(): void
    {
        $this->authorize('library.manage');
        verifyCsrf();

        $data   = $this->postData();
        $errors = $this->validate($data, [
            'title'            => 'required',
            'author'           => 'required',
            'accession_number' => 'required',
            'quantity'         => 'required|numeric',
        ]);

        if ($errors) {
            $this->backWithErrors($errors);
            return;
        }

        $qty = max(1, (int)$data['quantity']);

        $id = db()->insert('books', [
            'institution_id'     => $this->institutionId,
            'accession_number'   => sanitize($data['accession_number']),
            'title'              => sanitize($data['title']),
            'author'             => sanitize($data['author']),
            'isbn'               => sanitize($data['isbn'] ?? ''),
            'category'           => sanitize($data['category'] ?? ''),
            'publisher'          => sanitize($data['publisher'] ?? ''),
            'edition'            => sanitize($data['edition'] ?? ''),
            'publication_year'   => !empty($data['publication_year']) ? (int)$data['publication_year'] : null,
            'quantity'           => $qty,
            'available_quantity' => $qty,
            'shelf_location'     => sanitize($data['shelf_location'] ?? ''),
            'language'           => sanitize($data['language'] ?? 'English'),
            'price'              => (float)($data['price'] ?? 0),
            'description'        => sanitize($data['description'] ?? ''),
            'status'             => 'active',
        ]);

        $this->logAudit('book_added', 'library', $id);
        $this->redirectWith(url('library'), 'success', 'Book "' . sanitize($data['title']) . '" added successfully.');
    }

    // ─── Edit Book Form ────────────────────────────────────────────────────────

    public function edit(int $id): void
    {
        $this->authorize('library.manage');

        $book = db()->query(
            "SELECT * FROM books WHERE id = ? AND institution_id = ? AND deleted_at IS NULL",
            [$id, $this->institutionId]
        )->fetch();

        if (!$book) {
            $this->redirectWith(url('library'), 'error', 'Book not found.');
            return;
        }

        $this->view('library/form', ['book' => $book, 'editMode' => true]);
    }

    // ─── Update Book ───────────────────────────────────────────────────────────

    public function update(int $id): void
    {
        $this->authorize('library.manage');
        verifyCsrf();

        $book = db()->query(
            "SELECT * FROM books WHERE id = ? AND institution_id = ? AND deleted_at IS NULL",
            [$id, $this->institutionId]
        )->fetch();

        if (!$book) {
            $this->redirectWith(url('library'), 'error', 'Book not found.');
            return;
        }

        $data   = $this->postData();
        $errors = $this->validate($data, [
            'title'            => 'required',
            'author'           => 'required',
            'accession_number' => 'required',
            'quantity'         => 'required|numeric',
        ]);

        if ($errors) {
            $this->backWithErrors($errors);
            return;
        }

        $newQty    = max(1, (int)$data['quantity']);
        $issuedNow = (int)$book['quantity'] - (int)$book['available_quantity'];
        $newAvail  = max(0, $newQty - $issuedNow);

        db()->update('books', [
            'accession_number'   => sanitize($data['accession_number']),
            'title'              => sanitize($data['title']),
            'author'             => sanitize($data['author']),
            'isbn'               => sanitize($data['isbn'] ?? ''),
            'category'           => sanitize($data['category'] ?? ''),
            'publisher'          => sanitize($data['publisher'] ?? ''),
            'edition'            => sanitize($data['edition'] ?? ''),
            'publication_year'   => !empty($data['publication_year']) ? (int)$data['publication_year'] : null,
            'quantity'           => $newQty,
            'available_quantity' => $newAvail,
            'shelf_location'     => sanitize($data['shelf_location'] ?? ''),
            'language'           => sanitize($data['language'] ?? 'English'),
            'price'              => (float)($data['price'] ?? 0),
            'description'        => sanitize($data['description'] ?? ''),
            'status'             => sanitize($data['status'] ?? 'active'),
        ], 'id = ? AND institution_id = ?', [$id, $this->institutionId]);

        $this->logAudit('book_updated', 'library', $id);
        $this->redirectWith(url('library'), 'success', 'Book updated successfully.');
    }

    // ─── Soft Delete ───────────────────────────────────────────────────────────

    public function delete(int $id): void
    {
        $this->authorize('library.manage');
        verifyCsrf();

        $issued = db()->query(
            "SELECT COUNT(*) AS cnt FROM issued_books WHERE book_id = ? AND status = 'issued'",
            [$id]
        )->fetch();

        if ((int)($issued['cnt'] ?? 0) > 0) {
            $this->redirectWith(url('library'), 'error', 'Cannot delete — this book has copies currently issued.');
            return;
        }

        db()->update('books',
            ['deleted_at' => date('Y-m-d H:i:s'), 'status' => 'inactive'],
            'id = ? AND institution_id = ?',
            [$id, $this->institutionId]
        );

        $this->logAudit('book_deleted', 'library', $id);
        $this->redirectWith(url('library'), 'success', 'Book removed from library.');
    }

    // ─── Issues List ───────────────────────────────────────────────────────────

    public function issues(): void
    {
        $this->authorize('library.view');

        $institutionId = $this->institutionId;
        $filter        = sanitize($_GET['filter'] ?? 'all');

        $whereStatus = match($filter) {
            'issued'   => "AND ib.status = 'issued'",
            'overdue'  => "AND ib.status = 'issued' AND ib.due_date < CURDATE()",
            'returned' => "AND ib.status = 'returned'",
            default    => '',
        };

        $issues = db()->query("
            SELECT ib.*,
                   b.title AS book_title,
                   b.accession_number,
                   s.first_name, s.last_name, s.student_id_number,
                   DATEDIFF(CURDATE(), ib.due_date) AS days_overdue
            FROM issued_books ib
            JOIN books    b ON b.id = ib.book_id
            JOIN students s ON s.id = ib.student_id
            WHERE ib.institution_id = ? {$whereStatus}
            ORDER BY ib.created_at DESC
            LIMIT 200
        ", [$institutionId])->fetchAll();

        $availableBooks = db()->query("
            SELECT id, accession_number, title, author, available_quantity
            FROM books
            WHERE institution_id = ? AND available_quantity > 0
              AND status = 'active' AND deleted_at IS NULL
            ORDER BY title
        ", [$institutionId])->fetchAll();

        $this->view('library/issues', compact('issues', 'availableBooks', 'filter'));
    }

    // ─── Issue a Book ──────────────────────────────────────────────────────────

    public function storeIssue(): void
    {
        $this->authorize('library.manage');
        verifyCsrf();

        $data   = $this->postData();
        $errors = $this->validate($data, [
            'book_id'    => 'required|numeric',
            'student_id' => 'required|numeric',
            'due_date'   => 'required',
        ]);

        if ($errors) {
            $this->backWithErrors($errors);
            return;
        }

        $bookId    = (int)$data['book_id'];
        $studentId = (int)$data['student_id'];

        $book = db()->query(
            "SELECT id, title, available_quantity FROM books
             WHERE id = ? AND institution_id = ? AND available_quantity > 0 AND status = 'active'",
            [$bookId, $this->institutionId]
        )->fetch();

        if (!$book) {
            $this->redirectWith(url('library/issues'), 'error', 'Book is not available for issue.');
            return;
        }

        $already = db()->query(
            "SELECT id FROM issued_books WHERE book_id = ? AND student_id = ? AND status = 'issued'",
            [$bookId, $studentId]
        )->fetch();

        if ($already) {
            $this->redirectWith(url('library/issues'), 'error', 'This student already has a copy of this book.');
            return;
        }

        $id = db()->insert('issued_books', [
            'institution_id' => $this->institutionId,
            'book_id'        => $bookId,
            'student_id'     => $studentId,
            'issued_by'      => $this->user['id'] ?? 0,
            'issued_date'    => date('Y-m-d'),
            'due_date'       => sanitize($data['due_date']),
            'fine_per_day'   => (float)($data['fine_per_day'] ?? 1.00),
            'status'         => 'issued',
        ]);

        db()->query(
            "UPDATE books SET available_quantity = available_quantity - 1 WHERE id = ?",
            [$bookId]
        );

        $this->logAudit('book_issued', 'issued_books', $id);
        $this->redirectWith(url('library/issues'), 'success', '"' . $book['title'] . '" issued successfully.');
    }

    // ─── Process Return ────────────────────────────────────────────────────────

    public function processReturn(int $id): void
    {
        $this->authorize('library.manage');
        verifyCsrf();

        $issue = db()->query("
            SELECT ib.*, b.title AS book_title
            FROM issued_books ib
            JOIN books b ON b.id = ib.book_id
            WHERE ib.id = ? AND ib.institution_id = ? AND ib.status = 'issued'
        ", [$id, $this->institutionId])->fetch();

        if (!$issue) {
            $this->redirectWith(url('library/issues'), 'error', 'Issue record not found or already returned.');
            return;
        }

        // Calculate fine
        $today       = new \DateTime();
        $dueDate     = new \DateTime($issue['due_date']);
        $overdueDays = $today > $dueDate ? (int)$today->diff($dueDate)->days : 0;
        $fine        = $overdueDays > 0 ? round($overdueDays * (float)$issue['fine_per_day'], 2) : 0.00;

        db()->update('issued_books', [
            'status'      => 'returned',
            'return_date' => date('Y-m-d'),
            'fine_amount' => $fine,
        ], 'id = ?', [$id]);

        db()->query(
            "UPDATE books SET available_quantity = available_quantity + 1 WHERE id = ?",
            [$issue['book_id']]
        );

        $this->logAudit('book_returned', 'issued_books', $id);

        $msg = '"' . $issue['book_title'] . '" returned successfully.';
        if ($fine > 0) {
            $msg .= " Fine collected: ₹{$fine} ({$overdueDays} day" . ($overdueDays > 1 ? 's' : '') . " overdue).";
        }
        $this->redirectWith(url('library/issues'), 'success', $msg);
    }
}

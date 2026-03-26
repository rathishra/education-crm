<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class SettingController extends BaseController
{
    public function index(): void
    {
        $this->authorize('settings.view');

        db()->query("SELECT * FROM settings WHERE group_name != 'communication' ORDER BY group_name, key_name");
        $rows = db()->fetchAll();

        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['key_name']] = $row['value'];
        }

        $this->view('settings/index', compact('settings'));
    }

    public function update(): void
    {
        $this->authorize('settings.edit');

        $data = $this->postData();
        unset($data['_token']);

        foreach ($data as $key => $value) {
            db()->query("SELECT id FROM settings WHERE key_name = ?", [$key]);
            $existing = db()->fetch();

            if ($existing) {
                db()->update('settings', ['value' => sanitize($value)], '`key_name` = ?', [$key]);
            } else {
                db()->insert('settings', [
                    'key_name'   => sanitize($key),
                    'value'      => sanitize($value),
                    'group_name' => 'general',
                ]);
            }
        }

        $this->logAudit('settings_updated', 'settings', 0);
        $this->redirectWith('settings', 'Settings saved.', 'success');
    }

    public function communication(): void
    {
        $this->authorize('settings.view');

        db()->query("SELECT * FROM settings WHERE group_name = 'communication' ORDER BY key_name");
        $rows = db()->fetchAll();
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['key_name']] = $row['value'];
        }

        $this->view('settings/communication', compact('settings'));
    }

    public function updateCommunication(): void
    {
        $this->authorize('settings.edit');

        $data = $this->postData();
        unset($data['_token']);

        foreach ($data as $key => $value) {
            db()->query("SELECT id FROM settings WHERE key_name = ?", [$key]);
            $existing = db()->fetch();

            if ($existing) {
                db()->update('settings', ['value' => sanitize($value)], '`key_name` = ?', [$key]);
            } else {
                db()->insert('settings', [
                    'key_name'   => sanitize($key),
                    'value'      => sanitize($value),
                    'group_name' => 'communication',
                ]);
            }
        }

        $this->redirectWith('settings/communication', 'Communication settings saved.', 'success');
    }
}

<?php
// app/Controllers/Admin/SettingsController.php

require_once __DIR__ . '/../../Models/Admin/AdminModel.php';
require_once __DIR__ . '/../../Models/Admin/XmlModel.php';

class AdminSettingsController
{
    private PDO $pdo;
    private AdminModel $adminModel;
    private XmlModel $xmlModel;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->adminModel = new AdminModel($pdo);
        $this->xmlModel = new XmlModel($pdo);
    }

    public function getSettingsPageData(array $session, array $post, array $files, array $get): array
    {
        $message = '';
        $message_type = '';

        // Authentication Check
        if (!isset($session['admin_logged_in'])) {
            header('Location: index.php?page=admin_login');
            exit();
        }

        $admin_session_user = $session['admin_user'];

        // Handle XML Data Actions
        if (isset($get['export_users_xml'])) {
            $this->xmlModel->exportUsersToXML();
            $this->adminModel->logActivity($admin_session_user, 'XML export', 'Exported users to XML');
        }

        if (isset($get['export_full_xml'])) {
            $this->xmlModel->exportFullSystemToXML();
            $this->adminModel->logActivity($admin_session_user, 'XML export', 'Exported full system to XML');
        }

        if (isset($get['export_books_xml'])) {
            $this->xmlModel->exportBooksToXML();
            $this->adminModel->logActivity($admin_session_user, 'XML export', 'Exported books to XML');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($post['import_users_xml'])) {
            if (isset($files['user_xml_file']) && $files['user_xml_file']['error'] === UPLOAD_ERR_OK) {
                $count = $this->xmlModel->importUsersFromXML($files['user_xml_file']['tmp_name']);
                $this->adminModel->logActivity($admin_session_user, 'XML import', 'Imported ' . $count . ' users from XML');
                $message = "Successfully imported $count new users and their history.";
                $message_type = "success";
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($post['import_books_xml'])) {
            if (isset($files['books_xml_file']) && $files['books_xml_file']['error'] === UPLOAD_ERR_OK) {
                $count = $this->xmlModel->importBooksFromXML($files['books_xml_file']['tmp_name']);
                $this->adminModel->logActivity($admin_session_user, 'XML import', 'Imported ' . $count . ' books from XML');
                $message = "Successfully imported $count books from XML.";
                $message_type = "success";
            }
        }

        // Fetch current admin data
        $admin = $this->adminModel->getAdminBySession($admin_session_user);

        // Handle Admin ID Modification
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($post['update_account'])) {
            $result = $this->adminModel->updateAdminId($admin_session_user, trim($post['admin_id'] ?? ''));
            $message = $result['message'];
            $message_type = $result['success'] ? 'success' : 'error';
            if ($result['success'] && isset($result['new_id'])) {
                $this->adminModel->logActivity($admin_session_user, 'Admin ID updated', 'Changed admin ID from ' . $admin_session_user . ' to ' . $result['new_id']);
                $_SESSION['admin_user'] = $result['new_id'];
                $admin_session_user = $result['new_id'];
            }
        }

        // Handle Password Change
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($post['update_password'])) {
            $result = $this->adminModel->updatePassword(
                $admin_session_user,
                $post['old_pass'] ?? '',
                $post['new_pass'] ?? '',
                $post['repeat_pass'] ?? '',
                $admin ?? []
            );
            $message = $result['message'];
            $message_type = $result['success'] ? 'success' : 'error';
            if ($result['success']) {
                $this->adminModel->logActivity($admin_session_user, 'Password updated', 'Admin password was changed');
            }
        }

        // Handle Account Deletion
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($post['delete_account'])) {
            $this->adminModel->logActivity($admin_session_user, 'Admin account deleted', 'Admin deleted their own account');
            $this->adminModel->deleteAdminAccount($admin_session_user);
            $_SESSION = array();
            session_destroy();
            header("Location: index.php?page=admin_login");
            exit();
        }

        return [
            'message' => $message,
            'message_type' => $message_type,
            'admin_session_user' => $admin_session_user,
            'admin' => $admin,
        ];
    }
}

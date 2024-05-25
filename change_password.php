<?php
class ChangePasswordPage {
    private $userId;
    private $con;

    public function __construct($userId, $con) {
        $this->userId = $userId;
        $this->con = $con;
    }

    public function changePassword($old_password, $new_password) {
        $notification = '';
        // Check if the old password is correct
        $check_password_query = "SELECT password FROM admin WHERE id = ?";
        $check_password_stmt = $this->con->prepare($check_password_query);
        $check_password_stmt->bind_param("i", $this->userId);
        $check_password_stmt->execute();
        $check_password_result = $check_password_stmt->get_result();
        $row = $check_password_result->fetch_assoc();
        $hashed_password = $row['password'];

        if (password_verify($old_password, $hashed_password)) {
            // Old password is correct, proceed with changing password
            $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_password_query = "UPDATE admin SET password = ? WHERE id = ?";
            $update_password_stmt = $this->con->prepare($update_password_query);
            $update_password_stmt->bind_param("si", $hashed_new_password, $this->userId);
            if ($update_password_stmt->execute()) {
                // Password successfully updated
                $notification = '<div class="alert alert-success" role="alert">Password updated successfully!</div>';
            } else {
                // Error updating password
                $notification = '<div class="alert alert-danger" role="alert">Error updating password. Please try again.</div>';
            }
        } else {
            // Old password is incorrect
            $notification = '<div class="alert alert-danger" role="alert">Incorrect old password. Please try again.</div>';
        }
        return $notification;
    }

    public function displayPage() {
        // Tampilkan konten halaman change password di sini
        // Tambahkan form untuk mengubah kata sandi
    }
}

?>

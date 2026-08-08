<?php
if (!defined('DVWA_WEB_PAGE_TO_ROOT')) {
    define('DVWA_WEB_PAGE_TO_ROOT', '../../../');
}

$html = "";

// Identity comes from the authenticated session only. Cookies and query string
// parameters are attacker controlled and can never be used to make an
// authorisation decision.
$current_user_id = 0;
$user_role = '';
$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], "SELECT user_id, role FROM users WHERE user = ? LIMIT 1");
if ($stmt) {
    $current_user = dvwaCurrentUser();
    mysqli_stmt_bind_param($stmt, "s", $current_user);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    if ($res && mysqli_num_rows($res) > 0) {
        $row = mysqli_fetch_assoc($res);
        $current_user_id = intval($row['user_id']);
        $user_role = isset($row['role']) ? $row['role'] : '';
    }
    mysqli_stmt_close($stmt);
}

if (isset($_GET['action']) && isset($_GET['user_id'])) {
    if (!preg_match('/^\d+$/', $_GET['user_id'])) {
        $html .= "<p>Invalid user ID format. Please enter a number.</p>";
    } else {
        $id = intval($_GET['user_id']);

        // Object level authorisation: a user may only read their own record.
        if ($id !== $current_user_id) {
            $html .= "<p>Access denied. You can only view your own profile.</p>";
        } else {
            $stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], "SELECT first_name, last_name, user_id, avatar FROM users WHERE user_id = ? LIMIT 1");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "i", $id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                if ($result && mysqli_num_rows($result) > 0) {
                    $row = mysqli_fetch_assoc($result);
                    $html .= "
                        <div class=\"profile-info\">
                            <h3>User Profile</h3>
                            <p>User ID: " . htmlspecialchars($row['user_id'], ENT_QUOTES, 'UTF-8') . "</p>
                            <p>Name: " . htmlspecialchars($row['first_name'], ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($row['last_name'], ENT_QUOTES, 'UTF-8') . "</p>
                            <p>Avatar: " . htmlspecialchars($row['avatar'], ENT_QUOTES, 'UTF-8') . "</p>
                        </div>";
                } else {
                    $html .= "<p>No user found with ID: " . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . "</p>";
                }
                mysqli_stmt_close($stmt);
            }
        }

        // Log access attempts with a parameterised statement so a spoofed
        // X-Forwarded-For header cannot inject SQL.
        try {
            $create_table = "CREATE TABLE IF NOT EXISTS bac_log (
                id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id INT(6) NULL,
                target_id INT(6) NULL,
                ip_address VARCHAR(50) NULL,
                action VARCHAR(50) NULL,
                timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            mysqli_query($GLOBALS["___mysqli_ston"], $create_table);

            $ip = $_SERVER['REMOTE_ADDR'];
            $log = mysqli_prepare($GLOBALS["___mysqli_ston"], "INSERT INTO bac_log (user_id, target_id, ip_address) VALUES (?, ?, ?)");
            if ($log) {
                mysqli_stmt_bind_param($log, "iis", $current_user_id, $id, $ip);
                mysqli_stmt_execute($log);
                mysqli_stmt_close($log);
            }
        } catch (Exception $e) {
            // Silently fail if logging doesn't work
        }
    }
}

// Role is read from the database, never from a client supplied cookie.
$html .= "<div class='info-banner'>Current Role: " . htmlspecialchars($user_role !== '' ? $user_role : 'regular_user', ENT_QUOTES, 'UTF-8') . "</div>";
?>

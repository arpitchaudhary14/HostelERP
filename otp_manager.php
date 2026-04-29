<?php
require_once "db.php";
require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
class OTPManager {
    private $conn;
    private $max_per_hour = 5;
    private $cooldown_seconds = 60;
    private $expiry_minutes = 2;
    private $global_max_per_hour = 100;
    public function __construct($conn) {
        $this->conn = $conn;
    }
    public function canRequestOTP($identifier) {
        $ip = $_SERVER['REMOTE_ADDR'];
        $stmt_del = mysqli_prepare($this->conn, "DELETE FROM otp_rate_limits WHERE last_attempt_time < NOW() - INTERVAL 1 HOUR AND blocked_until IS NULL");
        mysqli_stmt_execute($stmt_del);
        $stmt_block = mysqli_prepare($this->conn, "SELECT blocked_until FROM otp_rate_limits WHERE (identifier=? OR identifier=?) AND blocked_until > NOW() LIMIT 1");
        mysqli_stmt_bind_param($stmt_block, "ss", $ip, $identifier);
        mysqli_stmt_execute($stmt_block);
        $block_res = mysqli_stmt_get_result($stmt_block);
        if ($row_block = mysqli_fetch_assoc($block_res)) {
            $wait_time = strtotime($row_block['blocked_until']) - time();
            $h = ceil($wait_time / 3600);
            $m = ceil(($wait_time % 3600) / 60);
            return "Security Alert: Access is temporarily restricted. Try again in $h hours $m minutes.";
        }
        $global_res = mysqli_query($this->conn, "SELECT SUM(attempts) as total_hourly FROM otp_rate_limits WHERE type='ip'");
        $total_hourly = mysqli_fetch_assoc($global_res)['total_hourly'] ?? 0;
        if ($total_hourly >= $this->global_max_per_hour) {
             return "SYSTEM PAUSED: Max global OTP limit reached. Try again later.";
        }
        $stmt = mysqli_prepare($this->conn, "SELECT attempts, last_attempt_time FROM otp_rate_limits WHERE identifier=? OR identifier=?");
        mysqli_stmt_bind_param($stmt, "ss", $ip, $identifier);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while($row = mysqli_fetch_assoc($result)) {
            if ($row['attempts'] >= $this->max_per_hour) {
                return "You have exceeded the maximum OTP requests. Please try again in an hour.";
            }
            if ($row['last_attempt_time']) {
                $last_time = strtotime($row['last_attempt_time']);
                $seconds_since_last = time() - $last_time;
                if ($seconds_since_last < $this->cooldown_seconds) {
                    $wait = $this->cooldown_seconds - $seconds_since_last;
                    return "Please wait $wait seconds before requesting another OTP.";
                }
            }
        }
        return true;
    }
    private function recordAttempt($identifier) {
        $ip = $_SERVER['REMOTE_ADDR'];
        foreach ([$ip, $identifier] as $id) {
            $stmt_check = mysqli_prepare($this->conn, "SELECT id FROM otp_rate_limits WHERE identifier=?");
            mysqli_stmt_bind_param($stmt_check, "s", $id);
            mysqli_stmt_execute($stmt_check);
            $check = mysqli_stmt_get_result($stmt_check);
            if (mysqli_num_rows($check) > 0) {
                $stmt_upd = mysqli_prepare($this->conn, "UPDATE otp_rate_limits SET attempts = attempts + 1, last_attempt_time = NOW() WHERE identifier=?");
                mysqli_stmt_bind_param($stmt_upd, "s", $id);
                mysqli_stmt_execute($stmt_upd);
            } else {
                $type = ($id === $ip) ? 'ip' : 'user';
                $stmt_ins = mysqli_prepare($this->conn, "INSERT INTO otp_rate_limits (identifier, type, last_attempt_time, attempts) VALUES (?, ?, NOW(), 1)");
                mysqli_stmt_bind_param($stmt_ins, "ss", $id, $type);
                mysqli_stmt_execute($stmt_ins);
            }
        }
    }
    public function requestOTP($email, $type) {
        $email_safe = mysqli_real_escape_string($this->conn, $email);
        $type_safe  = mysqli_real_escape_string($this->conn, $type);
        $limitCheck = $this->canRequestOTP($email);
        if ($limitCheck !== true) {
            return ['status' => 'error', 'message' => $limitCheck];
        }
        $query = "SELECT otp FROM otp_codes
                  WHERE email='$email_safe' AND type='$type_safe'
                  AND expiry_time > NOW() ORDER BY id DESC LIMIT 1";
        $result = mysqli_query($this->conn, $query);
        if (mysqli_num_rows($result) > 0) {
            $otp = mysqli_fetch_assoc($result)['otp'];
        } else {
            $otp    = (string) random_int(100000, 999999);
            $expiry = date("Y-m-d H:i:s", strtotime("+{$this->expiry_minutes} minutes"));
            mysqli_query($this->conn, "DELETE FROM otp_codes WHERE email='$email_safe' AND type='$type_safe'");
            $req_ip = $_SERVER['REMOTE_ADDR'];
            mysqli_query($this->conn,
                "INSERT INTO otp_codes (email, ip_address, otp, expiry_time, type)
                 VALUES ('$email_safe', '$req_ip', '$otp', '$expiry', '$type_safe')"
            );
        }
        $this->recordAttempt($email);
        $baseUrl     = "http://localhost/WebTechProject/security_actions.php";
        $actionToken = hash_hmac('sha256', $email . $otp, $_ENV['DB_PASS'] ?? 'secret');
        $cancelLink  = "$baseUrl?action=cancel&email=" . urlencode($email) . "&token=$actionToken";
        $blockLink   = "$baseUrl?action=block&email=" . urlencode($email) . "&token=$actionToken";
        $auditLink   = "http://localhost/WebTechProject/login.php";
        $type_label = str_replace('_', ' ', $type);
        $subject = "HostelERP - Your Verification Code";
        $body = "
        <div style='background-color:#f8f9fa; padding: 40px 20px; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif;'>
            <div style='max-width: 500px; margin: 0 auto; background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.08); border: 1px solid #eef0f2;'>
                <div style='background: linear-gradient(135deg, #6c63ff, #8b5cf6); padding: 40px 20px; text-align: center;'>
                    <h1 style='color: white; margin: 0; font-size: 32px; font-weight: 800; letter-spacing: -0.5px;'>HostelERP</h1>
                </div>
                <div style='padding: 40px; color: #2d3436;'>
                    <h2 style='color: #1a1a2e; margin-top: 0; font-size: 22px; font-weight: 700;'>Verification Required</h2>
                    <p style='font-size: 16px; line-height: 1.6; color: #636e72;'>Hello,</p>
                    <p style='font-size: 16px; line-height: 1.6; color: #636e72;'>You requested a verification code for <strong>$type_label</strong>. Use the code below to proceed securely:</p>
                    <div style='background: #f1f3f5; border-radius: 12px; padding: 24px; text-align: center; margin: 32px 0; border: 1px dashed #ced4da;'>
                        <span style='display: block; font-size: 12px; text-transform: uppercase; color: #a8aeb3; margin-bottom: 8px; font-weight: 700; letter-spacing: 1.5px;'>ONE-TIME PASSWORD</span>
                        <span style='font-size: 38px; font-weight: 800; color: #6c63ff; letter-spacing: 8px; font-family: \"Courier New\", Courier, monospace;'>$otp</span>
                    </div>  
                    <p style='font-size: 14px; color: #b2bec3; line-height: 1.5;'>This code is valid for <strong>{$this->expiry_minutes} minutes</strong>. For security reasons, do not share this code with anyone.</p>    
                    <div style='background: #fff5f5; border-left: 4px solid #ff4d4d; padding: 20px; margin: 25px 0; border-radius: 8px; text-align: left;'>
                        <p style='margin: 0; font-size: 14px; color: #d63031; font-weight: 700;'>Suspicious Activity?</p>
                        <p style='margin: 8px 0 16px; font-size: 13px; color: #636e72; line-height: 1.5;'>If you didn't request this code, someone may be trying to access your account. Use the options below to protect yourself instantly:</p>
                        <div style='display: flex; gap: 10px; flex-wrap: wrap;'>
                            <a href='$cancelLink' style='background: #f1f3f5; color: #1a1a2e; padding: 8px 12px; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: 600; border: 1px solid #dee2e6;'>Cancel This OTP</a>
                            <a href='$blockLink' style='background: #ff4d4d; color: white; padding: 8px 12px; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: 600;'>Block Requester</a>
                            <a href='$auditLink' style='background: #6c63ff; color: white; padding: 8px 12px; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: 600;'>Login Activity</a>
                        </div>
                        <p style='margin: 15px 0 0; font-size: 12px; color: #636e72;'>Need help? <a href='mailto:hostelerp.system@gmail.com' style='color: #d63031; text-decoration: none; font-weight: 700;'>Contact Support</a></p>
                    </div>
                    <div style='margin-top: 40px; padding-top: 24px; border-top: 1px solid #f1f3f5; text-align: center;'>
                        <p style='font-size: 12px; color: #b2bec3; margin-bottom: 0;'>&copy; 2026 HostelERP. All rights reserved.<br>Questions? <a href='mailto:hostelerp.system@gmail.com' style='color: #6c63ff; text-decoration: none;'>hostelerp.system@gmail.com</a></p>
                    </div>
                </div>
            </div>
        </div>";
        return $this->sendEmail($email, $subject, $body);
    }
    private function sendEmail($to, $subject, $body) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['MAIL_USERNAME'] ?? '';
            $mail->Password   = $_ENV['MAIL_PASSWORD'] ?? '';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $_ENV['MAIL_PORT'] ?? 587;
            $mail->setFrom($_ENV['MAIL_FROM'] ?? '', $_ENV['MAIL_FROM_NAME'] ?? 'HostelERP');
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->send();
            return ['status' => 'success', 'message' => 'OTP sent to your email.'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => "Mail error: " . $mail->ErrorInfo];
        }
    }
}
?>
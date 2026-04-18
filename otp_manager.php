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
        $identifier = mysqli_real_escape_string($this->conn, $identifier);
        mysqli_query($this->conn, "DELETE FROM otp_rate_limits WHERE last_attempt_time < NOW() - INTERVAL 1 HOUR");
        $global_res = mysqli_query($this->conn, "SELECT SUM(attempts) as total_hourly FROM otp_rate_limits WHERE type='ip'");
        $total_hourly = mysqli_fetch_assoc($global_res)['total_hourly'] ?? 0;
        if ($total_hourly >= $this->global_max_per_hour) {
             return "SYSTEM PAUSED: Max global OTP limit reached. Try again later.";
        }
        $query = "SELECT * FROM otp_rate_limits WHERE identifier='$ip' OR identifier='$identifier'";
        $result = mysqli_query($this->conn, $query);
        while($row = mysqli_fetch_assoc($result)) {
            if ($row['attempts'] >= $this->max_per_hour) {
                return "You have exceeded the maximum OTP requests. Please try again in an hour.";
            }
            if ($row['last_attempt_time']) {
                $last_time = strtotime($row['last_attempt_time']);
                $seconds_since_last = time() - $last_time;
                if ($seconds_since_last < $this->cooldown_seconds) {
                    $wait = $this->cooldown_seconds - $seconds_since_last;
                    if ($wait > 60) {
                        $mins = ceil($wait / 60);
                        return "Please wait $mins minutes before requesting another OTP.";
                    }
                    return "Please wait $wait seconds before requesting another OTP.";
                }
            }
        }
        return true;
    }
    private function recordAttempt($identifier) {
        $ip = $_SERVER['REMOTE_ADDR'];
        foreach ([$ip, $identifier] as $id) {
            $id = mysqli_real_escape_string($this->conn, $id);
            $check = mysqli_query($this->conn, "SELECT id FROM otp_rate_limits WHERE identifier='$id'");
            if (mysqli_num_rows($check) > 0) {
                mysqli_query($this->conn, "UPDATE otp_rate_limits SET attempts = attempts + 1, last_attempt_time = NOW() WHERE identifier='$id'");
            } else {
                mysqli_query($this->conn, "INSERT INTO otp_rate_limits (identifier, type, last_attempt_time) VALUES ('$id', 'ip', NOW())");
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
            mysqli_query($this->conn,
                "INSERT INTO otp_codes (email, otp, expiry_time, type)
                 VALUES ('$email_safe', '$otp', '$expiry', '$type_safe')"
            );
        }
        $this->recordAttempt($email);
        $subject = "HostelERP — Your OTP";
        $message = "Your OTP for " . str_replace('_', ' ', $type) . " is: <strong>$otp</strong><br>Valid for {$this->expiry_minutes} minutes.";
        return $this->sendEmail($email, $subject, $message);
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
            $mail->Body    = "
                <div style='font-family:Arial,sans-serif;max-width:480px;margin:0 auto;'>
                    <h2 style='color:#6c63ff;'>HostelERP</h2>
                    <p style='font-size:15px;'>$body</p>
                    <p style='color:#888;font-size:12px;'>Do not share this OTP with anyone.</p>
                </div>";
            $mail->send();
            return ['status' => 'success', 'message' => 'OTP sent to your email.'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => "Mail error: " . $mail->ErrorInfo];
        }
    }
}
?>
<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Anti-spam Honeypot Check: if this field has data, a bot filled it out
    $bot_check = isset($_POST['bot_check']) ? trim($_POST['bot_check']) : '';
    if (!empty($bot_check)) {
        // Pretend it succeeded so the bot doesn't try different attack vectors
        echo json_encode(["status" => "success", "message" => "Your inquiry has been sent successfully."]);
        exit;
    }

    // Get form data
    $name = isset($_POST['name']) ? strip_tags(trim($_POST['name'])) : '';
    $company = isset($_POST['company']) ? strip_tags(trim($_POST['company'])) : '';
    $email = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : '';
    $requirement = isset($_POST['requirement']) ? htmlspecialchars(trim($_POST['requirement'])) : '';

    if (empty($name) || empty($requirement) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Please fill out all required fields correctly."]);
        exit;
    }

    $mail = new PHPMailer(true);

    try {
        // Server settings
        // TODO: Replace with your actual SMTP settings below!
        $mail->isSMTP();
        $mail->Host       = 'smtp.example.com'; // Set the SMTP server to send through
        $mail->SMTPAuth   = true;               // Enable SMTP authentication
        $mail->Username   = 'your_email@example.com'; // SMTP username
        $mail->Password   = 'your_password';    // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption (or ENCRYPTION_SMTPS for port 465)
        $mail->Port       = 587;                // TCP port to connect to

        // Recipients
        $mail->setFrom('your_email@example.com', 'Atlas Global Website'); // Sender email
        $mail->addAddress('info@atlasglobal.com', 'Atlas Global Sales');  // Add a recipient
        $mail->addReplyTo($email, $name);

        // Content
        $mail->isHTML(false);
        $mail->Subject = 'New Product Inquiry from ' . $name;
        $mail->Body    = "You have received a new inquiry from your website.\n\n" .
                         "Name: $name\n" .
                         "Company: $company\n" .
                         "Email: $email\n\n" .
                         "Requirement:\n$requirement\n";

        $mail->send();
        
        echo json_encode(["status" => "success", "message" => "Your inquiry has been sent successfully."]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"]);
    }
} else {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "There was a problem with your submission, please try again."]);
}
?>

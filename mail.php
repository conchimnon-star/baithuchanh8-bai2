<?php
declare(strict_types=1);
session_start();

require __DIR__ . '/config.php';

function redirectWithMessage(string $type, string $message, array $form): void
{
    $_SESSION['bai2_form'] = $form;
    $_SESSION['bai2_flash'] = ['type' => $type, 'message' => $message];
    header('Location: index.php', true, 303);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php', true, 303);
    exit;
}

$postTo = $_POST['to'] ?? null;
$postSubject = $_POST['subject'] ?? null;
$postBody = $_POST['body'] ?? null;
$form = [
    'to' => is_string($postTo) ? trim($postTo) : '',
    'subject' => is_string($postSubject) ? trim($postSubject) : '',
    'body' => is_string($postBody) ? trim($postBody) : '',
];
$token = $_POST['csrf_token'] ?? '';
if (!is_string($token) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
    redirectWithMessage('error', 'Phiên gửi đã hết hạn hoặc không hợp lệ. Hãy thử lại.', $form);
}
if (!is_string($postTo) || !is_string($postSubject) || !is_string($postBody)) {
    redirectWithMessage('error', 'Dữ liệu biểu mẫu không hợp lệ.', $form);
}
if (!filter_var($form['to'], FILTER_VALIDATE_EMAIL)) {
    redirectWithMessage('error', 'Địa chỉ email người nhận không hợp lệ.', $form);
}
if (
    $form['subject'] === ''
    || strlen($form['subject']) > 200
    || preg_match('/[\r\n]/', $form['subject'])
) {
    redirectWithMessage('error', 'Tiêu đề không được để trống, vượt quá 200 ký tự hoặc chứa xuống dòng.', $form);
}
if ($form['body'] === '' || strlen($form['body']) > 20000) {
    redirectWithMessage('error', 'Nội dung không được để trống và tối đa 20.000 byte.', $form);
}
if (
    !filter_var($mailConfig['smtp_username'], FILTER_VALIDATE_EMAIL)
    || $mailConfig['smtp_password'] === ''
    || !filter_var($mailConfig['from_email'], FILTER_VALIDATE_EMAIL)
) {
    redirectWithMessage('error', 'Thiếu cấu hình Gmail SMTP. Hãy kiểm tra config.local.php.', $form);
}

$attachment = null;
$upload = $_FILES['attachment'] ?? null;
if ($upload !== null && !is_array($upload)) {
    redirectWithMessage('error', 'Thông tin tệp tải lên không hợp lệ.', $form);
}
if (is_array($upload)) {
    if (
        !isset($upload['tmp_name'], $upload['name'], $upload['size'], $upload['error'])
        || !is_string($upload['tmp_name'])
        || !is_string($upload['name'])
        || !is_int($upload['size'])
        || !is_int($upload['error'])
    ) {
        redirectWithMessage('error', 'Thông tin tệp tải lên không đầy đủ.', $form);
    }
    if ($upload['error'] !== UPLOAD_ERR_NO_FILE && $upload['error'] !== UPLOAD_ERR_OK) {
        redirectWithMessage('error', 'Tệp tải lên bị lỗi. Hãy thử chọn lại tệp.', $form);
    }
    if ($upload['error'] === UPLOAD_ERR_OK) {
        if (!is_uploaded_file($upload['tmp_name'])) {
            redirectWithMessage('error', 'Tệp tải lên không hợp lệ. Hãy thử chọn lại tệp.', $form);
        }
        if ($upload['size'] <= 0 || $upload['size'] > $mailConfig['max_attachment_bytes']) {
            redirectWithMessage('error', 'Tệp phải có dung lượng lớn hơn 0 và không vượt quá 5 MB.', $form);
        }
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($upload['tmp_name']);
        if (!is_string($mime) || !in_array($mime, $mailConfig['allowed_attachment_mimes'], true)) {
            redirectWithMessage('error', 'Loại tệp không được phép. Chỉ nhận PDF, TXT, JPEG và PNG.', $form);
        }
        $attachment = [
            'path' => $upload['tmp_name'],
            'name' => basename($upload['name']),
        ];
    }
}

require __DIR__ . '/PHPMailer/autoload.php';

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = (string) $mailConfig['smtp_host'];
    $mail->SMTPAuth = true;
    $mail->Username = (string) $mailConfig['smtp_username'];
    $mail->Password = (string) $mailConfig['smtp_password'];
    $mail->SMTPSecure = (string) $mailConfig['smtp_secure'];
    $mail->Port = (int) $mailConfig['smtp_port'];
    $mail->CharSet = PHPMailer::CHARSET_UTF8;
    $mail->setFrom((string) $mailConfig['from_email'], (string) $mailConfig['from_name']);
    $mail->addAddress($form['to']);
    $mail->isHTML(true);
    $mail->Subject = $form['subject'];
    $mail->Body = nl2br(htmlspecialchars($form['body'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
    $mail->AltBody = $form['body'];
    if ($attachment !== null) {
        $mail->addAttachment($attachment['path'], $attachment['name']);
    }
    $mail->send();
    redirectWithMessage('success', 'Email đã được gửi thành công đến ' . $form['to'] . '.', $form);
} catch (Exception $exception) {
    $detail = str_replace(
        [(string) $mailConfig['smtp_password'], (string) $mailConfig['smtp_username']],
        '[đã ẩn]',
        $mail->ErrorInfo !== '' ? $mail->ErrorInfo : $exception->getMessage()
    );
    redirectWithMessage('error', 'Không gửi được email. Chi tiết SMTP: ' . $detail, $form);
}

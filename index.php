<?php
declare(strict_types=1);
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$form = $_SESSION['bai2_form'] ?? ['to' => '', 'subject' => '', 'body' => ''];
unset($_SESSION['bai2_form']);
$flash = $_SESSION['bai2_flash'] ?? null;
unset($_SESSION['bai2_flash']);

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bài 2 - Gửi email qua form</title>
    <style>
        :root{font-family:system-ui,-apple-system,"Segoe UI",sans-serif;color:#182230;background:#f3f6fb}
        body{margin:0;padding:36px 18px}.card{max-width:760px;margin:auto;padding:30px;background:#fff;border:1px solid #e3e8ef;border-radius:16px;box-shadow:0 12px 35px #18223012}
        h1{margin-top:0;font-size:1.75rem}.intro{color:#5d6878;line-height:1.6}label{display:block;margin:18px 0 7px;font-weight:650}
        input,textarea{box-sizing:border-box;width:100%;padding:11px 12px;border:1px solid #bdc7d5;border-radius:8px;font:inherit}
        input:focus,textarea:focus{outline:3px solid #155eef30;border-color:#155eef}textarea{min-height:180px;resize:vertical}
        button{margin-top:20px;border:0;border-radius:9px;padding:12px 20px;background:#155eef;color:#fff;font-size:1rem;font-weight:650;cursor:pointer}
        button:hover{background:#124bc2}.notice{padding:13px 15px;border-radius:9px;margin:20px 0}.success{background:#e8f7ed;color:#176b35}.error{background:#fff0ee;color:#a52a1c}
        .help{margin:6px 0;color:#5d6878;font-size:.9rem}
    </style>
</head>
<body>
<main class="card">
    <h1>Bài 2 - Gửi email qua form</h1>
    <p class="intro">Nhập nội dung thư. Tệp đính kèm là tùy chọn; chỉ PDF, TXT, JPEG hoặc PNG tối đa 5 MB được chấp nhận.</p>
    <?php if (is_array($flash)): ?>
        <p class="notice <?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?></p>
    <?php endif; ?>
    <form method="POST" action="mail.php" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
        <label for="to">Email người nhận</label>
        <input id="to" name="to" type="email" maxlength="254" required autocomplete="email" value="<?= e((string) $form['to']) ?>">
        <label for="subject">Tiêu đề email</label>
        <input id="subject" name="subject" type="text" maxlength="200" required value="<?= e((string) $form['subject']) ?>">
        <label for="body">Nội dung email</label>
        <textarea id="body" name="body" maxlength="20000" required><?= e((string) $form['body']) ?></textarea>
        <label for="attachment">Tệp đính kèm (không bắt buộc)</label>
        <input id="attachment" name="attachment" type="file" accept=".pdf,.txt,.jpg,.jpeg,.png">
        <p class="help">Tệp tải lên được kiểm tra MIME thực tế và dung lượng trước khi gửi.</p>
        <button type="submit">Gửi email</button>
    </form>
</main>
</body>
</html>

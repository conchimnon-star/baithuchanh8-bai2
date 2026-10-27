# Bài 2 – Gửi email qua form

Ứng dụng PHP gửi email đến một địa chỉ nhập từ form, hỗ trợ tệp đính kèm tùy chọn. Dùng PHPMailer và Gmail SMTP; thông tin SMTP cục bộ không được đưa lên Git.

## Cài đặt và chạy

1. Cài PHP 8 trở lên và Composer.
2. Trong thư mục này, chạy:

   ```sh
   composer install
   ```

3. Sao chép `config.local.php.example` thành `config.local.php`. Điền Gmail gửi và App Password.
4. Trên tài khoản Google, bật xác minh 2 bước rồi tạo App Password nếu tài khoản đủ điều kiện. Không dùng mật khẩu Gmail thông thường.
5. Đặt dự án trong `htdocs`, bật Apache trên XAMPP, rồi mở:

   `http://localhost/Lab8_Email/Bai2_GuiEmailForm/`

6. Nhập email người nhận, tiêu đề và nội dung; chọn tệp nếu cần; bấm **Gửi email**. Kiểm tra hộp thư đến và thư mục Spam.

Cấu hình mặc định là Gmail SMTP `smtp.gmail.com`, cổng `587` với STARTTLS và charset UTF-8. Bài hỗ trợ tệp PDF, TXT, JPEG hoặc PNG tối đa 5 MB; loại MIME được kiểm tra trên máy chủ. Nếu tệp không tải lên được, kiểm tra giới hạn `upload_max_filesize`, `post_max_size` và thư mục tạm trong `php.ini`, rồi khởi động lại Apache.

Không commit `config.local.php`. Nếu Composer báo thiếu ZIP extension trên Windows, bật extension ZIP trong `php.ini` mà Composer sử dụng rồi chạy lại `composer install`. Không tắt xác minh chứng chỉ TLS.

<x-mail::message>

<style>
    div.confirmation_code {
        background: #2d3748;
        color: #f7fafc;
        padding: 10px;
        border-radius: 5px;
        margin: 10px auto;
        width: fit-content;
    }

    div.confirmation_code span {
        font-size: 20px;
        font-weight: bold;
        letter-spacing: 2px;
    }
</style>

# Xin chào, {{ $user->fullname }},

Bạn đã đăng ký tài khoản thành công với email {{ $user->email }}. Để hoàn tất quá trình đăng ký, vui lòng xác minh email của bạn bằng cách nhập mã xác minh sau đây:

<div class="confirmation_code">
    <span>{{ $user->confirmation_code }}</span>
</div>

Mã OTP này sẽ hết hạn sau 1 phút.

Nếu bạn không thực hiện hành động này, vui lòng bỏ qua email này.

Regards,<br>
{{ config('app.name') }}
</x-mail::message>

<x-mail::message>

<style>
    div.container_info {
        background: #fff6ed;
        color: #fe6411;
        padding: 10px;
        border-radius: 5px;
        margin: 10px auto;
        width: fit-content;
    }

    div.container_info div {
        margin: 5px 0;
        font-size: 16px;
    }

    div.container_info span.title {
        font-weight: bold;
    }
</style>

# Xin chào, {{ $user->fullname }},

Tài khoản của bạn đã được tạo thành công với email {{ $user->email }}. <br>
Dưới đây là thông tin tài khoản của bạn:

<div class="container_info">
    <div>
        <span class="title">Email: </span>
        <span>{{ $user->email }}</span>
    </div>
    <div>
        <span class="title">Mật khẩu: </span>
        <span>{{ $password }}</span>
    </div>
</div>

Tài khoản này đã được kích hoạt và có thể sử dụng ngay. <br>

Bạn có thể đăng nhập vào hệ thống tại địa chỉ: [Đăng nhập](https://readstation.store/login) <br>

**Lưu ý:**
- Mật khẩu của bạn đã được tạo mặc định, vui lòng đổi mật khẩu sau khi đăng nhập để bảo mật tài khoản.
- Đừng chia sẻ mật khẩu của bạn với bất kỳ ai.
- Bạn cũng có thể đăng nhập bằng google với email trên.


Regards,<br>
{{ config('app.name') }}
</x-mail::message>

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

Kính chào quý khách <b>{{ $verificationRequest->userRequest->fullname }}</b>,

Chân thành cảm ơn quý khách đã sử dụng dịch vụ của chúng tôi.

Chúng tôi thông báo rằng yêu cầu xác thực {{ $verificationRequest->verification_card_type == 'student_card' ? 'thẻ sinh viên' : 'chứng minh nhân dân' }} của quý khách đã được chấp nhận.

Sau khi xác thực, quý khách sẽ được hưởng các ưu đãi và chính sách hỗ trợ từ chúng tôi. Vui lòng kiểm tra thông tin tài khoản để cập nhật thông tin mới nhất.

Cảm ơn quý khách đã sử dụng dịch vụ của chúng tôi.

Thân gửi,<br>
{{ config('app.name') }}
</x-mail::message>

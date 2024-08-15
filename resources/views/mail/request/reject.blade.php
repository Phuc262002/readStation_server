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

Chúng tôi rất tiếc phải thông báo rằng yêu cầu xác thực {{ $verificationRequest->verification_card_type == 'student_card' ? 'thẻ sinh viên' : 'chứng minh nhân dân' }} của quý khách đã bị từ chối.

Lý do từ chối: {{ $verificationRequest->reason }}

Vui lòng kiểm tra lại thông tin và thực hiện lại yêu cầu xác thực.

Nếu có bất kỳ thắc mắc hoặc cần hỗ trợ, vui lòng liên hệ với chúng tôi qua email: caodang@fpt.edu.vn hoặc số điện thoại: 090 166 0002 – 028 6686 6486.

Cảm ơn quý khách đã sử dụng dịch vụ của chúng tôi.

Thân gửi,<br>
{{ config('app.name') }}
</x-mail::message>

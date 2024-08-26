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

Kính chào quý khách <b>{{ $loanOrders->user->fullname }}</b>,

Chân thành cảm ơn quý khách đã sử dụng dịch vụ của chúng tôi.

Chúng tôi hy vọng quý khách hài lòng với trải nghiệm thuê sách tại ReadStation.

Đơn hàng của quý khách đã bị hủy tự động bởi hệ thống do quá hạn thanh toán.

Bấm vào đường link sau để xem chi tiết đơn hàng: [Xem đơn hàng](https://readstation.store/account/order/{{ $loanOrders->id }})

Chúng tôi rất tiếc vì sự bất tiện này và mong quý khách thông cảm.

Thân gửi,<br>
{{ config('app.name') }}
</x-mail::message>

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

Đơn hàng của quý khách hiện đã sắp tới hạn trả.

Bấm vào đường link sau để xem chi tiết đơn hàng: [Xem đơn hàng](https://readstation.store/account/order/{{ $loanOrders->id }})


Lưu ý: Quý khách vui lòng trả sách đúng hạn để tránh phát sinh phí phạt.

Thân gửi,<br>
{{ config('app.name') }}
</x-mail::message>

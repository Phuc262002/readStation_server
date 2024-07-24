<?php

namespace App\Http\Controllers\Api\VNPay;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class VnpayCreatePayment extends Controller
{
    private string $vnp_TmnCode;
    private string $vnp_HashSecret;
    private string $vnp_Url;
    private string $vnp_Returnurl;
    private string $vnp_apiUrl;
    private string $apiUrl;


    public function __construct()
    {
        $this->vnp_TmnCode = "NWEVMRXZ";
        $this->vnp_HashSecret = "BANLO0CS7GCLSS2TO9QP7H6PTI7R4BYB";
        $this->vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
        $this->vnp_Returnurl = env('CLIENT_URL')."/payment/result?portal=vnpay";
        $this->vnp_apiUrl = "http://sandbox.vnpayment.vn/merchant_webapi/merchant.html";
        $this->apiUrl = "https://sandbox.vnpayment.vn/merchant_webapi/api/transaction";
    }

    public function createPaymentLink($amount, $transaction_code, $description)
    {
        $validator = Validator::make([
            'amount' => $amount
        ], [
            'amount' => 'required|numeric',
        ],[
            'amount.required' => 'Vui lòng nhập số tiền',
            'amount.numeric' => 'Số tiền phải là số',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ]);
        }


        $vnp_TxnRef = rand(1, 10000); //Mã giao dịch thanh toán tham chiếu của merchant
        $vnp_Amount = $amount; // Số tiền thanh toán
        $vnp_Locale = "vn"; //Ngôn ngữ chuyển hướng thanh toán
        $vnp_BankCode = ""; //Mã phương thức thanh toán
        $vnp_IpAddr = $_SERVER['REMOTE_ADDR']; //IP Khách hàng thanh toán

        $startTime = date("YmdHis");
        $expire = date('YmdHis', strtotime('+30 minutes', strtotime($startTime)));

        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $this->vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount * 100,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $description,
            "vnp_OrderType" => "other",
            "vnp_ReturnUrl" => $this->vnp_Returnurl,
            "vnp_TxnRef" => $transaction_code,
            "vnp_ExpireDate" => $expire
        );

        if (isset($vnp_BankCode) && $vnp_BankCode != "") {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }

        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $this->vnp_Url . "?" . $query;
        if (isset($this->vnp_HashSecret)) {
            $vnpSecureHash =   hash_hmac('sha512', $hashdata, $this->vnp_HashSecret); 
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }

        return $vnp_Url;
    }
}

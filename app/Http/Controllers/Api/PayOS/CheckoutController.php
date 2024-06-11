<?php

namespace App\Http\Controllers\Api\PayOS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PayOS\PayOS;

class CheckoutController extends Controller
{
    public function __construct()
    {
    }

    public function createPaymentLink(Request $request) {
        $YOUR_DOMAIN = "http://localhost:8000";
        $data = [
            "orderCode" => intval(substr(strval(microtime(true) * 10000), -6)),
            "amount" => 2000,
            "description" => "Thanh toán đơn hàng",
            "returnUrl" => $YOUR_DOMAIN . "/success.html",
            "cancelUrl" => $YOUR_DOMAIN . "/cancel.html"
        ];
        error_log($data['orderCode']);
        $PAYOS_CLIENT_ID = env('PAYOS_CLIENT_ID');
        $PAYOS_API_KEY = env('PAYOS_API_KEY');
        $PAYOS_CHECKSUM_KEY = env('PAYOS_CHECKSUM_KEY');

        $payOS = new PayOS($PAYOS_CLIENT_ID, $PAYOS_API_KEY, $PAYOS_CHECKSUM_KEY);
        try {
            // $response = $payOS->createPaymentLink($data);
            // return redirect($response['checkoutUrl']);
            $response = $payOS->getPaymentLinkInformation($data['orderCode']);
            return $response;
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }
}
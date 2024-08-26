<?php

namespace App\Http\Controllers\Api\CheckSchedule;

use App\Http\Controllers\Controller;
use App\Mail\CheckOrderPending as MailCheckOrderPending;
use App\Models\LoanOrders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class CheckOrderPending extends Controller
{
    public function checkOrderPending(Request $request)
    {
        $orderPending = LoanOrders::where('status', 'pending')->where('created_at', '<=', now()->subDays(1))->get();

        if ($orderPending->isNotEmpty()) {
            foreach ($orderPending as $order) {
                $order->update([
                    'status' => 'canceled',
                    'reason_cancel' => 'Hết hạn thanh toán tại cửa hàng'
                ]);

                foreach ($order->loanOrderDetails as $orderDetail) {
                    $orderDetail->update(['status' => 'canceled']);
                }

                $transaction = $order->transaction;
                if ($transaction) {
                    $transaction->update(['status' => 'canceled']);
                }

                Mail::to($order->user->email)->send(new MailCheckOrderPending($order));
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Check schedule order pending success'
        ], 200);
    }
}

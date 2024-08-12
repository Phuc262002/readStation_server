<?php

namespace App\Http\Controllers\Api\CheckSchedule;

use App\Http\Controllers\Controller;
use App\Models\LoanOrders;
use App\Models\Transaction;
use Illuminate\Http\Request;

class CheckPaymentOrder extends Controller
{
    public function checkPaymentOrder(Request $request)
    {
        try {
            $transactions = Transaction::where('transaction_method', 'online')
                           ->where('status', 'pending')
                           ->where('expired_at', '<=', now()->subMinutes(30))
                           ->get();

            foreach ($transactions as $transaction) {
                $transaction->status = 'canceled';
                $transaction->save();

                $order = LoanOrders::with('loanOrderDetails')->where('id', $transaction->loan_order_id)->first();
                foreach ($order->loanOrderDetails as $orderDetail) {
                    if ($orderDetail->status == 'canceled') {
                        $orderDetail->save();
                    }
                }
                $order->status = 'canceled';
                $order->save();
            }

            return response()->json([
                'status' => true,
                'message' => 'Check transaction success'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Check transaction failed',
                'errors' => $th->getMessage()
            ], 500);
        }
    }
}

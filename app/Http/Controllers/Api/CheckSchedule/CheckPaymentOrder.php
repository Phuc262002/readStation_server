<?php

namespace App\Http\Controllers\Api\CheckSchedule;

use App\Http\Controllers\Controller;
use App\Models\Extensions;
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
                ->where('expired_at', '<=', now())
                ->get();

            foreach ($transactions as $transaction) {
                if ($transaction->transaction_type == 'payment') {
                    $transaction->status = 'canceled';
                    $transaction->save();

                    $order = LoanOrders::with('loanOrderDetails')->where('id', $transaction->loan_order_id)->first();
                    if ($order->status == 'pending') {
                        foreach ($order->loanOrderDetails as $orderDetail) {
                            if ($orderDetail->status == 'canceled') {
                                $orderDetail->save();
                            }
                        }
                        $order->status = 'canceled';
                        $order->save();
                    }
                } else if ($transaction->transaction_type == 'extend') {
                    $transaction->status = 'canceled';
                    $transaction->save();

                    $order = LoanOrders::with('loanOrderDetails')->where('id', $transaction->loan_order_id)->first();
                    $extension = Extensions::with('extensionDetails')->where('loan_order_id', $order->id)->where('status', 'pending')->first();

                    $extension->update([
                        'status' => 'rejected'
                    ]);

                    $checkOrderdue = false;
                    foreach ($order->loanOrderDetails as $orderDetail) {
                        if ($orderDetail->current_due_date <= now()) {
                            $orderDetail->status = 'overdue';
                            $orderDetail->save();
                            $checkOrderdue = true;
                        }
                    }
                    if ($checkOrderdue) {
                        $order->status = 'overdue';
                    }
                }
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

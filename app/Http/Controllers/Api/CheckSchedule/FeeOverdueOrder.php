<?php

namespace App\Http\Controllers\Api\CheckSchedule;

use App\Http\Controllers\Controller;
use App\Models\LoanOrderDetails;
use App\Models\LoanOrders;
use Illuminate\Http\Request;

class FeeOverdueOrder extends Controller
{
    public function IncreaseFineFeeOverdue() {
        try {
            $orderOverdue = LoanOrders::with('loanOrderDetails')->where('status', 'overdue')->get();

            foreach ($orderOverdue as $order) {
                foreach ($order->loanOrderDetails as $orderDetail) {
                    $orderDetail->fine_amount = $orderDetail->fine_amount + 5000;
                    $orderDetail->save();
                }

                $order = LoanOrders::find($order->id);
                $total_fine_fee = LoanOrderDetails::where('loan_order_id', $order->id)->sum('fine_amount');
                $order->total_fine_fee = $total_fine_fee;
                $order->total_return_fee = $order->total_deposit_fee - $total_fine_fee;
                $order->save();
            }

            return response()->json([
                'status' => true,
                'message' => 'Increase fine fee overdue success'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Increase fine fee overdue failed',
                'errors' => $th->getMessage()
            ], 500);
        }
    }
}

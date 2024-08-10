<?php

namespace App\Http\Controllers\Api\CheckSchedule;

use App\Http\Controllers\Controller;
use App\Mail\RemindOrderOverdue;
use App\Mail\RemindOrderOverdued;
use App\Models\LoanOrderDetails;
use App\Models\LoanOrders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class RemindReturnBookController extends Controller
{
    public function remindReturnBook(Request $request)
    {
        try {
            $loanOrderDetailRemind = LoanOrderDetails::where('current_due_date', '<=', now())
                ->where(function ($query) {
                    $query->where('status', '=', 'active')
                        ->orWhere('status', '=', 'extended');
                })
                ->get();

            if (!$loanOrderDetailRemind->isEmpty()) {
                foreach ($loanOrderDetailRemind as $loanOrderDetail) {
                    $loanOrderDetail->status = 'overdue';
                    $loanOrderDetail->save();

                    $loanOrder = LoanOrders::where('id', $loanOrderDetail->loan_order_id)
                        ->update(['status' => 'overdue']);

                    Mail::to($loanOrder->user->email)->send(new RemindOrderOverdue($loanOrderDetail->loanOrder));
                }
            }

            $loanOrderOverdue = LoanOrders::where('status', 'overdue')->get();

            if (!$loanOrderOverdue->isEmpty()) {
                foreach ($loanOrderOverdue as $loanOrder) {
                    Mail::to($loanOrder->user->email)->send(new RemindOrderOverdued($loanOrder));
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Check schedule remind return book success'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Check schedule remind return book fail',
                'errors' => $th->getMessage()
            ], 500);
        }
    }
}

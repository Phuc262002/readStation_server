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
                ->whereIn('status', ['active', 'extended'])
                ->get();

            if ($loanOrderDetailRemind->isNotEmpty()) {
                foreach ($loanOrderDetailRemind as $loanOrderDetail) {
                    $loanOrderDetail->update(['status' => 'overdue']);

                    $loanOrder = LoanOrders::find($loanOrderDetail->loan_order_id);
                    if ($loanOrder) {
                        $loanOrder->update(['status' => 'overdue']);

                        // Ensure that the loanOrder relationship exists before attempting to send an email
                        if ($loanOrder->user && $loanOrder->user->email) {
                            Mail::to($loanOrder->user->email)->send(new RemindOrderOverdue($loanOrderDetail->loanOrder));
                        }
                    }
                }
            }

            $loanOrderOverdue = LoanOrders::with(['user'])
                ->where('status', 'overdue')
                ->get();

            if ($loanOrderOverdue->isNotEmpty()) {
                foreach ($loanOrderOverdue as $loanOrder) {
                    if ($loanOrder->user && $loanOrder->user->email) {
                        Mail::to($loanOrder->user->email)->send(new RemindOrderOverdued($loanOrder));
                    }
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Check schedule remind return book success'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Check schedule remind return book failed',
                'errors' => $th->getMessage()
            ], 500);
        }
    }
}

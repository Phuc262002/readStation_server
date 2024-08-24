<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookDetail;
use App\Models\LoanOrderDetails;
use App\Models\LoanOrders;
use App\Models\ReturnHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/admin/return-histories',
    operationId: 'getReturnHistories',
    summary: 'Get all return histories',
    description: 'Get all return histories',
    tags: ['Admin / Return History'],
    security: [
        ['bearerAuth' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'page',
            in: 'query',
            required: false,
            description: 'Số trang hiện tại',
            schema: new OA\Schema(type: 'integer', default: 1)
        ),
        new OA\Parameter(
            name: 'pageSize',
            in: 'query',
            required: false,
            description: 'Số lượng mục trên mỗi trang',
            schema: new OA\Schema(type: 'integer', default: 10)
        ),
        new OA\Parameter(
            name: 'search',
            in: 'query',
            required: false,
            description: 'Từ khóa tìm kiếm',
            schema: new OA\Schema(type: 'string')
        ),
        new OA\Parameter(
            name: 'status',
            in: 'query',
            required: false,
            description: 'Trạng thái của return history',
            schema: new OA\Schema(type: 'string', enum: ['pending', 'completed', 'lost'])
        )
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get all return histories successfully',
        ),
        new OA\Response(
            response: 400,
            description: 'Dữ liệu không hợp lệ',
        ),
    ],
)]

#[OA\Get(
    path: '/api/v1/admin/return-histories/{id}',
    operationId: 'getReturnHistory',
    summary: 'Get return history',
    description: 'Get return history',
    tags: ['Admin / Return History'],
    security: [
        ['bearerAuth' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            description: 'Id của return history',
            schema: new OA\Schema(type: 'integer')
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get return history successfully',
        ),
        new OA\Response(
            response: 400,
            description: 'Dữ liệu không hợp lệ',
        ),
        new OA\Response(
            response: 500,
            description: 'Failed to get return history',
        ),
    ],
)]

#[OA\Put(
    path: '/api/v1/admin/return-histories/update/{id}',
    operationId: 'updateReturnHistory',
    summary: 'Update return history',
    description: 'Update return history',
    tags: ['Admin / Return History'],
    security: [
        ['bearerAuth' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            description: 'Id của return history',
            schema: new OA\Schema(type: 'integer')
        ),
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['condition', 'fine_amount', 'actual_return_condition'],
            properties: [
                new OA\Property(property: 'condition', type: 'string'),
                new OA\Property(property: 'fine_amount', type: 'number'),
                new OA\Property(property: 'actual_return_condition', type: 'string', enum: ['excellent', 'good', 'fair', 'poor', 'damaged', 'lost']),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Update return history successfully',
        ),
        new OA\Response(
            response: 400,
            description: 'Dữ liệu không hợp lệ',
        ),
        new OA\Response(
            response: 500,
            description: 'Failed to update return history',
        ),
    ],
)]

class ReturnHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'page' => 'integer|min:1',
            'pageSize' => 'integer|min:1',
            'search' => 'string',
            'status' => 'string|in:pending,completed,lost',
        ], [
            'page.integer' => 'Page phải là số nguyên.',
            'pageSize.integer' => 'PageSize phải là số nguyên.',
            'page.min' => 'Page phải lớn hơn hoặc bằng 1.',
            'pageSize.min' => 'PageSize phải lớn hơn hoặc bằng 1.',
            'search.string' => 'Search phải là chuỗi.',
            'status.string' => 'Status phải là chuỗi.',
            'status.in' => 'Status phải là pending, completed hoặc lost.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Dữ liệu không hợp lệ",
                "errors" => $validator->errors()
            ], 400);
        }

        $page = $request->input('page', 1);
        $pageSize = $request->input('pageSize', 10);
        $search = $request->input('search');
        $status = $request->input('status');

        try {
            $query = ReturnHistory::query()->with([
                'loanOrderDetail',
                'loanOrderDetail.loanOrder',
                'loanOrderDetail.loanOrder.user',
                'loanOrderDetail.bookDetails',
                'loanOrderDetail.bookDetails.book',
                'processedBy',
                'shippingMethod'
            ]);

            $totalItems = $query->count();

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('loanOrderDetail.loanOrder', function ($q) use ($search) {
                        $q->where('order_code', 'like', '%' . $search . '%')
                            ->orWhereHas('user', function ($q) use ($search) {
                                $q->where('fullname', 'like', '%' . $search . '%')
                                    ->orWhere('email', 'like', '%' . $search . '%')
                                    ->orWhere('phone', 'like', '%' . $search . '%');
                            });
                    });
                });
            }

            if ($status) {
                $query->where('status', $status);
            }

            $returnHistory = $query->orderBy('created_at', 'desc')->paginate($pageSize, ['*'], 'page', $page);

            return response()->json([
                "status" => true,
                "message" => "Get all return histories successfully!",
                "data" => [
                    "returnHistory" => $returnHistory->items(),
                    "page" => $returnHistory->currentPage(),
                    "pageSize" => $returnHistory->perPage(),
                    "totalPages" => $returnHistory->lastPage(),
                    "totalResults" => $returnHistory->total(),
                    "total" => $totalItems
                ],
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to get all return histories',
                'errors' => $th->getMessage()
            ], 500);
        }
    }

    public function show(ReturnHistory $returnHistory, $id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|exists:return_histories,id',
        ], [
            'id.required' => 'Id không được để trống',
            'id.exists' => 'Id không tồn tại',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Dữ liệu không hợp lệ",
                "errors" => $validator->errors()
            ], 400);
        }

        try {
            $returnHistory = ReturnHistory::with([
                'loanOrderDetail',
                'loanOrderDetail.extensionsDetails',
                'loanOrderDetail.loanOrder',
                'loanOrderDetail.loanOrder.user',
                'loanOrderDetail.bookDetails',
                'loanOrderDetail.bookDetails.book',
                'processedBy',
                'shippingMethod',

            ])->find($id);

            return response()->json([
                "status" => true,
                "message" => "Get return history successfully!",
                "data" => $returnHistory
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to get return history',
                'errors' => $th->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make(array_merge($request->all(), [
            'id' => $id
        ]), [
            'id' => 'required|exists:return_histories,id',
            'condition' => 'required|string',
            'fine_amount' => 'required|numeric|min:0',
            'actual_return_condition' => 'required|in:excellent,good,fair,poor,damaged,lost',
        ], [
            'id.required' => 'Id không được để trống',
            'id.exists' => 'Id không tồn tại',
            'condition.required' => 'Condition không được để trống',
            'condition.string' => 'Condition phải là chuỗi',
            'fine_amount.numeric' => 'Fine amount phải là số',
            'fine_amount.min' => 'Fine amount phải lớn hơn hoặc bằng 0',
            'actual_return_condition.required' => 'Actual return condition không được để trống',
            'actual_return_condition.in' => 'Actual return condition không hợp lệ',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Dữ liệu không hợp lệ",
                "errors" => $validator->errors()
            ], 400);
        }

        try {
            $returnHistory = ReturnHistory::find($id);
            $loanOrderDetail = LoanOrderDetails::find($returnHistory->loan_order_details_id);
            $bookDetails = BookDetail::find($loanOrderDetail->book_details_id);
            $order = LoanOrders::with('loanOrderDetails')->find($loanOrderDetail->loan_order_id);

            if ($request->actual_return_condition == 'lost') {
                $returnHistory->update([
                    'condition' => $request->condition,
                    'fine_amount' => $request->fine_amount,
                    'actual_return_condition' => $request->actual_return_condition,
                    'status' => 'lost',
                    'processed_by' => auth()->user()->id,
                    'received_at_library_date' => $request->actual_return_condition == 'lost' ? null : now(),
                ]);

                $loanOrderDetail->update([
                    'status' => 'lost',
                    'fine_amount' => $request->fine_amount,
                    'actual_return_condition' => $request->actual_return_condition,
                    'return_date' => now(),
                    'status' => 'completed'
                ]);

                $bookDetails->update([
                    'stock_broken' => $bookDetails->stock_broken + 1,
                ]);
            }

            if ($request->actual_return_condition == 'excellent' || $request->actual_return_condition == 'good' || $request->actual_return_condition == 'fair' || $request->actual_return_condition == 'damaged' || $request->actual_return_condition == 'poor') {
                $returnHistory->update([
                    'condition' => $request->condition,
                    'fine_amount' => $request->fine_amount,
                    'actual_return_condition' => $request->actual_return_condition,
                    'status' => 'completed',
                    'processed_by' => auth()->user()->id,
                    'received_at_library_date' => now(),
                ]);

                $loanOrderDetail->update([
                    'status' => 'completed',
                    'fine_amount' => $request->fine_amount,
                    'actual_return_condition' => $request->actual_return_condition,
                    'return_date' => now(),
                    'status' => 'completed'
                ]);

                $bookDetails->update([
                    'stock' => $bookDetails->stock + 1,
                ]);
            }

            $order->update([
                'total_deposit_fee' => $order->total_deposit_fee - $request->fine_amount,
                'total_fine_fee' => $order->total_fine_fee + $request->fine_amount,
                'total_return_fee' => $order->total_deposit_fee - $request->fine_amount,
            ]);

            // Tìm tất cả các chi tiết đơn hàng liên quan đến đơn hàng
            $loanOrderDetails = LoanOrderDetails::where('loan_order_id', $order->id)->get();

            $flag = true;

            foreach ($loanOrderDetails as $item) {
                // Kiểm tra nếu có bất kỳ sách nào chưa hoàn thành
                if ($item->status != 'completed') {
                    $flag = false;
                    break;
                }
            }

            // Nếu tất cả các sách đều hoàn thành, cập nhật trạng thái đơn hàng
            if ($flag) {
                $order->update([
                    'status' => 'completed',
                    'completed_date' => now(),
                    'total_fine_fee' => LoanOrderDetails::where('loan_order_id', $order->id)->sum('fine_amount'),
                    'total_return_fee' => $order->total_deposit_fee - LoanOrderDetails::where('loan_order_id', $order->id)->sum('fine_amount') > 0 ? $order->total_deposit_fee - LoanOrderDetails::where('loan_order_id', $order->id)->sum('fine_amount') : 0,
                ]);
            }



            return response()->json([
                "status" => true,
                "message" => "Update return history successfully!",
                "data" => $returnHistory
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update return history',
                'errors' => $th->getMessage()
            ], 500);
        }
    }
}

<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\BookReview;
use App\Models\LoanOrderDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

use function PHPUnit\Framework\isEmpty;

#[OA\Post(
    path: '/api/v1/account/book-reviews/create',
    tags: ['Account / Book Review'],
    operationId: 'bookReviewStore',
    summary: 'Store a newly created resource in storage',
    description: 'Store a newly created resource in storage',
    security: [
        ['bearerAuth' => []]
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['loan_order_details_id', 'review_text', 'rating'],
            properties: [
                new OA\Property(property: 'loan_order_details_id', type: 'integer', description: 'Loan order details id'),
                new OA\Property(property: 'review_text', type: 'string', description: 'Review text'),
                new OA\Property(property: 'rating', type: 'integer', description: 'Rating'),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Đánh giá sách thành công',
        ),
        new OA\Response(
            response: 400,
            description: 'Dữ liệu không hợp lệ',
        ),
    ],
)]

class BookReviewController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "loan_order_details_id" => "required|exists:loan_order_details,id",
            "review_text" => "required|string|min:10",
            "rating" => "required|numeric|min:1|max:5",
        ], [
            "loan_order_details_id.required" => "CHi tiết đơn mượn không được để trống",
            "loan_order_details_id.exists" => "CHi tiết đơn mượn không tồn tại",
            "review_text.required" => "Nội dung đánh giá không được để trống",
            "review_text.string" => "Nội dung đánh giá phải là chuỗi",
            "review_text.min" => "Nội dung đánh giá phải có ít nhất 10 ký tự",
            "rating.required" => "Đánh giá không được để trống",
            "rating.numeric" => "Đánh giá phải là số",
            "rating.min" => "Đánh giá phải lớn hơn hoặc bằng 1",
            "rating.max" => "Đánh giá phải nhỏ hơn hoặc bằng 5",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "message" => "Lỗi truyền dữ liệu",
                "errors" => $validator->messages()
            ], 400);
        }

        try {
            $loanOrderDetail = LoanOrderDetails::with(['bookReviews'])->find($request->loan_order_details_id);

            if (!$loanOrderDetail) {
                return response()->json([
                    "status" => false,
                    "message" => "Đơn hàng không tồn tại",
                ], 404);
            }

            if ($loanOrderDetail->status != "completed") {
                return response()->json([
                    "status" => false,
                    "message" => "Không thể đánh giá sách chưa trả",
                ], 400);
            }

            if (!$loanOrderDetail->bookReviews->isEmpty()) {
                return response()->json([
                    "status" => false,
                    "message" => "Đơn hàng đã được đánh giá"
                ], 400);
            }

            $bookReview = BookReview::create([
                "loan_order_details_id" => $loanOrderDetail->id,
                "book_details_id" => $loanOrderDetail->book_details_id,
                "user_id" => auth()->user()->id,
                "review_text" => $request->review_text,
                "rating" => $request->rating,
                "review_date" => now(),
            ]);

            return response()->json([
                "status" => true,
                "message" => "Đánh giá sách thành công",
                "data" => $bookReview,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => "Có lỗi xảy ra, vui lòng thử lại sau",
                "error" => $th->getMessage(),
            ], 500);
        }
    }
}

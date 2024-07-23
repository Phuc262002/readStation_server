<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/admin/book-reviews',
    tags: ['Admin / Book Review'],
    operationId: 'bookReviewIndex',
    summary: 'Get all bookReviews',
    description: 'Get all bookReviews',
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
            name: 'rating',
            in: 'query',
            required: false,
            description: 'Đánh giá',
            schema: new OA\Schema(type: 'integer', enum: [1, 2, 3, 4, 5])
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get all bookReviews successfully!',
        ),
        new OA\Response(
            response: 400,
            description: 'Validation error',
        ),
    ],
)]

class BookReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'page' => 'integer|min:1',
            'pageSize' => 'integer|min:1',
            'rating' => 'integer|min:1|max:5',
        ], [
            'page.integer' => 'Trang phải là số nguyên.',
            'page.min' => 'Trang phải lớn hơn hoặc bằng 1.',
            'pageSize.integer' => 'Kích thước trang phải là số nguyên.',
            'pageSize.min' => 'Kích thước trang phải lớn hơn hoặc bằng 1.',
            'rating.integer' => 'Đánh giá phải là số nguyên.',
            'rating.min' => 'Đánh giá phải lớn hơn hoặc bằng 1.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }

        // Lấy giá trị page và pageSize từ query parameters
        $page = $request->input('page', 1);
        $pageSize = $request->input('pageSize', 10);
        $rating = $request->input('rating');

        // Tạo query ban đầu
        $query = BookReview::query()->with([
            'bookDetail',
            'bookDetail.book',
            'loanOrderDetail',
            'user'
        ]);

        // Lấy tổng số mục trong DB trước khi áp dụng bộ lọc tìm kiếm

        // Áp dụng bộ lọc theo type
        $totalItems = $query->count();

        if ($rating) {
            $query->where('rating', $rating);
        }

        // Thực hiện phân trang
        $bookReviews = $query->orderBy('created_at', 'desc')->paginate($pageSize, ['*'], 'page', $page);

        return response()->json([
            "status" => true,
            "message" => "Get all bookReviews successfully!",
            "data" => [
                "bookReviews" => $bookReviews->items(),
                "page" => $bookReviews->currentPage(),
                "pageSize" => $bookReviews->perPage(),
                "totalPages" => $bookReviews->lastPage(),
                "totalResults" => $bookReviews->total(),
                "total" => $totalItems
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(BookReview $bookReview)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BookReview $bookReview)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BookReview $bookReview)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BookReview $bookReview)
    {
        //
    }
}

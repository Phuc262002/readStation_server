<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookDetail;
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
        new OA\Parameter(
            name: 'status',
            in: 'query',
            required: false,
            description: 'Trạng thái',
            schema: new OA\Schema(type: 'string', enum: ['active', 'inactive'])
        ),
        new OA\Parameter(
            name: 'sort',
            in: 'query',
            required: false,
            description: 'Sắp xếp',
            schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'], default: 'desc')
        ),
        new OA\Parameter(
            name: 'search',
            in: 'query',
            required: false,
            description: 'Tìm kiếm',
            schema: new OA\Schema(type: 'string')
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get all bookReviews successfully!',
        ),
        new OA\Response(
            response: 400,
            description: 'Dữ liệu không hợp lệ',
        ),
    ],
)]

#[OA\Get(
    path: '/api/v1/admin/book-reviews/{book_details_id}',
    tags: ['Admin / Book Review'],
    operationId: 'bookReviewShow',
    summary: 'Get bookReview by book_details_id',
    description: 'Get bookReview by book_details_id',
    security: [
        ['bearerAuth' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'book_details_id',
            in: 'path',
            required: true,
            description: 'ID của book_details',
            schema: new OA\Schema(type: 'integer')
        ),
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
        new OA\Parameter(
            name: 'status',
            in: 'query',
            required: false,
            description: 'Trạng thái',
            schema: new OA\Schema(type: 'string', enum: ['active', 'inactive'])
        ),
        new OA\Parameter(
            name: 'sort',
            in: 'query',
            required: false,
            description: 'Sắp xếp',
            schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'], default: 'desc')
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get bookReview successfully!',
        ),
        new OA\Response(
            response: 400,
            description: 'Dữ liệu không hợp lệ',
        ),
    ],
)]

#[OA\Put(
    path: '/api/v1/admin/book-reviews/{id}',
    tags: ['Admin / Book Review'],
    operationId: 'bookReviewUpdate',
    summary: 'Update bookReview',
    description: 'Update bookReview',
    security: [
        ['bearerAuth' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            description: 'ID của bookReview',
            schema: new OA\Schema(type: 'integer')
        ),

    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['status'],
            properties: [
                new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive'], description: 'Trạng thái', default: 'enum => active | inactive')
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Update bookReview successfully!',
        ),
        new OA\Response(
            response: 400,
            description: 'Dữ liệu không hợp lệ',
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
            'status' => 'in:active,inactive',
            'sort' => 'in:asc,desc',
            'search' => 'string',
        ], [
            'page.integer' => 'Trang phải là số nguyên.',
            'page.min' => 'Trang phải lớn hơn hoặc bằng 1.',
            'pageSize.integer' => 'Kích thước trang phải là số nguyên.',
            'pageSize.min' => 'Kích thước trang phải lớn hơn hoặc bằng 1.',
            'rating.integer' => 'Đánh giá phải là số nguyên.',
            'rating.min' => 'Đánh giá phải lớn hơn hoặc bằng 1.',
            'rating.max' => 'Đánh giá phải nhỏ hơn hoặc bằng 5.',
            'status.in' => 'Trạng thái phải là active hoặc inactive.',
            'sort.in' => 'Sắp xếp phải là asc hoặc desc.',
            'search.string' => 'Tìm kiếm phải là chuỗi ký tự.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Dữ liệu không hợp lệ",
                "errors" => $validator->errors()
            ], 400);
        }

        // Lấy giá trị page và pageSize từ query parameters
        $page = $request->input('page', 1);
        $pageSize = $request->input('pageSize', 10);
        $rating = $request->input('rating');
        $status = $request->input('status');
        $sort = $request->input('sort', 'desc');
        $search = $request->input('search');

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

        // Áp dụng bộ lọc theo status
        if ($status) {
            $query->where('status', $request->status);
        }

        // Áp dụng bộ lọc theo search

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('review_text', 'like', "%$search%")
                    ->orWhereHas('bookDetail', function ($q) use ($search) {
                        $q->whereHas('book', function ($q) use ($search) {
                            $q->where('title', 'like', "%$search%");
                        });
                    })
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('fullname', 'like', "%$search%")
                            ->orWhere('email', 'like', "%$search%")
                            ->orWhere('phone', 'like', "%$search%");
                    });
            });
        }

        // Thực hiện phân trang
        $bookReviews = $query->orderBy('created_at', $sort)->paginate($pageSize, ['*'], 'page', $page);

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

    public function show(Request $request, $book_details_id)
    {
        $validator = Validator::make(['book_details_id' => $book_details_id], [
            'book_details_id' => 'required|exists:book_details,id',
            'page' => 'integer|min:1',
            'pageSize' => 'integer|min:1',
            'rating' => 'integer|min:1|max:5',
            'status' => 'in:active,inactive',
            'sort' => 'in:asc,desc',
        ], [
            'book_details_id.required' => 'ID không được để trống.',
            'book_details_id.exists' => 'ID không tồn tại.',
            'page.integer' => 'Trang phải là số nguyên.',
            'page.min' => 'Trang phải lớn hơn hoặc bằng 1.',
            'pageSize.integer' => 'Kích thước trang phải là số nguyên.',
            'pageSize.min' => 'Kích thước trang phải lớn hơn hoặc bằng 1.',
            'rating.integer' => 'Đánh giá phải là số nguyên.',
            'rating.min' => 'Đánh giá phải lớn hơn hoặc bằng 1.',
            'rating.max' => 'Đánh giá phải nhỏ hơn hoặc bằng 5.',
            'status.in' => 'Trạng thái phải là active hoặc inactive.',
            'sort.in' => 'Sắp xếp phải là asc hoặc desc.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "message" => "Dữ liệu không hợp lệ",
                "errors" => $validator->errors()
            ], 400);
        }

        $page = $request->input('page', 1);
        $pageSize = $request->input('pageSize', 10);
        $rating = $request->input('rating');
        $status = $request->input('status');
        $sort = $request->input('sort', 'desc');

        try {
            $query = BookReview::query()->with([
                'bookDetail',
                'bookDetail.book',
                'loanOrderDetail',
                'user'
            ])->where('book_details_id', $book_details_id);

            // Áp dụng bộ lọc theo type
            $totalItems = $query->count();

            if ($rating) {
                $query->where('rating', $rating);
            }

            // Áp dụng bộ lọc theo status
            if ($status) {
                $query->where('status', $request->status);
            }

            // Thực hiện phân trang
            $bookReviews = $query->orderBy('created_at', $sort)->paginate($pageSize, ['*'], 'page', $page);

            return response()->json([
                "status" => true,
                "message" => "Get bookReview successfully!",
                "data" => [
                    "bookReviews" => $bookReviews->items(),
                    "page" => $bookReviews->currentPage(),
                    "pageSize" => $bookReviews->perPage(),
                    "totalPages" => $bookReviews->lastPage(),
                    "totalResults" => $bookReviews->total(),
                    "total" => $totalItems,
                ]
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => "Get bookReview failed!",
                "errors" => $th->getMessage()
            ], 400);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make(array_merge(
            $request->all(),
            ['id' => $id]
        ), [
            'id' => 'required|exists:book_reviews,id',
            'status' => 'required|in:active,inactive',
        ], [
            'id.required' => 'ID không được để trống.',
            'id.exists' => 'ID không tồn tại.',
            'status.required' => 'Trạng thái không được để trống.',
            'status.in' => 'Trạng thái phải là active hoặc inactive.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "message" => "Dữ liệu không hợp lệ",
                "errors" => $validator->errors()
            ], 400);
        }

        try {
            $bookReview = BookReview::find($id);
            $bookReview->status = $request->status;
            $bookReview->save();
            return response()->json([
                "status" => true,
                "message" => "Update bookReview successfully!",
                "data" => $bookReview
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => "Update bookReview failed!",
                "errors" => $th->getMessage()
            ], 400);
        }
    }
}

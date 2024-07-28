<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\BookReview;
use App\Models\LoanOrderDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/public/book-reviews/{book_details_id}',
    tags: ['Public / Book Review'],
    operationId: 'getAllBookReviewsPublic',
    summary: 'Get all book reviews public',
    description: 'Get all book reviews',
    parameters: [
        new OA\Parameter(
            name: 'book_details_id',
            in: 'path',
            required: true,
            description: 'ID của sách chi tiết',
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
            description: 'Get all bookReviews successfully',
        ),
        new OA\Response(
            response: 400,
            description: 'Lỗi truyền dữ liệu',
        ),
        new OA\Response(
            response: 500,
            description: 'Có lỗi xảy ra, vui lòng thử lại sau',
        ),
    ],
)]

class BookReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, $book_details_id)
    {
        $validator = Validator::make(array_merge(
            $request->all(),
            ["book_details_id" => $book_details_id]
        ), [
            "book_details_id" => "required|exists:book_details,id",
            "page" => "nullable|numeric|min:1",
            "limit" => "nullable|numeric|min:1",
            "rating" => "nullable|numeric|min:1|max:5",
            "sort" => "nullable|in:asc,desc",
        ], [
            "page.required" => "Trang không được để trống",
            "page.numeric" => "Trang phải là số",
            "page.min" => "Trang phải lớn hơn hoặc bằng 1",
            "limit.required" => "Giới hạn không được để trống",
            "limit.numeric" => "Giới hạn phải là số",
            "limit.min" => "Giới hạn phải lớn hơn hoặc bằng 1",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "message" => "Lỗi truyền dữ liệu",
                "errors" => $validator->messages()
            ], 400);
        }

        $page = $request->input('page', 1);
        $pageSize = $request->input('pageSize', 10);
        $rating = $request->input('rating', null);
        $sort = $request->input('sort', 'desc');

        try {
            $query = BookReview::with(['user']);

            if ($rating) {
                $query->where('rating', $rating);
            }

            $bookReviews = $query->where('book_details_id', $book_details_id)->where('status', 'active');

            $totalItems = $query->count();
            $bookReviews = $query->orderBy('review_date', $sort)->paginate($pageSize, ['*'], 'page', $page);

            $bookReviews->getCollection()->transform(function ($post) {
                unset($post->content);
                return array_merge($post->toArray(), [
                    "user" => $post->user->only(['fullname', 'avatar', 'gender', 'job', 'story']),
                ]);
            });
    
            return response()->json([
                "status" => true,
                "message" => "Get all bookReviews successfully",
                "data" => [
                    'bookReviews' => $bookReviews->items(),
                    'page' => $page,
                    'pageSize' => $pageSize,
                    'lastPage' => $bookReviews->lastPage(),
                    'totalResults' => $totalItems,
                    'total' =>  $bookReviews->total()
                ]
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => "Có lỗi xảy ra, vui lòng thử lại sau",
                "error" => $th->getMessage(),
            ], 500);
        }
    }
}

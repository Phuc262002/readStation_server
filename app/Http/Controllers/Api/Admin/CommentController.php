<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/comments/admin/get-all',
    operationId: 'getCommentAdmin',
    tags: ['Admin / Comment'],
    summary: 'Get comments',
    description: 'Get comments',
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
            name: 'status',
            in: 'query',
            required: false,
            description: 'Trạng thái của bình luận',
            schema: new OA\Schema(type: 'string', enum: ['published', 'banned', 'hidden'])
        )
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'User profile fetched successfully'
        ),
        new OA\Response(
            response: 400,
            description: 'Validation error'
        )
    ]
)]


class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $validator = validator($request->all(), [
            'page' => 'integer',
            'pageSize' => 'integer',
            'status' => 'in:published,banned,hidden'
        ], [
            'page.integer' => 'Trang phải là số nguyên',
            'pageSize.integer' => 'Số lượng mục trên mỗi trang phải là số nguyên',
            'status.in' => 'Trạng thái không hợp lệ'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => true, // Sửa lỗi chính tả ở đây
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }

        $page = $request->input('page', 1);
        $pageSize = $request->input('pageSize', 10);
        $request->input('published'); // Thêm dấu chấm phẩy ở đây

        $query = Comment::query()->with('user', 'post', 'post.user', 'get_parent_comment');

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        $comments = $query->orderBy('created_at', 'desc')->paginate($pageSize, ['*'], 'page', $page);

        return response()->json([
            'status' => true,
            'message' => 'Lấy danh sách bình luận thành công',
            'data' => [
                'comments' => $comments->items(),
                'page' => $page,
                'pageSize' => $pageSize,
                'lastPage' => $comments->lastPage(),
                'totalResults' => $comments->count(), // Sửa lỗi sử dụng hàm count()
                'total' =>  $comments->total()
            ]
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
    public function show(Comment $comment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Comment $comment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Comment $comment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Comment $comment)
    {
        //
    }
}

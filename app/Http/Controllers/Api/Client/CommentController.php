<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/comments',
    operationId: 'getComment',
    tags: ['Comment'],
    summary: 'Get comments',
    description: 'Get comments',
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
            name: 'post_id',
            in: 'query',
            required: true,
            description: 'Id của bài viết',
            schema: new OA\Schema(type: 'integer')
        ),
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

#[OA\Post(
    path: '/api/v1/comments/create',
    operationId: 'createComment',
    tags: ['Comment'],
    summary: 'Create comment',
    description: 'Create comment',
    security: [
        ['bearerAuth' => []]
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['post_id', 'content'],
            properties: [
                new OA\Property(property: 'post_id', type: 'integer'),
                new OA\Property(property: 'content', type: 'string'),
                new OA\Property(property: 'parent_id', default: null, type: 'string', nullable: true)
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Comment created successfully'
        ),
        new OA\Response(
            response: 400,
            description: 'Validation error'
        ),
        new OA\Response(
            response: 500,
            description: 'Comment created failed'
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
            'post_id' => 'required|exists:posts,id',
            'page' => 'integer',
            'pageSize' => 'integer'
        ], [
            'post_id.required' => 'Post id là bắt buộc',
            'post_id.exists' => 'Post id không tồn tại',
            'page.integer' => 'Trang phải là số nguyên',
            'pageSize.integer' => 'Số lượng mục trên mỗi trang phải là số nguyên'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => true,
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }

        $page = $request->input('page', 1);
        $pageSize = $request->input('pageSize', 10);

        // Định nghĩa một hàm đệ quy để lấy tất cả các comment, bao gồm cả các comment cấp 2
        function getAllComments($postId, $parentId = null, $pageSize = 10, $page = 1)
        {
            $query = Comment::where('post_id', $postId)
                ->where('status', 'published')
                ->where('parent_id', $parentId)
                ->with(['user' => function ($query) {
                    $query->select('id', 'fullname', 'avatar');
                }])
                ->select('id', 'parent_id', 'content', 'created_at', 'updated_at', 'user_id')
                ->orderBy('created_at', 'desc');

            // Thực hiện phân trang
            $comments = $query->paginate($pageSize, ['*'], 'page', $page);

            $commentsArray = [];

            foreach ($comments as $comment) {
                $commentObj = [
                    'id' => $comment->id,
                    'parent_id' => $comment->parent_id,
                    'content' => $comment->content,
                    'created_at' => $comment->created_at,
                    'updated_at' => $comment->updated_at,
                    'user' => [
                        'fullname' => $comment->user->fullname,
                        'avatar' => $comment->user->avatar
                    ]
                ];

                // Gọi đệ quy để lấy các comment cấp 2 của comment hiện tại
                $children = getAllComments($postId, $comment->id, $pageSize, $page);
                if (!empty($children)) {
                    $commentObj['replies'] = $children;
                }

                array_push($commentsArray, $commentObj);
            }

            return $commentsArray;
        }

        // Gọi hàm để lấy tất cả các comment
        $allComments = getAllComments($request->post_id, null, $pageSize, $page);

        $pgn = Comment::where('post_id', $request->post_id)
            ->where('status', 'published')
            ->whereNull('parent_id')
            ->paginate($pageSize, ['*'], 'page', $page);

        return response()->json([
            "status" => true,
            "message" => "Get comments successfully",
            "data" => [
                'comments' => $allComments,
                'page' => $page,
                'pageSize' => $pageSize,
                'lastPage' => $pgn->lastPage(),
                'totalResults' => count($allComments),
                'total' =>  $pgn->total()
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = validator($request->all(), [
            'post_id' => 'required|exists:posts,id',
            'content' => 'required|string',
            'parent_id' => 'exists:comments,id'
        ], [
            'post_id.required' => 'Post id là bắt buộc',
            'post_id.exists' => 'Post id không tồn tại',
            'content.required' => 'Nội dung bình luận là bắt buộc',
            'content.string' => 'Nội dung bình luận phải là chuỗi',
            'parent_id.integer' => 'Parent id phải là số nguyên',
            'parent_id.exists' => 'Parent id không tồn tại'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }

        try {
            $comment = Comment::create([
                'post_id' => $request->post_id,
                'content' => $request->content,
                'parent_id' => $request->parent_id,
                'user_id' => auth()->user()->id,
            ]);

            return response()->json([
                "status" => true,
                "message" => "Comment created successfully",
                "data" => $comment
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => "Comment created failed",
                "errors" => $th->getMessage()
            ], 500);
        }
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

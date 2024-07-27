<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/admin/posts',
    operationId: 'adminPostIndex',
    summary: 'Danh sách bài viết',
    description: 'Lấy danh sách bài viết',
    tags: ['Admin / Post'],
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
            name: 'category_id',
            in: 'query',
            required: false,
            description: 'Id của category',
            schema: new OA\Schema(type: 'integer')
        ),
        new OA\Parameter(
            name: 'status',
            in: 'query',
            required: false,
            description: 'Trạng thái bài viết',
            schema: new OA\Schema(type: 'string', enum: ['wating_approve', 'approve_canceled', 'draft', 'published', 'hidden', 'deleted'])
        ),
        new OA\Parameter(
            name: 'type',
            in: 'query',
            required: false,
            description: 'Loại sách (member, manager)',
            schema: new OA\Schema(type: 'string', enum: ['member', 'manager'])
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get all posts successfully!',
        ),
        new OA\Response(
            response: 400,
            description: 'Dữ liệu không hợp lệ',
        ),
    ],
)]

#[OA\Put(
    path: '/api/v1/admin/posts/update/{id}',
    operationId: 'adminPostUpdate',
    tags: ['Admin / Post'],
    summary: 'Update a post',
    description: 'Update a post',
    security: [
        ['bearerAuth' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            description: 'ID của bài viết',
            schema: new OA\Schema(type: 'integer')
        ),
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['status', 'reason_cancel'],
            properties: [
                new OA\Property(property: 'status', type: 'string', enum: ['published', 'approve_canceled']),
                new OA\Property(property: 'reason_cancel', type: 'string', nullable: true, default: null),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Update post successfully!',
        ),
        new OA\Response(
            response: 500,
            description: 'Update post request failed',
        ),
    ]
)]


class PostController extends Controller
{
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'page' => 'integer|min:1',
            'pageSize' => 'integer|min:1',
            'search' => 'string',
            'status' => 'string|in:wating_approve,approve_canceled,draft,published,hidden,deleted,handle',
            'category_id' => 'integer',
            'type' => 'string|in:member,manager',
        ], [
            'page.integer' => 'Page phải là số nguyên.',
            'pageSize.integer' => 'PageSize phải là số nguyên.',
            'status.in' => 'Trạng thái phải là wating_approve, approve_canceled, draft, published, hidden, handle hoặc deleted.',
            'type.in' => 'Loại phải là member hoặc manager.',
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
        $search = $request->input('search');
        $category_id = $request->input('category_id');
        $type = $request->input('type');
        $status = $request->input('status');

        // Tạo query ban đầu
        $query = Post::query()->with(['user', 'category']);

        $totalItems = $query->count();
        $query = $query->filter($category_id, $status, $type);

        // Áp dụng bộ lọc tìm kiếm nếu có tham số tìm kiếm
        $query = $query->search($search);

        // Thực hiện phân trang
        $posts = $query->orderBy('created_at', 'desc')->paginate($pageSize, ['*'], 'page', $page);

        $posts->getCollection()->transform(function ($post) {
            unset($post->content);
            return array_merge($post->toArray(), [
                "user" => $post->user->only(['fullname', 'avatar', 'gender', 'job', 'story']),
            ]);
        });

        return response()->json([
            "status" => true,
            "message" => "Get all posts successfully!",
            "data" => [
                "posts" => $posts->items(),
                "page" => $posts->currentPage(),
                "pageSize" => $posts->perPage(),
                "totalPages" => $posts->lastPage(),
                "totalResults" => $posts->total(),
                "total" => $totalItems
            ],
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make(array_merge(['id' => $id], $request->all()), [
            'id' => 'required|exists:posts,id',
            'status' => 'required|in:published,approve_canceled',
            'reason_cancel' => 'required_if:status,approve_canceled',
        ], [
            'id.required' => 'Vui lòng nhập ID',
            'id.exists' => 'ID không tồn tại',
            'status.required' => 'Vui lòng nhập trạng thái',
            'status.in' => 'Trạng thái phải là published hoặc approve_canceled',
            'reason_cancel.required_if' => 'Vui lòng nhập lý do từ chối',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "message" => "Dữ liệu không hợp lệ",
                "errors" => $validator->errors()
            ], 400);
        }

        try {
            $post = Post::find($id);

            if (!$post) {
                return response()->json([
                    "status" => false,
                    "message" => "Post not found!",
                ], 404);
            }

            if ($post->status == 'approve_canceled') {
                return response()->json([
                    "status" => false,
                    "message" => "Post has been canceled!",
                ], 400);
            }

            if ($post->status == 'published') {
                $post->update([
                    'status' => $request->status,
                ]);
            } else {
                $post->update([
                    'status' => $request->status,
                    'reason_cancel' => $request->reason_cancel,
                ]);
            }

            return response()->json([
                "status" => true,
                "message" => "Update post successfully!",
                "data" => $post,
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => "Approved post failed!",
                "errors" => $th->getMessage(),
            ], 500);
        }
    }
}

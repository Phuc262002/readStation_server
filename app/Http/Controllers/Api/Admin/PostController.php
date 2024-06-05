<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/posts/admin/get-all',
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
            schema: new OA\Schema(type: 'string', enum: ['wating_approve', 'draft', 'published', 'hidden', 'deleted'])
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
            description: 'Validation error',
        ),
    ],
)]

class PostController extends Controller
{
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'page' => 'integer|min:1',
            'pageSize' => 'integer|min:1',
            'search' => 'string',
            'status' => 'string|in:wating_approve,draft,published,hidden,deleted',
            'category_id' => 'integer',
            'type' => 'string|in:member,manager',
        ], [
            'page.integer' => 'Page phải là số nguyên.',
            'pageSize.integer' => 'PageSize phải là số nguyên.',
            'status.in' => 'Trạng thái không hợp lệ.',
            'type.in' => 'Loại không hợp lệ.',
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
        $search = $request->input('search');
        $category_id = $request->input('category_id');
        $type = $request->input('type');
        $status = $request->input('status');

        // Tạo query ban đầu
        $query = Post::query()->with(['user', 'category']);;

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
}

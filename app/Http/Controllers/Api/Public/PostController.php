<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/public/posts',
    tags: ['Public / Post'],
    operationId: 'getAllPosts',
    summary: 'Get all posts',
    description: 'Get all posts with pagination',
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
            name: 'category_id',
            in: 'query',
            required: false,
            description: 'ID của category',
            schema: new OA\Schema(type: 'integer')
        ),
        new OA\Parameter(
            name: 'sort',
            in: 'query',
            required: false,
            description: 'Sắp xếp theo thời gian tạo hoặc lượt xem',
            schema: new OA\Schema(type: 'string', enum: ['asc', 'desc', 'popular'], default: 'desc')
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
    ]
)]

#[OA\Get(
    path: '/api/v1/public/posts/{slug}',
    tags: ['Public / Post'],
    operationId: 'getPostPublic',
    summary: 'Get a post by slug',
    description: 'Get a post by slug',
    parameters: [
        new OA\Parameter(
            name: 'slug',
            in: 'path',
            required: true,
            description: 'Slug của post',
            schema: new OA\Schema(type: 'string')
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get post successfully!',
        ),
        new OA\Response(
            response: 404,
            description: 'Post not found',
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
            'category_id' => 'integer',
            'sort' => 'string:in:asc,desc,popular'
        ],[
            'page.integer' => 'Trang phải là số nguyên.',
            'page.min' => 'Trang phải lớn hơn hoặc bằng 1.',
            'pageSize.integer' => 'Kích thước trang phải là số nguyên.',
            'pageSize.min' => 'Kích thước trang phải lớn hơn hoặc bằng 1.',
            'category_id.integer' => 'Category_id phải là một số nguyên.',
            'sort.string' => 'Sort phải là một chuỗi.',
            'sort.in' => 'Sort phải là một trong các giá trị: asc, desc, popular.'
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
        $category_id = $request->input('category_id');
        $sort = $request->input('sort', 'desc');

        // Tạo query ban đầu
        $query = Post::query()->with(['user', 'category']);
        $totalItems = $query->count();

        $query->where('status', 'published');

        if ($category_id) {
            $query->where('category_id', $category_id);
        }

        if ($sort === 'popular') {
            $query->orderBy('view', 'desc');
        } else {
            $query->orderBy('created_at', $sort);
        }

        // Lấy tổng số mục trong DB trước khi áp dụng bộ lọc tìm kiếm

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

    public function show(Request $request)
    {

        $slug = $request->route('slug');

        $validator = Validator::make(['slug' => $slug], [
            'slug' => 'required',
        ]);

        $customMessages = [
            'slug.required' => 'Slug không được để trống.',
        ];

        $validator->setCustomMessages($customMessages);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }
        $post = Post::with('user', 'category')->where('slug', $slug)->where('status', 'published')->first();

        if (!$post) {
            return response()->json([
                "status" => false,
                "message" => "Post not found",
            ], 404);
        } else if ($post->status != 'published') {
            return response()->json([
                "status" => false,
                "message" => "Post not found",
            ], 404);
        } else {
            $post->increment('view');
        }

        return response()->json([
            "status" => true,
            "message" => "Get post successfully!",
            "data" => array_merge($post->toArray(), [
                "user" => $post->user->only(['fullname', 'avatar', 'gender', 'job', 'story']),
            ])
        ], 200);
    }
}

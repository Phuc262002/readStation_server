<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'page' => 'integer|min:1',
            'pageSize' => 'integer|min:1',
            'category_id' => 'integer',
        ]);

        $customMessages = [
            'page.integer' => 'Trang phải là số nguyên.',
            'page.min' => 'Trang phải lớn hơn hoặc bằng 1.',
            'pageSize.integer' => 'Kích thước trang phải là số nguyên.',
            'pageSize.min' => 'Kích thước trang phải lớn hơn hoặc bằng 1.',
            'category_id.integer' => 'Category_id phải là một số nguyên.',
        ];

        $validator->setCustomMessages($customMessages);

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

        // Tạo query ban đầu
        $query = Post::query()->with(['user', 'category']);
        $totalItems = $query->count();

        $query->where('status', 'active');
        // Áp dụng bộ lọc theo category_id
        if ($category_id) {
            $query->where('category_id', $category_id);
        }

        // Lấy tổng số mục trong DB trước khi áp dụng bộ lọc tìm kiếm

        $posts = $query->orderBy('created_at', 'desc')->paginate($pageSize, ['*'], 'page', $page);

        $posts->getCollection()->transform(function ($post) {
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
                "lastPage" => $posts->lastPage(),
                "totalResults" => $posts->total(),
                "total" => $totalItems
            ],
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|integer',
            'title' => 'required|string|max:255',
            "content" => "required|string",
            "summary" => "required|string",
            "image" => "required|string",
            "status" => "string|in:published,draft",
        ]);

        $customMessages = [
            'category_id.required' => 'Category_id không được để trống.',
            'category_id.integer' => 'Category_id phải là một số nguyên.',
            'title.required' => 'Title không được để trống.',
            'title.string' => 'Title phải là một chuỗi.',
            'title.max' => 'Title không được vượt quá 255 ký tự.',
            'content.required' => 'Content không được để trống.',
            'content.string' => 'Content phải là một chuỗi.',
            'summary.required' => 'Summary không được để trống.',
            'summary.string' => 'Summary phải là một chuỗi.',
            'image.required' => 'Image không được để trống.',
            'image.string' => 'Image phải là một chuỗi.',
            'status.required' => 'Status không được để trống.',
            'status.in' => 'Status phải là published hoặc draft.',
        ];

        $validator->setCustomMessages($customMessages);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }

        try {
            $post = Post::create(array_merge(
                $validator->validated(),
                ["user_id" => auth()->user()->id]
            ));

            return response()->json([
                "status" => true,
                "message" => "Create post successfully!",
                "data" => $post
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => "Create post failed!",
                "errors" => $th->getMessage()
            ], 500);
        }
    }

    public function show(Post $post)
    {
        return response()->json([
            "status" => true,
            "message" => "Get post successfully!",
            "data" => $post
        ], 200);
    }

    public function update(Request $request, Post $post)
    {
        //
    }

    public function destroy(Post $post)
    {
        //
    }
}

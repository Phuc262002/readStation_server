<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/admin/categories',
    tags: ['Admin / Category'],
    operationId: 'getAllCategory',
    summary: 'Get all categories (admin)',
    description: 'Get all categories',
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
            name: 'type',
            in: 'query',
            required: true,
            description: 'Loại danh mục',
            schema: new OA\Schema(enum: ['book', 'post'], default: 'book')
        ),
        new OA\Parameter(
            name: 'status',
            in: 'query',
            required: false,
            description: 'Loại danh mục',
            schema: new OA\Schema(enum: ['active', 'inactive', 'deleted'])
        ),
        new OA\Parameter(
            name: 'search',
            in: 'query',
            required: false,
            description: 'Từ khóa tìm kiếm',
            schema: new OA\Schema(type: 'string')
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get all categories successfully!',
        ),
        new OA\Response(
            response: 400,
            description: 'Dữ liệu không hợp lệ',
        ),
    ],
)]

#[OA\Get(
    path: '/api/v1/admin/categories/{id}',
    tags: ['Admin / Category'],
    operationId: 'getCategory',
    summary: 'Get a category',
    description: 'Get a category',
    security: [
        ['bearerAuth' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            description: 'ID của danh mục',
            schema: new OA\Schema(type: 'integer')
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get category successfully!',
        ),
        new OA\Response(
            response: 400,
            description: 'Dữ liệu không hợp lệ',
        ),
        new OA\Response(
            response: 404,
            description: 'Category not found!',
        ),
    ],
)]

#[OA\Post(
    path: '/api/v1/admin/categories/create',
    tags: ['Admin / Category'],
    operationId: 'createCategory',
    summary: 'Create a category',
    description: 'Create a category',
    security: [
        ['bearerAuth' => []]
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['name', 'type'],
            properties: [
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'description', type: 'string'),
                new OA\Property(property: 'type', type: 'string', enum: ['book', 'post']),
                new OA\Property(property: 'is_featured', type: 'boolean', default: false),
                new OA\Property(property: 'image', type: 'string'),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Create category successfully!',
        ),
        new OA\Response(
            response: 400,
            description: 'Dữ liệu không hợp lệ',
        ),
    ],
)]

#[OA\Put(
    path: '/api/v1/admin/categories/update/{id}',
    tags: ['Admin / Category'],
    operationId: 'updateCategory',
    summary: 'Update a category',
    description: 'Update a category',
    security: [
        ['bearerAuth' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            description: 'ID của danh mục',
            schema: new OA\Schema(type: 'integer')
        ),
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['name', 'type', 'status'],
            properties: [
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'description', type: 'string'),
                new OA\Property(property: 'type', type: 'string', enum: ['book', 'post']),
                new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive', 'deleted']),
                new OA\Property(property: 'is_featured', type: 'boolean', default: false),
                new OA\Property(property: 'image', type: 'string'),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Update category successfully!',
        ),
        new OA\Response(
            response: 400,
            description: 'Dữ liệu không hợp lệ',
        ),
        new OA\Response(
            response: 404,
            description: 'Category not found!',
        ),
        new OA\Response(
            response: 500,
            description: 'Update category failed!',
        ),
    ],
)]

#[OA\Delete(
    path: '/api/v1/admin/categories/delete/{id}',
    tags: ['Admin / Category'],
    operationId: 'deleteCategory',
    summary: 'Delete a category',
    description: 'Delete a category',
    security: [
        ['bearerAuth' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            description: 'ID của danh mục',
            schema: new OA\Schema(type: 'integer')
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Delete category successfully!',
        ),
        new OA\Response(
            response: 400,
            description: 'Dữ liệu không hợp lệ',
        ),
        new OA\Response(
            response: 404,
            description: 'Category not found!',
        ),
        new OA\Response(
            response: 500,
            description: 'Delete category failed!',
        ),
    ],
)]

class CategoryController extends Controller
{
    public function getAllCategory(Request $request)
    {
        // Validate request parameters
        $validator = Validator::make($request->all(), [
            'page' => 'integer|min:1',
            'pageSize' => 'integer|min:1',
            'search' => 'string',
            'status' => 'string|in:active,inactive,deleted',
            'type' => 'required|string|in:book,post'
        ],[
            'page.integer' => 'Trang phải là số nguyên.',
            'page.min' => 'Trang phải lớn hơn hoặc bằng 1.',
            'pageSize.integer' => 'Kích thước trang phải là số nguyên.',
            'pageSize.min' => 'Kích thước trang phải lớn hơn hoặc bằng 1.',
            'search.string' => 'Tìm kiếm phải là chuỗi.',
            'type.required' => 'Tên loại là bắt buộc.',
            'type.string' => 'Loại phải là một chuỗi.',
            'type.in' => 'Loại phải là book hoặc post.',
            'status.in' => 'Trạng thái  phải là hoạt động, ẩn hoặc đã xóa',
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
        $type = $request->input('type');
        $status = $request->input('status');

        // Tạo query ban đầu
        $query = Category::query();

        // Lấy tổng số mục trong DB trước khi áp dụng bộ lọc tìm kiếm

        // Áp dụng bộ lọc theo type
        $totalItems = $query->count();
        $query = $query->filter($type, $status, true);

        // Áp dụng bộ lọc tìm kiếm nếu có tham số tìm kiếm
        $query = $query->search($search);

        // Thực hiện phân trang
        $categories = $query->orderBy('created_at', 'desc')->paginate($pageSize, ['*'], 'page', $page);

        $categories->getCollection()->transform(function ($category) {
            return [
                array_merge($category->toArray(), [
                    'total_books' => Book::where('category_id', $category->id)->count()
                ])
            ];
        });

        return response()->json([
            "status" => true,
            "message" => "Get all categories successfully!",
            "data" => [
                "categories" => $categories->items(),
                "page" => $categories->currentPage(),
                "pageSize" => $categories->perPage(),
                "totalPages" => $categories->lastPage(),
                "totalResults" => $categories->total(),
                "total" => $totalItems
            ],
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'description' => 'nullable|string',
            'is_featured' => 'boolean',
            'image' => 'nullable|string',
            'type' => 'required|string|in:book,post'
        ],[
            'name.required' => 'Tên danh mục là bắt buộc.',
            'name.string' => 'Tên danh mục phải là một chuỗi.',
            'type.required' => 'Tên loại là bắt buộc.',
            'type.string' => 'Loại phải là một chuỗi.',
            'type.in' => 'Loại phải là book hoặc post.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Dữ liệu không hợp lệ",
                "errors" => $validator->errors()
            ], 400);
        }

        if ($request->is_featured == true && $request->image == null) {
            return response()->json([
                "status" => false,
                "message" => "Ảnh là bắt buộc khi danh mục được chọn là nổi bật!"
            ], 400);
        } else if ($request->is_featured == true && Category::where('is_featured', true)->where('type', 'book')->count() >= 6) {
            return response()->json([
                "status" => false,
                "message" => "Chỉ có thể chọn tối đa 6 danh mục nổi bật!"
            ], 400);
        }

        $category = Category::create(array_merge(
            $validator->validated(),
        ));

        return response()->json([
            "status" => true,
            "message" => "Create category successfully!",
            "data" => $category
        ], 200);
    }

    public function show($id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|integer|min:1'
        ],[
            'id.required' => 'Trường id là bắt buộc.',
            'id.integer' => 'Id phải là một số nguyên.',
            'id.min' => 'Id phải lớn hơn hoặc bằng 1.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Dữ liệu không hợp lệ",
                "errors" => $validator->errors()
            ], 400);
        }

        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                "status" => false,
                "message" => "Category not found!"
            ], 404);
        }

        return response()->json([
            "status" => true,
            "message" => "Get category successfully!",
            "data" => $category
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make(array_merge(['id' => $id], $request->all()), [
            'id' => 'required|integer|min:1|exists:categories,id',
            'name' => 'string',
            'type' => 'string|in:book,post',
            'is_featured' => 'boolean',
            'image' => 'nullable|string',
            'description' => 'nullable|string',
            'status' => 'string|in:active,inactive,deleted',
        ],[
            'id.integer' => 'Id phải là một số nguyên.',
            'id.min' => 'Id phải lớn hơn hoặc bằng 1.',
            'id.exists' => 'Id không tồn tại.',
            'name.string' => 'Tên nhà xuất bản phải là một chuỗi.',
            'type.string' => 'Loại phải là một chuỗi.',
            'type.in' => 'Loại phải là book hoặc post.',
            'status.in' => 'Trạng thái phải là hoạt động, ẩn hoặc đã xóa',
            'status.string' => 'Trạng thái phải là một chuỗi.',
            'description.string' => 'Nội dung phải là một chuỗi.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Dữ liệu không hợp lệ",
                "errors" => $validator->errors()
            ], 400);
        }

        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                "status" => false,
                "message" => "Category not found!"
            ], 404);
        }

        if ($request->is_featured == true && $request->image == null) {
            return response()->json([
                "status" => false,
                "message" => "Image is required when is_featured is true!"
            ], 400);
        } else if ($request->is_featured == true && $category->is_featured != true && Category::where('is_featured', true)->count() >= 6) {
            return response()->json([
                "status" => false,
                "message" => "Only 6 categories can be featured!"
            ], 400);
        }


        if ($request->status == 'inactive' && $category->is_featured == true) {
            return response()->json([
                "status" => false,
                "message" => "Không thể ẩn danh mục nổi bật!"
            ], 400);
        }

        try {
            $category->update($validator->validated());

            return response()->json([
                "status" => true,
                "message" => "Update category successfully!",
                "data" => $category
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => "Update category failed!"
            ], 500);
        }
    }

    public function destroy($id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|integer|min:1|exists:categories,id'
        ],[
            'id.required' => 'Trường id là bắt buộc.',
            'id.integer' => 'Id phải là một số nguyên.',
            'id.min' => 'Id phải lớn hơn hoặc bằng 1.',
            'id.exists' => 'Id không tồn tại.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Dữ liệu không hợp lệ",
                "errors" => $validator->errors()
            ], 400);
        }

        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                "status" => false,
                "message" => "Category not found!"
            ], 404);
        } elseif ($category->is_featured == true) {
            return response()->json([
                "status" => false,
                "message" => "Không thể xóa danh mục nổi bật!"
            ], 400);
        }

        try {
            $category->delete();

            if ($category->status == 'deleted') {
                return response()->json([
                    "status" => true,
                    "message" => "Danh mục đã thêm vào thùng rác! Bạn có thể khôi phục lại sau này!",
                ], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => "Delete category failed!"
            ], 500);
        }

        return response()->json([
            "status" => true,
            "message" => "Danh mục đã được xóa vĩnh viễn! Bạn không thể khôi phục lại sau này!",
        ], 200);
    }
}

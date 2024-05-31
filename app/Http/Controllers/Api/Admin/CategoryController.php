<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/categories/admin/get-all',
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
            description: 'Validation error',
        ),
    ],
)]

#[OA\Get(
    path: '/api/v1/categories/get-one/{id}',
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
            description: 'Validation error',
        ),
        new OA\Response(
            response: 404,
            description: 'Category not found!',
        ),
    ],
)]

#[OA\Post(
    path: '/api/v1/categories/create',
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
                new OA\Property(property: 'type', type: 'string', enum: ['book', 'post'])
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
            description: 'Validation error',
        ),
    ],
)]

#[OA\Put(
    path: '/api/v1/categories/update/{id}',
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
                new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive', 'deleted'])
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
            description: 'Validation error',
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
    path: '/api/v1/categories/delete/{id}',
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
            description: 'Validation error',
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
            'type.required' => 'Trường type là bắt buộc.',
            'type.string' => 'Type phải là một chuỗi.',
            'type.in' => 'Type phải là book hoặc post.',
            'status.in' => 'Status phải là active, inactive hoặc deleted'
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

        return response()->json([
            "status" => true,
            "message" => "Get all categories successfully!",
            "data" => [
                "categories" => $categories->items(),
                "page" => $categories->currentPage(),
                "pageSize" => $categories->perPage(),
                "lastPage" => $categories->lastPage(),
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
            'type' => 'required|string|in:book,post'
        ],[
            'name.required' => 'Trường name là bắt buộc.',
            'name.string' => 'Name phải là một chuỗi.',
            'type.required' => 'Trường type là bắt buộc.',
            'type.string' => 'Type phải là một chuỗi.',
            'type.in' => 'Type phải là book hoặc post.',
            'status.in' => 'Status phải là active, inactive hoặc deleted'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Validation error",
                "errors" => $validator->errors()
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

    public function show(Request $request, Category $category)
    {
        $id = $request->route('id');

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
                "message" => "Validation error",
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

    public function update(Request $request, Category $category)
    {
        $id = $request->route('id');

        $validator = Validator::make(array_merge(['id' => $id], $request->all()), [
            'id' => 'required|integer|min:1',
            'name' => 'required|string',
            'type' => 'required|string|in:book,post',
            'description' => 'nullable|string',
            'status' => 'required|string|in:active,inactive,deleted',
        ],[
            'id.required' => 'Trường id là bắt buộc.',
            'id.integer' => 'Id phải là một số nguyên.',
            'id.min' => 'Id phải lớn hơn hoặc bằng 1.',
            'name.required' => 'Trường name là bắt buộc.',
            'name.string' => 'Name phải là một chuỗi.',
            'type.required' => 'Trường type là bắt buộc.',
            'type.string' => 'Type phải là một chuỗi.',
            'type.in' => 'Type phải là book hoặc post.',
            'status.in' => 'Status phải là active, inactive hoặc deleted',
            'status.required' => 'Trường status là bắt buộc.',
            'status.string' => 'Status phải là một chuỗi.',
            'description.string' => 'Description phải là một chuỗi.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Validation error",
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


        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Validation error",
                "errors" => $validator->errors()
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

    public function destroy(Request $request, Category $category)
    {
        $id = $request->route('id');

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
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }

        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                "status" => false,
                "message" => "Category not found!"
            ], 404);
        } else if ($category->status == 'deleted') {
            return response()->json([
                "status" => false,
                "message" => "Category is not exist!"
            ], 400);
        }

        try {
            $category->delete();
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => "Delete category failed!"
            ], 500);
        }

        return response()->json([
            "status" => true,
            "message" => "Delete category successfully!",
        ], 200);
    }
}

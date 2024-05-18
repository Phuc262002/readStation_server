<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Validate request parameters
        $validator = Validator::make($request->all(), [
            'page' => 'integer|min:1',
            'pageSize' => 'integer|min:1',
            'search' => 'string',
            'status' => 'string|in:active,inactive,deleted',
            'type' => 'required|string|in:book,post'
        ]);

        $customMessages = [
            'page.integer' => 'Trang phải là số nguyên.',
            'page.min' => 'Trang phải lớn hơn hoặc bằng 1.',
            'pageSize.integer' => 'Kích thước trang phải là số nguyên.',
            'pageSize.min' => 'Kích thước trang phải lớn hơn hoặc bằng 1.',
            'type.required' => 'Trường type là bắt buộc.',
            'type.string' => 'Type phải là một chuỗi.',
            'type.in' => 'Type phải là book hoặc post.',
            'status.in' => 'Status phải là active, inactive hoặc deleted'
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
        $search = $request->input('search');
        $type = $request->input('type');
        $status = $request->input('status');

        // Tạo query ban đầu
        $query = Category::query();

        // Lấy tổng số mục trong DB trước khi áp dụng bộ lọc tìm kiếm
        
        // Áp dụng bộ lọc theo type
        $totalItems = $query->count();
        $query = $query->filter($type, $status);

        // Áp dụng bộ lọc tìm kiếm nếu có tham số tìm kiếm
        $query = $query->search($search);

        // Thực hiện phân trang
        $categories = $query->paginate($pageSize, ['*'], 'page', $page);

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

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'description' => 'nullable|string',
            'type' => 'required|string|in:book,post'
        ]);

        $customMessages = [
            'name.required' => 'Trường name là bắt buộc.',
            'name.string' => 'Name phải là một chuỗi.',
            'type.required' => 'Trường type là bắt buộc.',
            'type.string' => 'Type phải là một chuỗi.',
            'type.in' => 'Type phải là book hoặc post.',
            'status.in' => 'Status phải là active, inactive hoặc deleted'
        ];

        $validator->setCustomMessages($customMessages);

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

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Category $category)
    {
        $id = $request->route('id');

        $validator = Validator::make(['id' => $id], [
            'id' => 'required|integer|min:1'
        ]);

        $customMessages = [
            'id.required' => 'Trường id là bắt buộc.',
            'id.integer' => 'Id phải là một số nguyên.',
            'id.min' => 'Id phải lớn hơn hoặc bằng 1.'
        ];

        $validator->setCustomMessages($customMessages);

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

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $id = $request->route('id');

        $validator = Validator::make(array_merge(['id' => $id], $request->all()), [
            'id' => 'required|integer|min:1',
            'name' => 'required|string',
            'type' => 'required|string|in:book,post',
            'description' => 'nullable|string',
            'status' => 'required|string|in:active,inactive,deleted',
        ]);

        $customMessages = [
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
        ];

        $validator->setCustomMessages($customMessages);

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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Category $category)
    {
        $id = $request->route('id');

        $validator = Validator::make(['id' => $id], [
            'id' => 'required|integer|min:1'
        ]);

        $customMessages = [
            'id.required' => 'Trường id là bắt buộc.',
            'id.integer' => 'Id phải là một số nguyên.',
            'id.min' => 'Id phải lớn hơn hoặc bằng 1.'
        ];

        $validator->setCustomMessages($customMessages);

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

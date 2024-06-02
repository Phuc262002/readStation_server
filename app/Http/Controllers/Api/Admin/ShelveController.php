<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shelve;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/shelves',
    tags: ['Admin / Shelve'],
    operationId: 'getAllShelves',
    summary: 'Danh sách kệ sách',
    description: 'Lấy danh sách kệ sách',
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
            description: 'Loại trạng thái',
            schema: new OA\Schema(enum: ['active', 'inactive', 'deleted'])
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
            description: 'ID danh mục',
            schema: new OA\Schema(type: 'integer')
        ),
        new OA\Parameter(
            name: 'bookcase_id',
            in: 'query',
            required: false,
            description: 'ID kệ sách',
            schema: new OA\Schema(type: 'integer')
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get all shelve successfully!',
        ),
        new OA\Response(
            response: 400,
            description: 'Validation error',
        ),
    ],
)]

#[OA\Post(
    path: '/api/v1/shelves/create',
    tags: ['Admin / Shelve'],
    operationId: 'createShelve',
    summary: 'Tạo kệ sách',
    description: 'Tạo mới một kệ sách',
    security: [
        ['bearerAuth' => []]
    ],
    requestBody: new OA\RequestBody(
        required: true,
        description: 'Create new shelve',
        content: new OA\JsonContent(
            required: ['bookcase_id', 'category_id'],
            properties: [
                new OA\Property(property: 'bookcase_id', type: 'integer'),
                new OA\Property(property: 'bookshelf_code', type: 'string'),
                new OA\Property(property: 'category_id', type: 'integer'),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Create shelve successfully!',
        ),
        new OA\Response(
            response: 400,
            description: 'Validation error',
        ),
    ],
)]

class ShelveController extends Controller
{
    public function index(Request $request)
    {
        // Validate request parameters
        $validator = Validator::make($request->all(), [
            'page' => 'integer|min:1',
            'pageSize' => 'integer|min:1',
            'search' => 'string',
            'category_id',
            'bookcase_id',
            'status' => 'string|in:active,inactive,deleted',
        ],[
            'page.integer' => 'Trang phải là số nguyên.',
            'page.min' => 'Trang phải lớn hơn hoặc bằng 1.',
            'pageSize.integer' => 'Kích thước trang phải là số nguyên.',
            'pageSize.min' => 'Kích thước trang phải lớn hơn hoặc bằng 1.',
            'search.string' => 'Tìm kiếm phải là chuỗi.',
            'category_id.integer' => 'Category_id phải là số nguyên.',
            'bookcase_id.integer' => 'Bookcase_id phải là số nguyên.',
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
        $category_id = $request->input('category_id');
        $bookcase_id = $request->input('bookcase_id');
        $status = $request->input('status');

        // Tạo query ban đầu
        $query = Shelve::query();

        // Lấy tổng số mục trong DB trước khi áp dụng bộ lọc tìm kiếm

        // Áp dụng bộ lọc theo type
        $totalItems = $query->count();
        $query = $query->filter($status, $bookcase_id, $category_id);

        // Áp dụng bộ lọc tìm kiếm nếu có tham số tìm kiếm
        $query = $query->search($search);

        // Thực hiện phân trang
        $shelve = $query->orderBy('created_at', 'desc')->paginate($pageSize, ['*'], 'page', $page);

        return response()->json([
            "status" => true,
            "message" => "Get all shelve successfully!",
            "data" => [
                "shelves" => $shelve->items(),
                "page" => $shelve->currentPage(),
                "pageSize" => $shelve->perPage(),
                "lastPage" => $shelve->lastPage(),
                "totalResults" => $shelve->total(),
                "total" => $totalItems
            ],
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bookcase_id' => 'required',
            'bookshelf_code' => 'nullable|string',
            'category_id' => 'required',
        ],[
            'bookcase_id.required' => 'Bookcase_id không được để trống.',
            'bookshelf_code.string' => 'Bookshelf_code phải là chuỗi.',
            'category_id.required' => 'Category_id không được để trống.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }

        $shelve = Shelve::create(array_merge(
            $validator->validated(),
        ));

        return response()->json([
            "status" => true,
            "message" => "Create shelve successfully!",
            "data" => $shelve
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Shelve $shelve)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Shelve $shelve)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Shelve $shelve)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Shelve $shelve)
    {
        //
    }
}

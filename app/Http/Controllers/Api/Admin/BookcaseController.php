<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bookcase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/bookcases',
    tags: ['Admin / Bookcase'],
    operationId: 'getAllBookcases',
    summary: 'Get all bookcases',
    description: 'Get all bookcases with pagination, search and filter',
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
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get all bookcases successfully',
        ),
        new OA\Response(
            response: 400,
            description: 'Validation error',
        ),
    ],
)]

#[OA\Post(
    path: '/api/v1/bookcases/create',
    tags: ['Admin / Bookcase'],
    operationId: 'createBookcase',
    summary: 'Create new bookcase',
    description: 'Create new bookcase',
    security: [
        ['bearerAuth' => []]
    ],
    requestBody: new OA\RequestBody(
        required: true,
        description: 'Create new bookcase',
        content: new OA\JsonContent(
            required: ['bookcase_code', 'description', 'name'],
            properties: [
                new OA\Property(property: 'bookcase_code', type: 'string', default: null, nullable: true),
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'description', type: 'string'),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Create bookcase successfully',
        ),
        new OA\Response(
            response: 400,
            description: 'Validation error',
        ),
    ],
)]

#[OA\Get(
    path: '/api/v1/bookcases/get-one/{id}',
    tags: ['Admin / Bookcase'],
    operationId: 'getBookcase',
    summary: 'Get bookcase by id',
    description: 'Get bookcase by id',
    security: [
        ['bearerAuth' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            description: 'Id của bookcase',
            schema: new OA\Schema(type: 'integer')
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get bookcase successfully',
        ),
        new OA\Response(
            response: 400,
            description: 'Validation error',
        ),
        new OA\Response(
            response: 404,
            description: 'Bookcase not found',
        ),
    ],
)]

#[OA\Put(
    path: '/api/v1/bookcases/update/{id}',
    tags: ['Admin / Bookcase'],
    operationId: 'updateBookcase',
    summary: 'Update bookcase by id',
    description: 'Update bookcase by id',
    security: [
        ['bearerAuth' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            description: 'Id của bookcase',
            schema: new OA\Schema(type: 'integer')
        ),
    ],
    requestBody: new OA\RequestBody(
        required: true,
        description: 'Update bookcase',
        content: new OA\JsonContent(
            required: ['description', 'name'],
            properties: [
                new OA\Property(property: 'bookcase_code', type: 'string', default: null, nullable: true),
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'description', type: 'string'),
                new OA\Property(property: 'status', type: 'string', default: null, nullable: true),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Update bookcase successfully',
        ),
        new OA\Response(
            response: 400,
            description: 'Validation error',
        ),
        new OA\Response(
            response: 404,
            description: 'Bookcase not found',
        ),
        new OA\Response(
            response: 500,
            description: 'Update bookcase failed',
        ),
    ],
)]

#[OA\Delete(
    path: '/api/v1/bookcases/delete/{id}',
    tags: ['Admin / Bookcase'],
    operationId: 'deleteBookcase',
    summary: 'Delete bookcase by id',
    description: 'Delete bookcase by id',
    security: [
        ['bearerAuth' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            description: 'Id của bookcase',
            schema: new OA\Schema(type: 'integer')
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Delete bookcase successfully',
        ),
        new OA\Response(
            response: 400,
            description: 'Validation error',
        ),
        new OA\Response(
            response: 404,
            description: 'Bookcase not found',
        ),
        new OA\Response(
            response: 500,
            description: 'Delete bookcase failed',
        ),
    ],
)]

class BookcaseController extends Controller
{
    public function index(Request $request)
    {
        // Validate request parameters
        $validator = Validator::make($request->all(), [
            'page' => 'integer|min:1',
            'pageSize' => 'integer|min:1',
            'search' => 'string',
            'status' => 'string|in:active,inactive,deleted',
        ], [
            'page.integer' => 'Trang phải là số nguyên.',
            'page.min' => 'Trang phải lớn hơn hoặc bằng 1.',
            'pageSize.integer' => 'Kích thước trang phải là số nguyên.',
            'pageSize.min' => 'Kích thước trang phải lớn hơn hoặc bằng 1.',
            'search.string' => 'Tìm kiếm phải là chuỗi.',
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
        $status = $request->input('status');

        // Tạo query ban đầu
        $query = Bookcase::query()->with(['shelves', 'books']);

        // Lấy tổng số mục trong DB trước khi áp dụng bộ lọc tìm kiếm

        // Áp dụng bộ lọc theo type
        $totalItems = $query->count();
        $query = $query->filter($status);

        // Áp dụng bộ lọc tìm kiếm nếu có tham số tìm kiếm
        $query = $query->search($search);

        // Thực hiện phân trang
        $bookcases = $query->orderBy('created_at', 'desc')->paginate($pageSize, ['*'], 'page', $page);

        return response()->json([
            "status" => true,
            "message" => "Get all bookcases successfully!",
            "data" => [
                "bookcases" => $bookcases->items(),
                "page" => $bookcases->currentPage(),
                "pageSize" => $bookcases->perPage(),
                "totalPages" => $bookcases->lastPage(),
                "totalResults" => $bookcases->total(),
                "total" => $totalItems
            ],
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bookcase_code' => 'nullable|string',
            'name' => 'required|string',
            'description' => 'nullable|string',
        ], [
            'name.required' => 'Tên không được để trống.',
            'name.string' => 'Tên phải là chuỗi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }

        $bookcase = Bookcase::create(array_merge(
            $validator->validated(),
        ));

        return response()->json([
            "status" => true,
            "message" => "Create bookcase successfully!",
            "data" => $bookcase
        ], 200);
    }

    public function show($id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|integer|min:1'
        ], [
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

        $bookcase = Bookcase::with(['shelves', 'shelves.books', 'books'])->find($id);

        if (!$bookcase) {
            return response()->json([
                "status" => false,
                "message" => "Bookcase not found!"
            ], 404);
        }

        return response()->json([
            "status" => true,
            "message" => "Get bookcase successfully!",
            "data" => $bookcase
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make(array_merge(['id' => $id], $request->all()), [
            'id' => 'required|integer|min:1|exists:bookcases,id',
            'bookcase_code' => 'nullable|string',
            'name' => 'required|string',
            'description' => 'nullable|string',
            'status' => 'nullable|string|in:active,inactive,deleted',
        ], [
            'id.required' => 'Trường id là bắt buộc.',
            'id.integer' => 'Id phải là một số nguyên.',
            'id.exists' => 'Mã kệ sách không tồn tại.',
            'id.min' => 'Id phải lớn hơn hoặc bằng 1.',
            'bookcase_code.string' => 'Mã kệ sách phải là chuỗi.',
            'description.string' => 'Mô tả phải là chuỗi.',
            'status.in' => 'Status phải là active, inactive hoặc deleted'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }

        $bookcase = Bookcase::find($id);

        if (!$bookcase) {
            return response()->json([
                "status" => false,
                "message" => "Bookcase not found!"
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
            $bookcase->update($validator->validated());

            return response()->json([
                "status" => true,
                "message" => "Update bookcase successfully!",
                "data" => $bookcase
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => "Update bookcase failed!"
            ], 500);
        }
    }

    public function destroy(Request $request)
    {
        $id = $request->route('id');

        $validator = Validator::make(['id' => $id], [
            'id' => 'required|integer|min:1|exists:bookcases,id'
        ], [
            'id.required' => 'Trường id là bắt buộc.',
            'id.integer' => 'Id phải là một số nguyên.',
            'id.min' => 'Id phải lớn hơn hoặc bằng 1.',
            'id.exists' => 'Mã kệ sách không tồn tại.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }

        $bookcase = Bookcase::find($id);

        if (!$bookcase) {
            return response()->json([
                "status" => false,
                "message" => "Bookcase not found!"
            ], 404);
        } else if ($bookcase->status == 'deleted') {
            return response()->json([
                "status" => false,
                "message" => "Bookcase is not exist!"
            ], 400);
        }

        try {
            $bookcase->delete();
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => "Delete bookcase failed!"
            ], 500);
        }

        return response()->json([
            "status" => true,
            "message" => "Delete bookcase successfully!",
        ], 200);
    }
}

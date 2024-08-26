<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/admin/suppliers',
    tags: ['Admin / Supplier'],
    operationId: 'getAllSuppliers',
    summary: 'Danh sách nhà cung cấp',
    description: 'Lấy danh sách nhà cung cấp',
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
            name: 'status',
            in: 'query',
            required: false,
            description: 'Trạng thái',
            schema: new OA\Schema(type: 'string', enum: ['active', 'inactive', 'deleted'])
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get all authors successfully',
        ),
        new OA\Response(
            response: 400,
            description: 'Dữ liệu không hợp lệ',
        ),
    ],
)]

#[OA\Get(
    path: '/api/v1/admin/suppliers/{id}',
    tags: ['Admin / Supplier'],
    operationId: 'getSupplier',
    summary: 'Chi tiết nhà cung cấp',
    description: 'Lấy thông tin chi tiết của một nhà cung cấp',
    security: [
        ['bearerAuth' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            description: 'ID của nhà cung cấp',
            schema: new OA\Schema(type: 'integer')
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get supplier successfully',
        ),
        new OA\Response(
            response: 400,
            description: 'Dữ liệu không hợp lệ',
        ),
    ],
)]


#[OA\Post(
    path: '/api/v1/admin/suppliers/create',
    tags: ['Admin / Supplier'],
    operationId: 'createSupplier',
    summary: 'Tạo nhà cung cấp',
    description: 'Tạo mới một nhà cung cấp',
    security: [
        ['bearerAuth' => []]
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['name', 'address', 'phone', 'email'],
            properties: [
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'address', type: 'string'),
                new OA\Property(property: 'phone', type: 'string'),
                new OA\Property(property: 'email', type: 'string'),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Create supplier successfully',
        ),
        new OA\Response(
            response: 400,
            description: 'Dữ liệu không hợp lệ',
        ),
    ],
)]

#[OA\Put(
    path: '/api/v1/admin/suppliers/update/{id}',
    tags: ['Admin / Supplier'],
    operationId: 'updateSupplier',
    summary: 'Cập nhật nhà cung cấp',
    description: 'Cập nhật thông tin của một nhà cung cấp',
    security: [
        ['bearerAuth' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            description: 'ID của nhà cung cấp',
            schema: new OA\Schema(type: 'integer')
        ),
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'address', type: 'string'),
                new OA\Property(property: 'phone', type: 'string'),
                new OA\Property(property: 'email', type: 'string'),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Update supplier successfully',
        ),
        new OA\Response(
            response: 400,
            description: 'Dữ liệu không hợp lệ',
        ),
    ],
)]

#[OA\Delete(
    path: '/api/v1/admin/suppliers/delete/{id}',
    tags: ['Admin / Supplier'],
    operationId: 'deleteSupplier',
    summary: 'Xóa nhà cung cấp',
    description: 'Xóa một nhà cung cấp',
    security: [
        ['bearerAuth' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            description: 'ID của nhà cung cấp',
            schema: new OA\Schema(type: 'integer')
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Delete supplier successfully',
        ),
        new OA\Response(
            response: 400,
            description: 'Dữ liệu không hợp lệ',
        ),
    ],
)]

class SupplierController extends Controller
{
    public function index(Request $request)
    {
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
            'status.in' => 'Status phải là active, inactive hoặc deleted'
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
        $status = $request->input('status');

        // Tạo câu query
        $query = Supplier::query();

        // Lấy tổng số mục trong DB trước khi áp dụng bộ lọc tìm kiếm

        // Áp dụng bộ lọc theo type
        $totalItems = $query->count();
        $query = $query->filter($status);

        // Áp dụng bộ lọc tìm kiếm nếu có tham số tìm kiếm
        $query = $query->search($search);

        // Thực hiện phân trang
        $suppliers = $query->orderBy('created_at', 'desc')->paginate($pageSize, ['*'], 'page', $page);

        return response()->json([
            "status" => true,
            "message" => "Get all suppliers successfully!",
            "data" => [
                "suppliers" => $suppliers->items(),
                "page" => $suppliers->currentPage(),
                "pageSize" => $suppliers->perPage(),
                "totalPages" => $suppliers->lastPage(),
                "totalResults" => $suppliers->total(),
                "total" => $totalItems
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'address' => 'required|string',
            'phone' => 'required',
            'email' => 'required|email',
        ],[
            'name.required' => 'Tên nhà cung cấp không được để trống',
            'name.string' => 'Tên nhà cung cấp phải là chuỗi',
            'address.required' => 'Địa chỉ nhà cung cấp không được để trống',
            'address.string' => 'Địa chỉ nhà cung cấp phải là chuỗi',
            'phone.required' => 'Số điện thoại nhà cung cấp không được để trống',
            'email.required' => 'Email nhà cung cấp không được để trống',
            'email.email' => 'Email nhà cung cấp không đúng định dạng',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Dữ liệu không hợp lệ",
                "errors" => $validator->errors()
            ], 400);
        }

        try {
            $supplier = Supplier::create($request->all());
            return response()->json([
                "status" => true,
                "message" => "Create supplier success",
                "data" => $supplier
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => "Create supplier fail",
                "errors" => $th->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|integer|min:1|exists:suppliers,id'
        ], [
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

        $supplier = Supplier::find($id);

        return response()->json([
            "status" => true,
            "message" => "Get supplier successfully!",
            "data" => $supplier
        ]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make(array_merge(['id' => $id], $request->all()), [
            'id' => 'required|integer|exists:suppliers,id|min:1',
            'name' => 'string',
            'address' => 'string',
            'phone' => 'string|regex:/^(0[35789])[0-9]{8}$/',
            'email' => 'email',
        ], [
            'id.required' => 'Trường id là bắt buộc.',
            'id.integer' => 'Id phải là một số nguyên.',
            'id.min' => 'Id phải lớn hơn hoặc bằng 1.',
            'id.exists' => 'Id không tồn tại.',
            'name.string' => 'Tên nhà cung cấp phải là chuỗi',
            'address.string' => 'Địa chỉ nhà cung cấp phải là chuỗi',
            'phone.string' => 'Số điện thoại nhà cung cấp phải là chuỗi',
            'phone.regex' => 'Số điện thoại nhà cung cấp không đúng định dạng', 
            'email.email' => 'Email nhà cung cấp không đúng định dạng',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Dữ liệu không hợp lệ",
                "errors" => $validator->errors()
            ], 400);
        }

        $supplier = Supplier::find($id);

        try {
            $supplier->update($request->all());
            return response()->json([
                "status" => true,
                "message" => "Update supplier success",
                "data" => $supplier
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => "Update supplier fail",
                "errors" => $th->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|integer|min:1|exists:suppliers,id'
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

        $supplier = Supplier::find($id);

        if (!$supplier) {
            return response()->json([
                "status" => false,
                "message" => "Supplier not found!"
            ], 404);
        } elseif ($supplier->status == 'deleted') {
            return response()->json([
                "status" => false,
                "message" => "Supplier not found!"
            ], 400);
        }

        try {
            $supplier->delete();

            if ($supplier->status == 'deleted') {
                return response()->json([
                    "status" => true,
                    "message" => "Nhà cung cấp đã thêm vào thùng rác! Bạn có thể khôi phục lại sau này!",
                ], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => "Delete supplier failed!"
            ], 500);
        }

        return response()->json([
            "status" => true,
            "message" => "Nhà cung cấp đã được xóa vĩnh viễn! Bạn không thể khôi phục lại sau này!",
        ], 200);
    }
}

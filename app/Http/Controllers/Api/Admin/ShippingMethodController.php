<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/admin/shipping-methods',
    tags: ['Admin / Shipping Method'],
    operationId: 'getAllShippingMethods',
    summary: 'Get all ShippingMethods (admin)',
    description: 'Get all ShippingMethods',
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
            description: 'Trạng thái của phương thức vận chuyển',
            schema: new OA\Schema(type: 'string', enum: ['active', 'inactive', 'deleted'])
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get all ShippingMethods successfully',
        ),
        new OA\Response(
            response: 400,
            description: 'Dữ liệu không hợp lệ',
        ),
    ],
)]

#[OA\Get(
    path: '/api/v1/admin/shipping-methods/{id}',
    tags: ['Admin / Shipping Method'],
    operationId: 'getShippingMethod',
    summary: 'Get ShippingMethod by id',
    description: 'Get ShippingMethod by id',
    security: [
        ['bearerAuth' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            description: 'Id của phương thức vận chuyển',
            schema: new OA\Schema(type: 'integer')
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get ShippingMethod successfully',
        ),
        new OA\Response(
            response: 400,
            description: 'Dữ liệu không hợp lệ',
        ),
        new OA\Response(
            response: 404,
            description: 'ShippingMethod not found',
        ),
    ],
)]

#[OA\Post(
    path: '/api/v1/admin/shipping-methods/create',
    tags: ['Admin / Shipping Method'],
    operationId: 'createShippingMethod',
    summary: 'Create ShippingMethod',
    description: 'Create ShippingMethod',
    security: [
        ['bearerAuth' => []]
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['Admin / Shipping Method'],
            properties: [
                new OA\Property(property: 'method', type: 'string'),
                new OA\Property(property: 'fee', type: 'number'),
                new OA\Property(property: 'logo', type: 'string'),
                new OA\Property(property: 'note', type: 'string'),
                new OA\Property(property: 'location', type: 'array', items: new OA\Items(type: 'string')),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Create ShippingMethod successfully',
        ),
        new OA\Response(
            response: 400,
            description: 'Dữ liệu không hợp lệ',
        ),
    ],
)]

#[OA\Put(
    path: '/api/v1/admin/shipping-methods/update/{id}',
    tags: ['Admin / Shipping Method'],
    operationId: 'updateShippingMethod',
    summary: 'Update ShippingMethod',
    description: 'Update ShippingMethod',
    security: [
        ['bearerAuth' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            description: 'Id của phương thức vận chuyển',
            schema: new OA\Schema(type: 'integer')
        ),
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['Admin / Shipping Method'],
            properties: [
                new OA\Property(property: 'method', type: 'string'),
                new OA\Property(property: 'fee', type: 'number'),
                new OA\Property(property: 'logo', type: 'string'),
                new OA\Property(property: 'note', type: 'string'),
                new OA\Property(property: 'location', type: 'array', items: new OA\Items(type: 'string')),
                new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive']),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Update ShippingMethod successfully',
        ),
        new OA\Response(
            response: 400,
            description: 'Dữ liệu không hợp lệ',
        ),
        new OA\Response(
            response: 404,
            description: 'ShippingMethod not found',
        ),
    ],
)]

#[OA\Delete(
    path: '/api/v1/admin/shipping-methods/delete/{id}',
    tags: ['Admin / Shipping Method'],
    operationId: 'deleteShippingMethod',
    summary: 'Delete ShippingMethod',
    description: 'Delete ShippingMethod',
    security: [
        ['bearerAuth' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            description: 'Id của phương thức vận chuyển',
            schema: new OA\Schema(type: 'integer')
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Delete ShippingMethod successfully',
        ),
        new OA\Response(
            response: 400,
            description: 'Dữ liệu không hợp lệ',
        ),
        new OA\Response(
            response: 404,
            description: 'ShippingMethod not found',
        ),
    ],
)]

class ShippingMethodController extends Controller
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
        ], [
            'page.integer' => 'Trang phải là số nguyên.',
            'page.min' => 'Trang phải lớn hơn hoặc bằng 1.',
            'pageSize.integer' => 'Kích thước trang phải là số nguyên.',
            'pageSize.min' => 'Kích thước trang phải lớn hơn hoặc bằng 1.',
            'ShippingMethod.string' => 'Tìm kiếm phải là một chuỗi.',
            'status.in' => 'Status phải là active, inactive và deleted'
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

        // Tạo query ban đầu
        $query = ShippingMethod::query();

        // Lấy tổng số mục trong DB trước khi áp dụng bộ lọc tìm kiếm

        // Áp dụng bộ lọc theo type
        $totalItems = $query->count();
        $query = $query->filter($status, true);

        // Áp dụng bộ lọc tìm kiếm nếu có tham số tìm kiếm
        $query = $query->search($search);

        // Thực hiện phân trang
        $ShippingMethods = $query->orderBy('created_at', 'desc')->paginate($pageSize, ['*'], 'page', $page);

        return response()->json([
            "status" => true,
            "message" => "Get all shippingMethods successfully!",
            "data" => [
                "shippingMethods" => $ShippingMethods->items(),
                "page" => $ShippingMethods->currentPage(),
                "pageSize" => $ShippingMethods->perPage(),
                "totalPages" => $ShippingMethods->lastPage(),
                "totalResults" => $ShippingMethods->total(),
                "total" => $totalItems
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'method' => 'required|string',
            'fee' => 'required|numeric|min:0',
            'logo' => 'nullable|string',
            'note' => 'nullable|string',
            'location' => 'required|array',
        ], [
            'method.required' => 'Tên phương thức vận chuyển không được để trống.',
            'method.string' => 'Tên phương thức vận chuyển phải là một chuỗi.',
            'fee.required' => 'Phí vận chuyển không được để trống.',
            'fee.numeric' => 'Phí vận chuyển phải là một số.',
            'fee.min' => 'Phí vận chuyển phải lớn hơn hoặc bằng 0.',
            'logo.string' => 'Logo phải là một chuỗi.',
            'note.string' => 'Ghi chú phải là một chuỗi.',
            'location.required' => 'Vị trí không được để trống.',
            'location.array' => 'Vị trí phải là một mảng.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Dữ liệu không hợp lệ",
                "errors" => $validator->errors()
            ], 400);
        }

        try {
            $ShippingMethod = ShippingMethod::create($validator->validated());


            return response()->json([
                "status" => true,
                "message" => "Create ShippingMethod successfully!",
                "data" => $ShippingMethod
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => "Create ShippingMethod failed!"
            ], 500);
        }
    }

    public function show($id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|integer|min:1|exists:shipping_methods,id'
        ], [
            'id.required' => 'Trường id là bắt buộc.',
            'id.integer' => 'Id phải là một số nguyên.',
            'id.min' => 'Id phải lớn hơn hoặc bằng 1.',
            'id.exists' => 'Phương thức vận chuyển không tồn tại.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Dữ liệu không hợp lệ",
                "errors" => $validator->errors()
            ], 400);
        }

        $shippingMethod = ShippingMethod::find($id);

        if (!$shippingMethod) {
            return response()->json([
                "status" => false,
                "message" => "ShippingMethod not found!"
            ], 404);
        }

        return response()->json([
            "status" => true,
            "message" => "Get ShippingMethod successfully!",
            "data" => $shippingMethod
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make(array_merge(['id' => $id], $request->all()), [
            'id' => 'required|integer|exists:shipping_methods,id|min:1',
            'method' => 'string',
            'fee' => 'numeric|min:0',
            'logo' => 'nullable|string',
            'note' => 'nullable|string',
            'location' => 'array',
            'status' => 'nullable|in:active,inactive',
        ], [
            'id.required' => 'Trường id là bắt buộc.',
            'id.integer' => 'Id phải là một số nguyên.',
            'id.min' => 'Id phải lớn hơn hoặc bằng 1.',
            'id.exists' => 'Phương thức vận chuyển không tồn tại.',
            'method.string' => 'Tên phương thức vận chuyển phải là một chuỗi.',
            'fee.numeric' => 'Phí vận chuyển phải là một số.',
            'fee.min' => 'Phí vận chuyển phải lớn hơn hoặc bằng 0.',
            'logo.string' => 'Logo phải là một chuỗi.',
            'note.string' => 'Ghi chú phải là một chuỗi.',
            'location.array' => 'Vị trí phải là một mảng.',
            'status.in' => 'Status phải là active, inactive'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Dữ liệu không hợp lệ",
                "errors" => $validator->errors()
            ], 400);
        }

        $ShippingMethod = ShippingMethod::find($id);

        try {
            $ShippingMethod->update($validator->validated());

            return response()->json([
                "status" => true,
                "message" => "Update ShippingMethod successfully!",
                "data" => $ShippingMethod
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => "Update ShippingMethod failed!"
            ], 500);
        }
    }

    public function destroy($id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|integer|min:1|exists:shipping_methods,id'
        ], [
            'id.required' => 'Trường id là bắt buộc.',
            'id.integer' => 'Id phải là một số nguyên.',
            'id.min' => 'Id phải lớn hơn hoặc bằng 1.',
            'id.exists' => 'Phương thức vận chuyển không tồn tại.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Dữ liệu không hợp lệ",
                "errors" => $validator->errors()
            ], 400);
        }

        $ShippingMethod = ShippingMethod::find($id);

        try {
            $ShippingMethod->delete();

            if ($ShippingMethod->status == 'deleted') {
                return response()->json([
                    "status" => true,
                    "message" => "Phương thức vận chuyển đã thêm vào thùng rác! Bạn có thể khôi phục lại sau này!",
                ], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => "Delete ShippingMethod failed!"
            ], 500);
        }

        return response()->json([
            "status" => true,
            "message" => "Phương thức vận chuyển đã được xóa vĩnh viễn! Bạn không thể khôi phục lại sau này!",
        ], 200);
    }
}

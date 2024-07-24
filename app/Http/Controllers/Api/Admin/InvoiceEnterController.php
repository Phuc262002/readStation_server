<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookDetail;
use App\Models\InvoiceEnter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/admin/invoice-enters',
    tags: ['Admin / Invoice Enter'],
    summary: 'Get all invoice enters',
    description: 'Lấy danh sách hóa đơn nhập',
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
            schema: new OA\Schema(type: 'string', enum: ['draft','active', 'canceled'])
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get all suppliers successfully',
        ),
        new OA\Response(
            response: 400,
            description: 'Dữ liệu không hợp lệ',
        ),
    ],
)]

#[OA\Get(
    path: '/api/v1/admin/invoice-enters/{id}',
    tags: ['Admin / Invoice Enter'],
    summary: 'Get invoice enter by ID',
    description: 'Lấy thông tin hóa đơn nhập theo ID',
    security: [
        ['bearerAuth' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            description: 'ID hóa đơn nhập',
            schema: new OA\Schema(type: 'integer')
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get invoice enter successfully',
        ),
        new OA\Response(
            response: 400,
            description: 'Dữ liệu không hợp lệ',
        ),
    ],
)]

#[OA\Post(
    path: '/api/v1/admin/invoice-enters/create',
    tags: ['Admin / Invoice Enter'],
    summary: 'Create new invoice enter',
    description: 'Tạo mới một nhà cung cấp',
    security: [
        ['bearerAuth' => []]
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['invoice_code', 'invoice_name', 'total', 'invoice_description', 'supplier_id', 'invoice_date', 'status', 'invoice_enter_detail'],
            properties: [
                new OA\Property(property: 'invoice_code', type: 'string', description: 'Mã hóa đơn', nullable: true, default: null),
                new OA\Property(property: 'invoice_name', type: 'string', description: 'Tên hóa đơn'),
                new OA\Property(property: 'total', type: 'string', description: 'Tổng tiền'),
                new OA\Property(property: 'invoice_description', type: 'string', description: 'Mô tả hóa đơn'),
                new OA\Property(property: 'supplier_id', type: 'integer', description: 'ID nhà cung cấp'),
                new OA\Property(property: 'invoice_date', type: 'string', format: 'date', description: 'Ngày hóa đơn'),
                new OA\Property(property: 'status', type: 'string', enum: ['draft', 'active'], description: 'Trạng thái'),
                new OA\Property(
                    property: 'invoice_enter_detail', 
                    type: 'array', 
                    description: 'Chi tiết hóa đơn', 
                    items: new OA\Items(
                        type: 'object',
                        required: ['book_detail_id', 'book_price', 'book_quantity'],
                        properties: [
                            new OA\Property(property: 'book_detail_id', type: 'integer', description: 'ID chi tiết sách'),
                            new OA\Property(property: 'book_price', type: 'string', description: 'Giá sách'),
                            new OA\Property(property: 'book_quantity', type: 'integer', description: 'Số lượng sách'),
                        ]
                    )
                ),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Create invoice enter successfully',
        ),
        new OA\Response(
            response: 400,
            description: 'Dữ liệu không hợp lệ',
        ),
    ],
)]

#[OA\Put(
    path: '/api/v1/admin/invoice-enters/update/{id}',
    tags: ['Admin / Invoice Enter'],
    summary: 'Update invoice enter by ID',
    description: 'Cập nhật thông tin hóa đơn nhập theo ID',
    security: [
        ['bearerAuth' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            description: 'ID hóa đơn nhập',
            schema: new OA\Schema(type: 'integer')
        ),
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['invoice_name', 'invoice_description', 'status'],
            properties: [
                new OA\Property(property: 'invoice_name', type: 'string', description: 'Tên hóa đơn'),
                new OA\Property(property: 'invoice_description', type: 'string', description: 'Mô tả hóa đơn'),
                new OA\Property(property: 'status', type: 'string', enum: ['draft', 'active', 'canceled'], description: 'Trạng thái'),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Update invoice enter successfully',
        ),
        new OA\Response(
            response: 400,
            description: 'Dữ liệu không hợp lệ',
        ),
    ],
)]

class InvoiceEnterController extends Controller
{
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'page' => 'integer|min:1',
            'pageSize' => 'integer|min:1',
            'search' => 'string',
            'status' => 'string|in:draft,active,canceled',
            'supplier_id' => 'integer|min:1'
        ], [
            'page.integer' => 'Trang phải là số nguyên.',
            'page.min' => 'Trang phải lớn hơn hoặc bằng 1.',
            'pageSize.integer' => 'Kích thước trang phải là số nguyên.',
            'pageSize.min' => 'Kích thước trang phải lớn hơn hoặc bằng 1.',
            'status.in' => 'Status phải là draft, active hoặc canceled.',
            'supplier_id.integer' => 'ID nhà cung cấp phải là số nguyên.',
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
        $supplier_id = $request->input('supplier_id');

        // Tạo câu query
        $query = InvoiceEnter::query()->with('supplier', 'user', 'invoiceEnterDetails');

        // Lấy tổng số mục trong DB trước khi áp dụng bộ lọc tìm kiếm

        // Áp dụng bộ lọc theo type
        $totalItems = $query->count();
        $query = $query->filter($status, $supplier_id);

        // Áp dụng bộ lọc tìm kiếm nếu có tham số tìm kiếm
        $query = $query->search($search);

        // Thực hiện phân trang
        $envoiceEnters = $query->orderBy('created_at', 'desc')->paginate($pageSize, ['*'], 'page', $page);

        return response()->json([
            "status" => true,
            "message" => "Get all envoiceEnters successfully!",
            "data" => [
                "envoiceEnters" => $envoiceEnters->items(),
                "page" => $envoiceEnters->currentPage(),
                "pageSize" => $envoiceEnters->perPage(),
                "totalPages" => $envoiceEnters->lastPage(),
                "totalResults" => $envoiceEnters->total(),
                "total" => $totalItems
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'invoice_code' => "nullable|string",
            'invoice_name' => "required",
            'total' => "required",
            'invoice_description' => "required|string",
            'supplier_id' => "required",
            'invoice_date' => "required|date",
            'status' => "required|string|in:draft,active",
            'invoice_enter_detail' => "required|array",
            'invoice_enter_detail.*.book_detail_id' => "required",
            'invoice_enter_detail.*.book_price' => "required",
            'invoice_enter_detail.*.book_quantity' => "required",

        ], [
            'invoice_name.required' => 'Tên hóa đơn không được để trống.',
            'total.required' => 'Tổng tiền không được để trống.',
            'invoice_description.required' => 'Mô tả hóa đơn không được để trống.',
            'supplier_id.required' => 'ID nhà cung cấp không được để trống.',
            'status.required' => 'Trạng thái không được để trống.',
            'status.string' => 'Trạng thái phải là chuỗi.',
            'status.in' => 'Trạng thái phải là draft hoặc active.',
            'invoice_date.required' => 'Ngày hóa đơn không được để trống.',
            'invoice_enter_detail.required' => 'Chi tiết hóa đơn không được để trống.',
            'status.in' => 'Trạng thái phải là draft hoặc active.',
            'invoice_enter_detail.required' => 'Chi tiết hóa đơn không được để trống.',
            'invoice_enter_detail.array' => 'Chi tiết hóa đơn phải là mảng.',
            'invoice_enter_detail.*.book_detail_id.required' => 'ID chi tiết sách không được để trống.',
            'invoice_enter_detail.*.book_price.required' => 'Giá sách không được để trống.',
            'invoice_enter_detail.*.book_quantity.required' => 'Số lượng sách không được để trống.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Dữ liệu không hợp lệ",
                "errors" => $validator->errors()
            ], 400);
        }

        try {
            $validatedData = $validator->validated();

            $invoiceEnter = InvoiceEnter::create(array_merge($validatedData, [
                'user_id' => auth()->user()->id
            ]));

            $invoiceEnterDetails = $validatedData['invoice_enter_detail'];
            foreach ($invoiceEnterDetails as $detail) {
                $detail['invoice_enter_id'] = $invoiceEnter->id;
            }

            $invoiceEnter->invoiceEnterDetails()->createMany($invoiceEnterDetails);

            if ($invoiceEnter->status == 'active') {
                foreach ($invoiceEnterDetails as $detail) {
                    $bookDetail = BookDetail::find($detail['book_detail_id']);
                    $bookDetail->update([
                        'stock' => $bookDetail->stock + $detail['book_quantity']
                    ]);
                }
            }

            return response()->json([
                "status" => true,
                "message" => "Create invoice enter successfully!",
                "data" => InvoiceEnter::with('invoiceEnterDetails')->find($invoiceEnter->id)
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => "Create invoice enter failed!",
                "errors" => $th->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|integer|min:1|exists:invoice_enters,id'
        ], [
            'id.required' => 'ID không được để trống.',
            'id.integer' => 'ID phải là số nguyên.',
            'id.min' => 'ID phải lớn hơn hoặc bằng 1.',
            'id.exists' => 'ID không tồn tại.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Dữ liệu không hợp lệ",
                "errors" => $validator->errors()
            ], 400);
        }

        $invoiceEnter = InvoiceEnter::with('supplier', 'user', 'invoiceEnterDetails', 'invoiceEnterDetails.bookDetail', 'invoiceEnterDetails.bookDetail.book')->find($id);

        if (!$invoiceEnter) {
            return response()->json([
                "status" => false,
                "message" => "Invoice enter not found!"
            ], 404);
        }

        return response()->json([
            "status" => true,
            "message" => "Get invoice enter successfully!",
            "data" => $invoiceEnter
        ]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make(array_merge(['id' => $id], $request->all()), [
            'id' => 'required|integer|min:1|exists:invoice_enters,id',
            'invoice_name' => "nullable|string",
            'invoice_description' => "nullable|string",
            'status' => "nullable|string|in:draft,active,canceled",
        ], [
            'id.required' => 'ID không được để trống.',
            'id.integer' => 'ID phải là số nguyên.',
            'id.min' => 'ID phải lớn hơn hoặc bằng 1.',
            'id.exists' => 'ID không tồn tại.',
            'invoice_name.required' => 'Tên hóa đơn không được để trống.',
            'invoice_description.required' => 'Mô tả hóa đơn không được để trống.',
            'status.required' => 'Trạng thái không được để trống.',
            'status.string' => 'Trạng thái phải là chuỗi.',
            'status.in' => 'Trạng thái phải là draft, active hoặc canceled.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Dữ liệu không hợp lệ",
                "errors" => $validator->errors()
            ], 400);
        }

        try {
            $validatedData = $validator->validated();
            $invoiceEnter = InvoiceEnter::find($id);

            if ($invoiceEnter->status == 'canceled') {
                return response()->json([
                    "status" => false,
                    "message" => "Can not update canceled invoice enter!"
                ], 400);
            }

            $invoiceEnter->update($validatedData);

            if ($invoiceEnter->status == 'active') {
                foreach ($invoiceEnter->invoiceEnterDetails as $detail) {
                    $bookDetail = BookDetail::find($detail->book_detail_id);
                    $bookDetail->update([
                        'stock' => $bookDetail->stock - $detail->book_quantity
                    ]);
                }
            }

            if ($invoiceEnter->status == 'active' && $validatedData['status'] != 'active') {
                return response()->json([
                    "status" => false,
                    "message" => "Can not change status from active to draft!"
                ], 400);
            }

            return response()->json([
                "status" => true,
                "message" => "Update invoice enter successfully!",
                "data" => InvoiceEnter::with('invoiceEnterDetails')->find($invoiceEnter->id)
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => "Update invoice enter failed!",
                "errors" => $th->getMessage()
            ], 500);
        }
    }
}

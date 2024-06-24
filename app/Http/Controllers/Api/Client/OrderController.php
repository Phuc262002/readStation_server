<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/account/order/get-all',
    operationId: 'getAllOrder',
    tags: ['Account'],
    summary: 'Get all order',
    description: 'Get all order',
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
            description: 'Trạng thái của order',
            schema: new OA\Schema(type: 'string', enum: ['pending', 'hiring', 'completed', 'canceled', 'out_of_date'])
        ),
        new OA\Parameter(
            name: 'search',
            in: 'query',
            required: false,
            description: 'Mã đơn hàng cần tìm kiếm',
            schema: new OA\Schema(type: 'string')
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get all order successfully'
        ),
        new OA\Response(
            response: 400,
            description: 'Validation error',
        ),
    ]
)]

#[OA\Post(
    path: '/api/v1/account/order/create',
    operationId: 'createOrder',
    tags: ['Account'],
    summary: 'Create order',
    description: 'Create order',
    security: [
        ['bearerAuth' => []]
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['payment_method', 'payment_shipping', 'phone', 'address', 'deposit_fee', 'total_fee', 'order_details'],
            properties: [
                new OA\Property(property: 'payment_method', type: 'string', description: 'Phương thức thanh toán', enum: ['wallet', 'cash']),
                new OA\Property(property: 'payment_shipping', type: 'string', description: 'Phương thức vận chuyển', enum: ['library', 'shipper']),
                new OA\Property(property: 'phone', type: 'string', description: 'Số điện thoại'),
                new OA\Property(property: 'address', type: 'string', description: 'Địa chỉ'),
                new OA\Property(property: 'user_note', type: 'string', description: 'Ghi chú'),
                new OA\Property(property: 'deposit_fee', type: 'number', description: 'Tiền đặt cọc'),
                new OA\Property(property: 'expired_date', type: 'string', description: 'Ngày hết hạn'),
                new OA\Property(property: 'total_fee', type: 'number', description: 'Tổng tiền'),
                new OA\Property(
                    property: 'order_details',
                    type: 'array',
                    description: 'Chi tiết đơn hàng',
                    items: new OA\Items(
                        type: 'object',
                        required: ['book_details_id', 'service_fee', 'deposit'],
                        properties: [
                            new OA\Property(property: 'book_details_id', type: 'integer', description: 'Id chi tiết sách'),
                            new OA\Property(property: 'service_fee', type: 'integer', description: 'Phí dịch vụ'),
                            new OA\Property(property: 'deposit', type: 'number', description: 'Tiền đặt cọc')
                        ]
                    )
                ),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Create order successfully'
        ),
        new OA\Response(
            response: 400,
            description: 'Validation error',
        ),
        new OA\Response(
            response: 500,
            description: 'Create order failed',
        ),
    ]
)]

#[OA\Get(
    path: '/api/v1/account/order/get-one/{id}',
    operationId: 'getOrder',
    tags: ['Account'],
    summary: 'Get order',
    description: 'Get order',
    security: [
        ['bearerAuth' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            description: 'Id của order',
            schema: new OA\Schema(type: 'integer')
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get order successfully'
        ),
        new OA\Response(
            response: 400,
            description: 'Validation error',
        ),
    ]
)]

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'page' => 'integer|min:1',
            'pageSize' => 'integer|min:1',
            'status' => 'string|in:pending,hiring,completed,canceled,out_of_date',
            'search' => 'string',
        ], [
            'page.integer' => 'Trường trang phải là kiểu số',
            'page.min' => 'Trường trang không được nhỏ hơn 1',
            'pageSize.integer' => 'Trường pageSize phải là kiểu số',
            'pageSize.min' => 'Trường pageSize không được nhỏ hơn 1',
            'status.string' => 'Trường status phải là kiểu chuỗi',
            'status.in' => 'Trường status phải là pending, hiring, completed, canceled hoặc out_of_date',
            'search.string' => 'Trường search phải là kiểu chuỗi',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }

        $page = $request->input('page', 1);
        $pageSize = $request->input('pageSize', 10);
        $search = $request->input('search');
        $status = $request->input('status');


        $query = Order::query()->with('orderDetails')->where('user_id', auth()->id());
        $totalItems = $query->count();
        if ($status) {
            $query->where('status', $status);
        } else {
            $query->whereIn('status', ['pending', 'approved', 'wating_take_book', 'hiring', 'increasing', 'wating_return', 'completed', 'canceled', 'out_of_date']);
        }

        if ($search) {
            $query->where('order_code', 'like', '%' . $search . '%');
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate($pageSize, ['*'], 'page', $page);

        return response()->json([
            'status' => true,
            'message' => 'Get all order successfully',
            "data" => [
                "orders" => $orders->items(),
                "page" => $orders->currentPage(),
                "pageSize" => $orders->perPage(),
                "totalPages" => $orders->lastPage(),
                "totalResults" => $orders->total(),
                "total" => $totalItems
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|string|in:wallet,cash',
            'payment_shipping' => 'required|string|in:library,shipper',
            'phone' => 'required|string',
            'address' => 'required|string',
            'user_note' => 'string',
            'deposit_fee' => 'required|numeric|min:0',
            'expired_date' => 'date',
            'total_fee' => 'required|numeric|min:0',
            'order_details' => 'required|array',
            'order_details.*.book_details_id' => 'required|integer',
            'order_details.*.service_fee' => 'required|integer|min:1',
            'order_details.*.deposit' => 'required|numeric|min:0',
        ], [
            'payment_method.required' => 'Trường phương thức thanh toán là bắt buộc',
            'payment_method.string' => 'Trường phương thức thanh toán phải là kiểu chuỗi',
            'payment_method.in' => 'Trường phương thức thanh toán phải là wallet hoặc cash',
            'payment_shipping.required' => 'Trường phương thức vận chuyển là bắt buộc',
            'payment_shipping.string' => 'Trường phương thức vận chuyển phải là kiểu chuỗi',
            'payment_shipping.in' => 'Trường phương thức vận chuyển phải là library hoặc shipper',
            'phone.required' => 'Trường số điện thoại là bắt buộc',
            'phone.string' => 'Trường số điện thoại phải là kiểu chuỗi',
            'address.required' => 'Trường địa chỉ là bắt buộc',
            'address.string' => 'Trường địa chỉ phải là kiểu chuỗi',
            'user_note.string' => 'Trường ghi chú phải là kiểu chuỗi',
            'deposit_fee.required' => 'Trường tiền đặt cọc là bắt buộc',
            'deposit_fee.numeric' => 'Trường tiền đặt cọc phải là kiểu số',
            'deposit_fee.min' => 'Trường tiền đặt cọc không được nhỏ hơn 0',
            'expired_date.date' => 'Trường ngày hết hạn phải là kiểu ngày',
            'fine_fee.required' => 'Trường tiền phạt là bắt buộc',
            'fine_fee.numeric' => 'Trường tiền phạt phải là kiểu số',
            'fine_fee.min' => 'Trường tiền phạt không được nhỏ hơn 0',
            'total_fee.required' => 'Trường tổng tiền là bắt buộc',
            'total_fee.numeric' => 'Trường tổng tiền phải là kiểu số',
            'total_fee.min' => 'Trường tổng tiền không được nhỏ hơn 0',
            'order_details.required' => 'Trường chi tiết đơn hàng là bắt buộc',
            'order_details.array' => 'Trường chi tiết đơn hàng phải là kiểu mảng',
            'order_details.*.book_details_id.required' => 'Trường id chi tiết sách là bắt buộc',
            'order_details.*.book_details_id.integer' => 'Trường id chi tiết sách phải là kiểu số',
            'order_details.*.service_fee.required' => 'Trường phí dịch vụ là bắt buộc',
            'order_details.*.service_fee.integer' => 'Trường phí dịch vụ phải là kiểu số',
            'order_details.*.service_fee.min' => 'Trường phí dịch vụ không được nhỏ hơn 1',
            'order_details.*.deposit.required' => 'Trường tiền đặt cọc là bắt buộc',
            'order_details.*.deposit.numeric' => 'Trường tiền đặt cọc phải là kiểu số',
            'order_details.*.deposit.min' => 'Trường tiền đặt cọc không được nhỏ hơn 0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }

        try {
            $validatedData = $validator->validated();

            if ($request->has('expired_date')) {
                if (strtotime($validatedData['expired_date']) < strtotime(date('Y-m-d'))) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Create order failed',
                        'errors' => 'Ngày hết hạn không hợp lệ'
                    ]);
                }

                if (strtotime($validatedData['expired_date']) > strtotime('+4 days')) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Create order failed',
                        'errors' => 'Ngày hết hạn không được quá 4 ngày'
                    ]);
                }
            }

            if ($validatedData['payment_method'] === 'wallet') {
                $wallet = auth()->user()->wallet;

                if ($wallet) {
                    if ($wallet->balance < $validatedData['total_fee']) {
                        return response()->json([
                            'status' => false,
                            'message' => 'Create order failed',
                            'errors' => 'Số dư trong ví không đủ'
                        ]);
                    }
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Create order failed',
                        'errors' => 'Bạn chưa kích hoạt ví'
                    ]);
                }
            } else {
                $order = Order::create(array_merge($validatedData, [
                    'user_id' => auth()->user()->id,
                    'expired_date' => $request->has('expired_date') ? date('Y-m-d', strtotime($validatedData['expired_date'])) : date('Y-m-d', strtotime('+4 days')),
                ]));

                $orderDetails = $validatedData['order_details'];
                foreach ($orderDetails as $key => $detail) {
                    $orderDetails[$key]['expired_date'] = $request->has('expired_date') ? date('Y-m-d', strtotime($validatedData['expired_date'])) : date('Y-m-d', strtotime('+4 days'));
                }

                $order->orderDetails()->createMany($orderDetails);
            }

            return response()->json([
                'status' => true,
                'message' => 'Create order successfully',
                'data' => $order
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Create order failed',
                'errors' => $th->getMessage()
            ]);
        }
    }

    public function show($id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|exists:orders,id',
        ], [
            'id.required' => 'Trường id là bắt buộc',
            'id.exists' => 'Id không tồn tại',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }

        $order = Order::with(
            'orderDetails',
            'orderDetails.bookDetail',
            'orderDetails.bookDetail.book',
            'orderDetails.bookDetail.book.author',
            'orderDetails.bookDetail.publishingCompany',
            'orderDetails.bookDetail.book.category',
        )->find($id);

        return response()->json([
            'status' => true,
            'message' => 'Get order successfully',
            'data' => $order
        ]);
    }

    public function update(Request $request, Order $order)
    {
        //
    }
}

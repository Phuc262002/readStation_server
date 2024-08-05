<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Api\VNPay\VnpayCreatePayment;
use App\Http\Controllers\Controller;
use App\Models\BookDetail;
use App\Models\Extensions;
use App\Models\LoanOrderDetails;
use App\Models\LoanOrders;
use App\Models\ReturnHistory;
use App\Models\ShippingMethod;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;
use PayOS\PayOS;

#[OA\Get(
    path: '/api/v1/account/orders',
    operationId: 'getAllOrder',
    tags: ['Account / Orders'],
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
            schema: new OA\Schema(type: 'string', enum: ['wating_payment', 'pending', 'approved', 'ready_for_pickup', 'preparing_shipment', 'in_transit', 'active', 'extended', 'returning', 'completed', 'canceled', 'overdue'])
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
            description: 'Dữ liệu không hợp lệ',
        ),
    ]
)]

#[OA\Post(
    path: '/api/v1/account/orders/payment/{id}',
    operationId: 'paymentOrder',
    tags: ['Account / Orders'],
    summary: 'Payment order',
    description: 'Payment order',
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
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['payment_portal'],
            properties: [
                new OA\Property(property: 'payment_portal', type: 'string', description: 'Cổng thanh toán', enum: ['payos', 'vnpay']),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Payment order successfully'
        ),
        new OA\Response(
            response: 400,
            description: 'Dữ liệu không hợp lệ',
        ),
    ]
)]

#[OA\Post(
    path: '/api/v1/account/orders/create',
    operationId: 'createOrder',
    tags: ['Account / Orders'],
    summary: 'Create order',
    description: 'Create order',
    security: [
        ['bearerAuth' => []]
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['payment_method', 'payment_portal', 'delivery_method', 'total_deposit_fee', 'total_service_fee', 'total_shipping_fee', 'total_all_fee', 'order_details'],
            properties: [
                new OA\Property(property: 'payment_method', type: 'string', description: 'Phương thức thanh toán', enum: ['online', 'cash']),
                new OA\Property(property: 'payment_portal', type: 'string', description: 'Cổng thanh toán', enum: ['payos', 'vnpay']),
                new OA\Property(property: 'delivery_method', type: 'string', description: 'Phương thức vận chuyển', enum: ['library', 'shipper']),
                new OA\Property(property: 'user_note', type: 'string', description: 'Ghi chú'),
                new OA\Property(
                    property: 'delivery_info',
                    type: 'object',
                    description: 'Thông tin giao hàng',
                    properties: [
                        new OA\Property(property: 'fullname', type: 'string', description: 'Họ tên'),
                        new OA\Property(property: 'phone', type: 'string', description: 'Số điện thoại'),
                        new OA\Property(property: 'address', type: 'string', description: 'Địa chỉ'),
                    ]
                ),
                new OA\Property(property: 'shipping_method_id', type: 'integer', description: 'Id phương thức vận chuyển'),
                new OA\Property(property: 'total_shipping_fee', type: 'number', description: 'Phí vận chuyển'),
                new OA\Property(property: 'total_service_fee', type: 'integer', description: 'Tổng phí dịch vụ'),
                new OA\Property(property: 'total_deposit_fee', type: 'number', description: 'Tiền đặt cọc'),
                new OA\Property(property: 'total_all_fee', type: 'number', description: 'Tổng tiền'),
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
                            new OA\Property(property: 'deposit_fee', type: 'number', description: 'Tiền đặt cọc')
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
            description: 'Dữ liệu không hợp lệ',
        ),
        new OA\Response(
            response: 500,
            description: 'Create order failed',
        ),
    ]
)]

#[OA\Get(
    path: '/api/v1/account/orders/{id}',
    operationId: 'getOrder',
    tags: ['Account / Orders'],
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
            description: 'Id hoặc mã đơn hàng',
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
            description: 'Dữ liệu không hợp lệ',
        ),
    ]
)]

#[OA\Put(
    path: '/api/v1/account/orders/cancel/{id}',
    operationId: 'cancelOrder',
    tags: ['Account / Orders'],
    summary: 'Cancel order',
    description: 'Cancel order',
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
            description: 'Cancel order successfully'
        ),
        new OA\Response(
            response: 400,
            description: 'Dữ liệu không hợp lệ',
        ),
        new OA\Response(
            response: 500,
            description: 'Cancel order failed',
        ),
    ]
)]

#[OA\Post(
    path: '/api/v1/account/orders/extension-all/{id}',
    operationId: 'extensionAllOrder',
    tags: ['Account / Orders'],
    summary: 'Extension all order',
    description: 'Extension all order',
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
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['extended_method'],
            properties: [
                new OA\Property(property: 'extended_method', type: 'string', description: 'Phương thức gia hạn', default: 'enum => pickup | library'),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Extension all order successfully'
        ),
        new OA\Response(
            response: 400,
            description: 'Dữ liệu không hợp lệ',
        ),
        new OA\Response(
            response: 500,
            description: 'Extension all order failed',
        ),
    ]
)]

#[OA\Post(
    path: '/api/v1/account/orders/extension-each-book/{id}',
    operationId: 'extensionEachBook',
    tags: ['Account / Orders'],
    summary: 'Extension each book',
    description: 'Extension each book',
    security: [
        ['bearerAuth' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            description: 'Id của order chi tiết',
            schema: new OA\Schema(type: 'integer')
        ),
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['extended_method'],
            properties: [
                new OA\Property(property: 'extended_method', type: 'string', description: 'Phương thức gia hạn', default: 'enum => pickup | library'),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Extension all order successfully'
        ),
        new OA\Response(
            response: 400,
            description: 'Dữ liệu không hợp lệ',
        ),
        new OA\Response(
            response: 500,
            description: 'Extension all order failed',
        ),
    ]
)]

#[OA\Post(
    path: '/api/v1/account/orders/return-all/{id}',
    operationId: 'returnAllOrder',
    tags: ['Account / Orders'],
    summary: 'Return all order',
    description: 'Return all order',
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
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['return_method', 'pickup_info'],
            properties: [
                new OA\Property(property: 'return_method', type: 'string', description: 'Phương thức trả sách', default: 'enum => pickup | library'),
                new OA\Property(
                    property: 'pickup_info',
                    type: 'object',
                    description: 'Thông tin lấy sách',
                    properties: [
                        new OA\Property(property: 'fullname', type: 'string', description: 'Họ tên'),
                        new OA\Property(property: 'phone', type: 'string', description: 'Số điện thoại'),
                        new OA\Property(property: 'address', type: 'string', description: 'Địa chỉ'),
                    ]
                ),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Return all order successfully'
        ),
        new OA\Response(
            response: 400,
            description: 'Dữ liệu không hợp lệ',
        ),
        new OA\Response(
            response: 500,
            description: 'Return all order failed',
        ),
    ]
)]

#[OA\Post(
    path: '/api/v1/account/orders/return-each-book/{id}',
    operationId: 'returnEachBook',
    tags: ['Account / Orders'],
    summary: 'Trả từng sách',
    description: 'Trả từng sách',
    security: [
        ['bearerAuth' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            description: 'Id chi tiết đơn hàng',
            schema: new OA\Schema(type: 'integer')
        ),
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['return_method', 'pickup_info'],
            properties: [
                new OA\Property(property: 'return_method', type: 'string', description: 'Phương thức trả sách', default: 'enum => pickup | library'),
                new OA\Property(
                    property: 'pickup_info',
                    type: 'object',
                    description: 'Thông tin lấy sách',
                    properties: [
                        new OA\Property(property: 'fullname', type: 'string', description: 'Họ tên'),
                        new OA\Property(property: 'phone', type: 'string', description: 'Số điện thoại'),
                        new OA\Property(property: 'address', type: 'string', description: 'Địa chỉ'),
                    ]
                ),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Trả từng sách',
        ),
        new OA\Response(
            response: 400,
            description: 'Dữ liệu không hợp lệ'
        ),
        new OA\Response(
            response: 500,
            description: 'Lỗi không xác định'
        ),
    ]
)]

#[OA\Post(
    path: '/api/v1/account/orders/update-payment/{id}',
    operationId: 'updatePaymentOrder',
    tags: ['Account / Orders'],
    summary: 'Update payment order',
    description: 'Update payment order',
    security: [
        ['bearerAuth' => []]
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['body'],
            properties: [
                new OA\Property(property: 'body', type: 'string', description: 'Thông tin giao dịch'),
                new OA\Property(property: 'status', type: 'string', default: 'enum => success | canceled', description: 'Trạng thái giao dịch'),
            ]
        )
    ),
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
            description: 'Cancel payment order successfully'
        ),
        new OA\Response(
            response: 400,
            description: 'Dữ liệu không hợp lệ',
        ),
        new OA\Response(
            response: 500,
            description: 'Cancel payment order failed',
        ),
    ]
)]



class OrderController extends Controller
{
    private string $payOSClientId;
    private string $payOSApiKey;
    private string $payOSChecksumKey;



    public function __construct()
    {
        $this->payOSClientId = env("PAYOS_CLIENT_ID");
        $this->payOSApiKey = env("PAYOS_API_KEY");
        $this->payOSChecksumKey = env("PAYOS_CHECKSUM_KEY");
    }

    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'page' => 'integer|min:1',
            'pageSize' => 'integer|min:1',
            'status' => 'in:wating_payment,pending,approved,ready_for_pickup,preparing_shipment,in_transit,extended,active,returning,completed,canceled,overdue',
            'search' => 'string',
        ], [
            'page.integer' => 'Trường trang phải là kiểu số',
            'page.min' => 'Trường trang không được nhỏ hơn 1',
            'pageSize.integer' => 'Trường pageSize phải là kiểu số',
            'pageSize.min' => 'Trường pageSize không được nhỏ hơn 1',
            'status.string' => 'Trường status phải là kiểu chuỗi',
            'status.in' => 'Trường status phải là wating_payment, pending, approved, ready_for_pickup, preparing_shipment, in_transit, extended, active, returning, completed, canceled, overdue',
            'search.string' => 'Trường search phải là kiểu chuỗi',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Dữ liệu không hợp lệ",
                "errors" => $validator->errors()
            ], 400);
        }

        $page = $request->input('page', 1);
        $pageSize = $request->input('pageSize', 10);
        $search = $request->input('search');
        $status = $request->input('status');


        $query = LoanOrders::query()->with(['loanOrderDetails', 'shippingMethod', 'transactions', 'extensionDetails'])->where('user_id', auth()->id());
        $totalItems = $query->count();
        if ($status) {
            $query->where('status', $status);
        } else {
            $query->whereIn('status', ['wating_payment', 'pending', 'approved', 'ready_for_pickup', 'preparing_shipment', 'in_transit', 'active', 'extended', 'returning', 'completed', 'canceled', 'overdue']);
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
            'payment_method' => 'required|string|in:online,cash',
            'delivery_method' => 'required|string|in:pickup,shipper',
            'user_note' => 'nullable|string',
            'total_deposit_fee' => 'required|numeric|min:0',
            'total_service_fee' => 'required|integer|min:1',
            'total_shipping_fee' => 'required|numeric|min:0',
            'total_all_fee' => 'required|numeric|min:0',
            'delivery_info' => 'nullable|array',
            'shipping_method_id' => 'nullable|integer|exists:shipping_methods,id',
            'number_of_days' => 'required|integer|min:1',

            'order_details' => 'required|array',
            'order_details.*.book_details_id' => 'required|integer',
            'order_details.*.service_fee' => 'required|integer|min:1',
            'order_details.*.deposit_fee' => 'required|numeric|min:0',
        ], [
            'payment_method.required' => 'Trường phương thức thanh toán là bắt buộc',
            'payment_method.string' => 'Trường phương thức thanh toán phải là kiểu chuỗi',
            'payment_method.in' => 'Trường phương thức thanh toán phải là wallet hoặc cash',
            'delivery_method.required' => 'Trường phương thức vận chuyển là bắt buộc',
            'delivery_method.string' => 'Trường phương thức vận chuyển phải là kiểu chuỗi',
            'delivery_method.in' => 'Trường phương thức vận chuyển phải là pickup hoặc shipper',
            'user_note.string' => 'Trường ghi chú phải là kiểu chuỗi',
            'total_deposit_fee.required' => 'Trường tiền đặt cọc là bắt buộc',
            'total_deposit_fee.numeric' => 'Trường tiền đặt cọc phải là kiểu số',
            'total_deposit_fee.min' => 'Trường tiền đặt cọc không được nhỏ hơn 0',
            'number_of_days.required' => 'Trường số ngày thuê là bắt buộc',
            'number_of_days.integer' => 'Trường số ngày thuê phải là kiểu số',
            'number_of_days.min' => 'Trường số ngày thuê không được nhỏ hơn 1',

            'total_service_fee.required' => 'Trường tổng phí dịch vụ là bắt buộc',
            'total_service_fee.integer' => 'Trường tổng phí dịch vụ phải là kiểu số',
            'total_service_fee.min' => 'Trường tổng phí dịch vụ không được nhỏ hơn 0',
            'total_all_fee.required' => 'Trường tổng tiền là bắt buộc',
            'total_all_fee.numeric' => 'Trường tổng tiền phải là kiểu số',
            'total_all_fee.min' => 'Trường tổng tiền không được nhỏ hơn 0',

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
                "message" => "Dữ liệu không hợp lệ",
                "errors" => $validator->errors()
            ], 400);
        }

        try {

            $allOrder = LoanOrders::where('user_id', auth()->user()->id)->get();

            // foreach ($allOrder as $order) {
            //     if (in_array($order->status, ['wating_payment', 'pending', 'approved', 'ready_for_pickup', 'preparing_shipment', 'in_transit', 'active', 'extended', 'returning', 'overdue'])) {
            //         return response()->json([
            //             'status' => false,
            //             'message' => 'Create order failed',
            //             'errors' => 'Bạn hiện đang có đơn hàng đang chờ xử lý, vui lòng chờ đơn hàng hiện tại được xử lý xong'
            //         ]);
            //     }
            // }

            if ($request->delivery_method === 'shipper') {
                $validator2 = Validator::make($request->all(), [
                    'delivery_info' => 'required|array',
                    'delivery_info.fullname' => 'required|string',
                    'delivery_info.phone' => 'required|regex:/^(0[35789])[0-9]{8}$/',
                    'delivery_info.address' => 'required|string',
                    'shipping_method_id' => 'required|integer|exists:shipping_methods,id',
                ], [
                    'delivery_info.required' => 'Trường thông tin giao hàng là bắt buộc',
                    'delivery_info.array' => 'Trường thông tin giao hàng phải là kiểu mảng',
                    'delivery_info.fullname.required' => 'Trường họ tên là bắt buộc',
                    'delivery_info.phone.required' => 'Trường số điện thoại là bắt buộc',
                    'delivery_info.address.required' => 'Trường địa chỉ là bắt buộc',
                    'delivery_info.fullname.string' => 'Trường họ tên phải là kiểu chuỗi',
                    'delivery_info.phone.regex' => 'Trường số điện thoại không hợp lệ',
                    'delivery_info.address.string' => 'Trường địa chỉ phải là kiểu chuỗi',
                    'shipping_method_id.integer' => 'Trường phương thức vận chuyển phải là kiểu số',
                    'shipping_method_id.exists' => 'Id phương thức vận chuyển không tồn tại',
                    'shipping_fee.numeric' => 'Trường phí vận chuyển phải là kiểu số',
                    'shipping_fee.min' => 'Trường phí vận chuyển không được nhỏ hơn 0',
                ]);

                if ($validator2->fails()) {
                    return response()->json([
                        "staus" => false,
                        "message" => "Dữ liệu không hợp lệ",
                        "errors" => $validator2->errors()
                    ], 400);
                }
            }

            $bookDetailsIds = collect($request->order_details)->pluck('book_details_id')->toArray();

            foreach ($bookDetailsIds as $bookDetailsId) {
                $bookDetail = BookDetail::find($bookDetailsId);

                if (!$bookDetail) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Create order failed',
                        'errors' => 'Id chi tiết sách không tồn tại'
                    ]);
                }

                if ($bookDetail->status !== 'active') {
                    return response()->json([
                        'status' => false,
                        'message' => 'Create order failed',
                        'errors' => 'Sách không còn trạng thái cho thuê'
                    ]);
                }

                if ($bookDetail->stock < 1) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Create order failed',
                        'errors' => 'Sách đã hết hàng'
                    ]);
                }
            }

            if ($request->payment_method === 'online') {

                $validator3 = Validator::make($request->all(), [
                    'payment_portal' => 'required|string|in:payos,vnpay',
                ], [
                    'payment_portal.required' => 'Trường cổng thanh toán là bắt buộc',
                    'payment_portal.string' => 'Trường cổng thanh toán phải là kiểu chuỗi',
                    'payment_portal.in' => 'Trường cổng thanh toán phải là payos hoặc vnpay',
                ]);

                if ($validator3->fails()) {
                    return response()->json([
                        "staus" => false,
                        "message" => "Dữ liệu không hợp lệ",
                        "errors" => $validator3->errors()
                    ], 400);
                }

                $transaction_code = intval(substr(strtotime(now()) . rand(1000, 9999), -9));

                $order = LoanOrders::create(array_merge($request->all(), [
                    'user_id' => auth()->user()->id,
                    'status' => 'wating_payment',
                ]));

                $orderDetails = $request->order_details;

                $order->loanOrderDetails()->createMany($orderDetails);

                $transaction = Transaction::create([
                    'user_id' => auth()->user()->id,
                    'transaction_code' => $transaction_code,
                    'transaction_type' => 'payment',
                    'loan_order_id' => $order->id,
                    'portal' => $request->payment_portal,
                    'transaction_method' => 'online',
                    'amount' => $request->total_all_fee,
                    'description' => 'Thanh toán đơn thuê ' . $order->order_code,
                ]);

                if (!$transaction) {
                    $order->delete();
                }
            } else {
                $order = LoanOrders::create(array_merge($request->all(), [
                    'user_id' => auth()->user()->id,
                    'expired_date' => $request->has('expired_date') ? date('Y-m-d', strtotime($request->expired_date)) : date('Y-m-d', strtotime('+' . $request->number_of_days . ' days')),
                ]));

                $orderDetails = $request->order_details;
                foreach ($orderDetails as $key => $detail) {
                    $orderDetails[$key]['expired_date'] = $request->has('expired_date') ? date('Y-m-d', strtotime($request->expired_date)) : date('Y-m-d', strtotime('+' . $request->number_of_days . ' days'));
                }

                $order->loanOrderDetails()->createMany($orderDetails);

                $transaction_code = intval(substr(strtotime(now()) . rand(1000, 9999), -9));

                $transaction = Transaction::create([
                    'user_id' => auth()->user()->id,
                    'transaction_code' => $transaction_code,
                    'portal' => $request->payment_portal,
                    'loan_order_id' => $order->id,
                    'transaction_type' => 'payment',
                    'transaction_method' => 'offline',
                    'amount' => $request->total_all_fee,
                    'description' => 'Thanh toán tiền mặt đơn thuê ' . $order->order_code,
                ]);

                if (!$transaction) {
                    $order->delete();
                }
            }

            foreach ($bookDetailsIds as $bookDetailsId) {
                $bookDetail = BookDetail::find($bookDetailsId);
                $bookDetail->update([
                    'stock' => $bookDetail->stock - 1
                ]);
            }

            $order = LoanOrders::find($order->id);

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
            'id' => 'required',
        ], [
            'id.required' => 'Trường id là bắt buộc',
            'id.exists' => 'Id không tồn tại',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Dữ liệu không hợp lệ",
                "errors" => $validator->errors()
            ], 400);
        }

        try {
            $query = LoanOrders::query()->with(
                'user',
                'shippingMethod',
                'loanOrderDetails',
                'loanOrderDetails.bookDetails',
                'loanOrderDetails.bookReviews',
                'loanOrderDetails.returnHistories',
                'loanOrderDetails.extensionsDetails',
                'loanOrderDetails.bookDetails.publishingCompany',
                'loanOrderDetails.bookDetails.book',
                'loanOrderDetails.bookDetails.book.author',
                'loanOrderDetails.bookDetails.book.category',
                'loanOrderDetails.bookDetails.book.shelve',
                'loanOrderDetails.bookDetails.book.shelve.bookcase',
                'transactions',
                'extensions',
                'extensions',
                'extensions.extensionDetails',
                'extensions.extensionDetails.loanOrderDetail',
                'extensions.extensionDetails.loanOrderDetail.bookDetails',
                'extensions.extensionDetails.loanOrderDetail.bookDetails.book',
            );

            if (is_numeric($id)) {
                $order = $query->where('id', $id)->first();
            } else {
                $order = $query->where('order_code', $id)->first();
            }

            if (!$order) {
                return response()->json([
                    'status' => false,
                    'message' => 'Get order failed',
                    'errors' => 'Id hoặc mã đơn hàng không tồn tại'
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Get order successfully',
                'data' => $order
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Get order failed',
                'errors' => $th->getMessage()
            ]);
        }
    }

    public function paymentOrder(Request $request, $id)
    {
        $validator = Validator::make(array_merge(['id' => $id], $request->all()), [
            'id' => 'required|exists:loan_orders,id',
            'payment_portal' => 'required|string|in:payos,vnpay',
        ], [
            'id.required' => 'Trường id là bắt buộc',
            'id.exists' => 'Id không tồn tại',
            'payment_portal.required' => 'Trường cổng thanh toán là bắt buộc',
            'payment_portal.string' => 'Trường cổng thanh toán phải là kiểu chuỗi',
            'payment_portal.in' => 'Trường cổng thanh toán phải là payos hoặc vnpay',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Dữ liệu không hợp lệ",
                "errors" => $validator->errors()
            ], 400);
        }

        $loanOrder = LoanOrders::find($id);
        $transaction = Transaction::where('loan_order_id', $loanOrder->id)->first();

        if ($loanOrder->status !== 'wating_payment') {
            return response()->json([
                'status' => false,
                'message' => 'Payment order failed',
                'errors' => 'Không thể thanh toán đơn hàng'
            ]);
        }

        try {
            if ($request->payment_portal === 'payos') {
                $body = $request->input();
                $body["amount"] = intval($transaction->amount);
                $body["orderCode"] = intval($transaction->transaction_code);
                $body["description"] =  $loanOrder->order_code;
                $body["expiredAt"] = now()->addMinutes(30)->getTimestamp();
                $body["returnUrl"] = env('CLIENT_URL') . "/payment/result?portal=payos&amount=" . $transaction->amount . "&description=" . $transaction->transaction_code;
                $body["cancelUrl"] = env('CLIENT_URL') . "/payment/result?portal=payos&amount=" . $transaction->amount . "&description=" . $transaction->transaction_code;
                $payOS = new PayOS($this->payOSClientId, $this->payOSApiKey, $this->payOSChecksumKey);

                $response = $payOS->createPaymentLink($body);

                $transaction->update([
                    'status' => 'pending',
                    'expired_at' => now()->addMinutes(30),
                    'extra_info' => $response
                ]);

                return response()->json([
                    'status' => true,
                    'message' => 'Payment order successfully',
                    'data' => $response
                ]);
            } else {
                $vnpay = new VnpayCreatePayment();

                $response = $vnpay->createPaymentLink($transaction->amount, $transaction->transaction_code, "Thanh toán đơn hàng " . $loanOrder->order_code);

                $transaction->update([
                    'status' => 'pending',
                    'portal' => 'vnpay',
                    'expired_at' => now()->addMinutes(30),
                    'extra_info' => [
                        'checkoutUrl' => $response
                    ]
                ]);

                return response()->json([
                    'status' => true,
                    'message' => 'Payment order successfully',
                    'data' => [
                        'checkoutUrl' => $response
                    ]
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json([
                "status" => $th->getCode(),
                "message" => $th->getMessage(),
            ]);
        }
    }

    public function cancelOrder(Request $request, $id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|exists:loan_orders,id',
        ], [
            'id.required' => 'Trường id là bắt buộc',
            'id.exists' => 'Id không tồn tại',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Dữ liệu không hợp lệ",
                "errors" => $validator->errors()
            ], 400);
        }

        try {
            $order = LoanOrders::with('loanOrderDetails')->find($id);

            if ($order->status !== 'pending' && $order->status !== 'wating_payment') {
                return response()->json([
                    'status' => false,
                    'message' => 'Update payment order failed',
                    'errors' => 'Không thể cập nhật trạng thái đơn hàng'
                ]);
            }

            foreach ($order->loanOrderDetails as $orderDetail) {
                $orderDetail->bookDetails->update([
                    'stock' => $orderDetail->bookDetails->stock + 1
                ]);

                $orderDetail->update([
                    'status' => 'canceled',
                ]);
            }

            $order->update([
                'status' => 'canceled',
            ]);

            $transaction = Transaction::where('loan_order_id', $order->id)->first();

            if ($transaction) {
                $transaction->update([
                    'status' => 'canceled'
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Cancel order successfully',
                'data' => $order
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Cancel order failed',
                'errors' => $th->getMessage()
            ]);
        }
    }

    public function returnAllOrder(Request $request, $order_id)
    {
        $validator = Validator::make(array_merge(
            $request->all(),
            ['order_id' => $order_id]
        ), [
            'order_id' => 'required|integer|exists:loan_orders,id',
            'return_method' => 'required|string|in:library,pickup',
        ], [
            'order_id.required' => 'Trường id đơn hàng là bắt buộc',
            'order_id.integer' => 'Trường id đơn hàng phải là kiểu số',
            'order_id.exists' => 'Id đơn hàng không tồn tại',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "message" => "Dữ liệu không hợp lệ",
                "errors" => $validator->errors()
            ], 400);
        }
        try {

            $order = LoanOrders::find($order_id);
            $orderDetails = LoanOrderDetails::where('loan_order_id', $order_id)->get();

            if ($order->status != 'active' && $order->status != 'extended') {
                return response()->json([
                    'status' => false,
                    'message' => 'Đơn hàng không ở trạng thái đang mượn',
                ], 400);
            }

            if ($request->return_method == 'pickup') {
                $validator2 = Validator::make($request->all(), [
                    'shipping_method_id' => 'required|integer|exists:shipping_methods,id',
                    'pickup_info' => 'required|array',
                    'pickup_info.fullname' => 'required|string',
                    'pickup_info.phone' => 'required|regex:/^(0[35789])[0-9]{8}$/',
                    'pickup_info.address' => 'required|string',
                ], [
                    'shipping_method_id.required' => 'Trường phương thức vận chuyển là bắt buộc',
                    'shipping_method_id.integer' => 'Trường phương thức vận chuyển phải là kiểu số',
                    'shipping_method_id.exists' => 'Id phương thức vận chuyển không tồn tại',
                    'pickup_info.required' => 'Trường thông tin nhận sách là bắt buộc',
                    'pickup_info.array' => 'Trường thông tin nhận sách phải là kiểu mảng',
                    'pickup_info.fullname.required' => 'Trường họ tên là bắt buộc',
                    'pickup_info.phone.required' => 'Trường số điện thoại là bắt buộc',
                    'pickup_info.address.required' => 'Trường địa chỉ là bắt buộc',
                    'pickup_info.fullname.string' => 'Trường họ tên phải là kiểu chuỗi',
                    'pickup_info.phone.regex' => 'Trường số điện thoại không hợp lệ',
                    'pickup_info.address.string' => 'Trường địa chỉ phải là kiểu chuỗi',
                ]);

                if ($validator2->fails()) {
                    return response()->json([
                        "status" => false,
                        "message" => "Dữ liệu không hợp lệ",
                        "errors" => $validator2->errors()
                    ], 400);
                }

                $transaction_code = intval(substr(strtotime(now()) . rand(1000, 9999), -9));
                $shipping_fee = ShippingMethod::find($request->shipping_method_id)->fee;

                $transaction = Transaction::create([
                    'user_id' => auth()->user()->id,
                    'transaction_code' => $transaction_code,
                    'portal' => 'payos',
                    'loan_order_id' => $order->id,
                    'transaction_type' => 'payment',
                    'transaction_method' => 'online',
                    'amount' => $shipping_fee,
                    'description' => 'Thanh toán tiền ship ' . $order->order_code,
                ]);
                $body = $request->input();
                $body["amount"] = intval($shipping_fee);
                $body["orderCode"] = intval($transaction->transaction_code);
                $body["description"] =  $order->order_code;
                $body["expiredAt"] = now()->addMinutes(30)->getTimestamp();
                $body["returnUrl"] = env('CLIENT_URL') . "/payment/result?portal=payos&transaction_type=extended&description=" . $transaction->transaction_code;
                $body["cancelUrl"] = env('CLIENT_URL') . "/payment/result?portal=payos&transaction_type=extended&description=" . $transaction->transaction_code;
                $payOS = new PayOS($this->payOSClientId, $this->payOSApiKey, $this->payOSChecksumKey);

                $response = $payOS->createPaymentLink($body);

                $transaction->update([
                    'status' => 'pending',
                    'expired_at' => now()->addMinutes(30),
                    'extra_info' => $response
                ]);

                $order->update([
                    'status' => 'returning'
                ]);

                foreach ($orderDetails as $orderDetail) {
                    $orderDetail->update([
                        'status' => 'returning'
                    ]);

                    $orderDetail->createReturnHistory([
                        'return_date' => now(),
                        'status' => 'pending'
                    ]);
                }

                $order->update([
                    'status' => 'returning'
                ]);

                return response()->json([
                    'status' => true,
                    'message' => 'Return all order successfully',
                    'data' => $order
                ]);
            } else {
                $order->update([
                    'status' => 'returning'
                ]);

                foreach ($orderDetails as $orderDetail) {
                    $orderDetail->update([
                        'status' => 'returning'
                    ]);

                    $orderDetail->createReturnHistory([
                        'return_date' => now(),
                        'status' => 'pending'
                    ]);
                }

                return response()->json([
                    'status' => true,
                    'message' => 'Return all order successfully',
                    'data' => $order
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Return all order failed',
                'errors' => $th->getMessage()
            ]);
        }
    }

    public function returnEachBook(Request $request, $id)
    {
        $validator = Validator::make(array_merge(
            $request->all(),
            ['loan_order_details_id' => $id]
        ), [
            'loan_order_details_id' => 'required|integer|exists:loan_order_details,id',
            'return_method' => 'required|string|in:library,pickup',
        ], [
            'loan_order_details_id.required' => 'Trường id chi tiết đơn hàng là bắt buộc',
            'loan_order_details_id.integer' => 'Trường id chi tiết đơn hàng phải là kiểu số',
            'loan_order_details_id.exists' => 'Id chi tiết đơn hàng không tồn tại',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "message" => "Dữ liệu không hợp lệ",
                "errors" => $validator->errors()
            ], 400);
        }

        $orderDetail = LoanOrderDetails::find($id);
        $order = LoanOrders::find($orderDetail->loan_order_id);

        if ($orderDetail->status != 'active' && $orderDetail->status != 'extended') {
            return response()->json([
                'status' => false,
                'message' => 'Chi tiết đơn hàng không ở trạng thái đang mượn',
            ], 400);
        }

        try {

            if ($request->return_method == 'pickup') {
                $validator2 = Validator::make($request->all(), [
                    'shipping_method_id' => 'required|integer|exists:shipping_methods,id',
                    'pickup_info' => 'required|array',
                    'pickup_info.fullname' => 'required|string',
                    'pickup_info.phone' => 'required|regex:/^(0[35789])[0-9]{8}$/',
                    'pickup_info.address' => 'required|string',
                ], [
                    'shipping_method_id.required' => 'Trường phương thức vận chuyển là bắt buộc',
                    'shipping_method_id.integer' => 'Trường phương thức vận chuyển phải là kiểu số',
                    'shipping_method_id.exists' => 'Id phương thức vận chuyển không tồn tại',
                    'pickup_info.required' => 'Trường thông tin nhận sách là bắt buộc',
                    'pickup_info.array' => 'Trường thông tin nhận sách phải là kiểu mảng',
                    'pickup_info.fullname.required' => 'Trường họ tên là bắt buộc',
                    'pickup_info.phone.required' => 'Trường số điện thoại là bắt buộc',
                    'pickup_info.address.required' => 'Trường địa chỉ là bắt buộc',
                    'pickup_info.fullname.string' => 'Trường họ tên phải là kiểu chuỗi',
                    'pickup_info.phone.regex' => 'Trường số điện thoại không hợp lệ',
                    'pickup_info.address.string' => 'Trường địa chỉ phải là kiểu chuỗi',
                ]);

                if ($validator2->fails()) {
                    return response()->json([
                        "status" => false,
                        "message" => "Dữ liệu không hợp lệ",
                        "errors" => $validator2->errors()
                    ], 400);
                }

                $transaction_code = intval(substr(strtotime(now()) . rand(1000, 9999), -9));
                $shipping_fee = ShippingMethod::find($request->shipping_method_id)->fee;

                $transaction = Transaction::create([
                    'user_id' => auth()->user()->id,
                    'transaction_code' => $transaction_code,
                    'portal' => 'payos',
                    'loan_order_id' => $orderDetail->loan_order_id,
                    'transaction_type' => 'payment',
                    'transaction_method' => 'online',
                    'amount' => $shipping_fee,
                    'description' => 'Thanh toán tiền ship ' . $order->order_code,
                ]);
                $body = $request->input();
                $body["amount"] = intval($shipping_fee);
                $body["orderCode"] = intval($transaction->transaction_code);
                $body["description"] =  $order->order_code;
                $body["expiredAt"] = now()->addMinutes(30)->getTimestamp();
                $body["returnUrl"] = env('CLIENT_URL') . "/payment/result?portal=payos&transaction_type=extended&description=" . $transaction->transaction_code;
                $body["cancelUrl"] = env('CLIENT_URL') . "/payment/result?portal=payos&transaction_type=extended&description=" . $transaction->transaction_code;
                $payOS = new PayOS($this->payOSClientId, $this->payOSApiKey, $this->payOSChecksumKey);

                $response = $payOS->createPaymentLink($body);

                $transaction->update([
                    'status' => 'pending',
                    'expired_at' => now()->addMinutes(30),
                    'extra_info' => $response
                ]);

                $orderDetail->createReturnHistory(array_merge(
                    $request->all(),
                    [
                        'return_date' => now(),
                        'status' => 'pending'
                    ]
                ));

                $orderDetail->update([
                    'status' => 'returning',
                ]);

                return response()->json([
                    'status' => true,
                    'message' => 'Trả sách thành công',
                    'data' => $response
                ]);
            } else {
                $orderDetail->createReturnHistory(array_merge(
                    $request->all(),
                    [
                        'return_date' => now(),
                        'status' => 'pending'
                    ]
                ));

                $orderDetail->update([
                    'status' => 'returning',
                ]);

                return response()->json([
                    'status' => true,
                    'message' => 'Trả sách thành công',
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Hoàn thành đơn hàng thất bại',
                'errors' => $th->getMessage()
            ], 500);
        }
    }

    public function updatePayment(Request $request, $id)
    {
        $validator = Validator::make(array_merge(
            $request->all(),
            ['transaction_code' => $id]
        ), [
            'transaction_code' => 'required|exists:transactions,transaction_code',
            'status' => 'required|string|in:success,canceled',
            'body' => 'required|array',
        ], [
            'transaction_code.required' => 'Trường id là bắt buộc',
            'body.required' => 'Trường nội dung hủy thanh toán là bắt buộc',
            'transaction_code.exists' => 'Id không tồn tại',
            'status.required' => 'Trường trạng thái thanh toán là bắt buộc',
            'status.string' => 'Trường trạng thái thanh toán phải là kiểu chuỗi',
            'status.in' => 'Trường trạng thái thanh toán phải là success hoặc canceled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "message" => "Dữ liệu không hợp lệ",
                "errors" => $validator->errors()
            ], 400);
        }

        try {
            $transaction = Transaction::where('transaction_code', $id)->first();

            if ($transaction->status !== 'pending') {
                return response()->json([
                    'status' => false,
                    'message' => 'Update payment failed',
                    'errors' => 'Không thể hủy thanh toán'
                ]);
            }
            $order = LoanOrders::find($transaction->loan_order_id);

            if ($request->status === 'canceled') {
                $transaction->update([
                    'status' => 'canceled',
                    'extra_info' => [
                        $transaction->extra_info,
                        $request->body
                    ]
                ]);


                if ($order) {
                    $order->update([
                        'status' => 'canceled'
                    ]);

                    foreach ($order->loanOrderDetails as $orderDetail) {
                        $orderDetail->update([
                            'status' => 'canceled'
                        ]);
                    }
                }
            } else {
                $transaction->update([
                    'status' => 'completed',
                    'extra_info' => [
                        $transaction->extra_info,
                        $request->body
                    ]
                ]);

                if ($order) {
                    $order->update([
                        'status' => 'approved'
                    ]);

                    foreach ($order->loanOrderDetails as $orderDetail) {
                        $orderDetail->update([
                            'status' => 'pending'
                        ]);
                    }
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Update payment successfully',
                'data' => $transaction
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Update payment failed',
                'errors' => $th->getMessage()
            ]);
        }
    }

    public function extensionAllOrder(Request $request, $id)
    {
        $validator = Validator::make(array_merge(
            $request->all(),
            ['id' => $id]
        ), [
            'id' => 'required|exists:loan_orders,id',
            'extended_method' => 'required|string|in:online,cash',
        ], [
            'id.required' => 'Trường id là bắt buộc',
            'id.exists' => 'Id không tồn tại',
            'extended_method.required' => 'Trường phương thức gia hạn là bắt buộc',
            'extended_method.string' => 'Trường phương thức gia hạn phải là kiểu chuỗi',
            'extended_method.in' => 'Trường phương thức gia hạn phải là online hoặc cash',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Dữ liệu không hợp lệ",
                "errors" => $validator->errors()
            ], 400);
        }

        try {
            $order = LoanOrders::with('loanOrderDetails')->find($id);

            if ($order->status !== 'active' && $order->status !== 'extended') {
                return response()->json([
                    'status' => false,
                    'message' => 'Extension order failed',
                    'errors' => 'Không thể gia hạn đơn hàng'
                ]);
            }

            if ($order->current_extensions >= $order->max_extensions) {
                return response()->json([
                    'status' => false,
                    'message' => 'Extension order failed',
                    'errors' => 'Đơn hàng đã đạt số lần gia hạn tối đa'
                ]);
            }

            if ($order->current_due_date < now()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Extension order failed',
                    'errors' => 'Đơn hàng đã quá hạn'
                ]);
            }

            $extension_fee = count($order->loanOrderDetails) * 10000;

            if ($request->extended_method == 'online') {
                $extension = Extensions::create([
                    'loan_order_id' => $order->id,
                    'extension_date' => now(),
                    'new_due_date' => date('Y-m-d', strtotime($order->current_due_date . ' + 5 days')),
                    'extension_fee' => $extension_fee,
                    'status' => 'pending'
                ]);

                $transaction_code = intval(substr(strtotime(now()) . rand(1000, 9999), -9));

                $transaction = Transaction::create([
                    'user_id' => auth()->user()->id,
                    'transaction_code' => $transaction_code,
                    'portal' => 'payos',
                    'loan_order_id' => $order->id,
                    'transaction_type' => 'extend',
                    'transaction_method' => 'online',
                    'amount' => $extension_fee,
                    'description' => 'Thanh toán gia hạn ' . $order->order_code,
                ]);
                $body = $request->input();
                $body["amount"] = intval($extension_fee);
                $body["orderCode"] = intval($transaction->transaction_code);
                $body["description"] =  $order->order_code;
                $body["expiredAt"] = now()->addMinutes(30)->getTimestamp();
                $body["returnUrl"] = env('CLIENT_URL') . "/payment/result?portal=payos&transaction_type=extended&amount=" . $extension_fee . "&description=" . $transaction->transaction_code;
                $body["cancelUrl"] = env('CLIENT_URL') . "/payment/result?portal=payos&transaction_type=extended&amount=" . $extension_fee . "&description=" . $transaction->transaction_code;
                $payOS = new PayOS($this->payOSClientId, $this->payOSApiKey, $this->payOSChecksumKey);

                $response = $payOS->createPaymentLink($body);

                $transaction->update([
                    'status' => 'pending',
                    'expired_at' => now()->addMinutes(30),
                    'extra_info' => $response
                ]);

                $extensionDetails = $order->loanOrderDetails->map(function ($orderDetail) use ($extension) {
                    return [
                        'extension_id' => $extension->id,
                        'loan_order_detail_id' => $orderDetail->id,
                        'new_due_date' => date('Y-m-d', strtotime($orderDetail->current_due_date . ' + 4 days')),
                        'extension_fee' => 10000
                    ];
                });

                $extension->extensionDetails()->createMany($extensionDetails);

                $extension->update([
                    'fee_transaction_id' => $transaction->id
                ]);

                foreach ($order->loanOrderDetails as $orderDetail) {
                    $orderDetail->update([
                        'current_due_date' => date('Y-m-d', strtotime($orderDetail->current_due_date . ' + 4 days')),
                        'status' => 'extended'
                    ]);
                }

                $order->update([
                    'status' => 'extended',
                    'current_extensions' => $order->current_extensions + 1,
                    'current_due_date' => date('Y-m-d', strtotime($order->current_due_date . ' + 5 days'))
                ]);

                return response()->json([
                    'status' => true,
                    'message' => 'Extension order successfully',
                    'data' => [
                        'order' => $order,
                        'extension' => $extension,
                        'transaction' => $transaction
                    ]
                ]);
            } else {
                $extension = Extensions::create([
                    'loan_order_id' => $order->id,
                    'extension_date' => now(),
                    'new_due_date' => date('Y-m-d', strtotime($order->current_due_date . ' + 5 days')),
                    'extension_fee' => $extension_fee,
                    'status' => 'approved'
                ]);

                $extensionDetails = $order->loanOrderDetails->map(function ($orderDetail) use ($extension) {
                    return [
                        'extension_id' => $extension->id,
                        'loan_order_detail_id' => $orderDetail->id,
                        'new_due_date' => date('Y-m-d', strtotime($orderDetail->current_due_date . ' + 4 days')),
                        'extension_fee' => 10000
                    ];
                });

                $extension->extensionDetails()->createMany($extensionDetails);

                $transaction_code = intval(substr(strtotime(now()) . rand(1000, 9999), -9));

                $transaction = Transaction::create([
                    'user_id' => auth()->user()->id,
                    'transaction_code' => $transaction_code,
                    'portal' => 'payos',
                    'loan_order_id' => $order->id,
                    'transaction_type' => 'extend',
                    'transaction_method' => 'offline',
                    'amount' => $extension_fee,
                    'description' => 'Thanh toán gia hạn ' . $order->order_code,
                ]);

                $extension->update([
                    'fee_transaction_id' => $transaction->id
                ]);

                foreach ($order->loanOrderDetails as $orderDetail) {
                    $orderDetail->update([
                        'current_due_date' => date('Y-m-d', strtotime($orderDetail->current_due_date . ' + 4 days')),
                        'status' => 'extended'
                    ]);
                }

                $order->update([
                    'status' => 'extended',
                    'current_extensions' => $order->current_extensions + 1,
                    'current_due_date' => date('Y-m-d', strtotime($order->current_due_date . ' + 5 days'))
                ]);

                return response()->json([
                    'status' => true,
                    'message' => 'Extension order successfully',
                    'data' => [
                        'order' => $order,
                        'extension' => $extension,
                        'transaction' => $transaction
                    ]
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Extension order failed',
                'errors' => $th->getMessage()
            ]);
        }
    }

    public function extensionEachBook(Request $request, $id)
    {
        $validator = Validator::make(array_merge(
            $request->all(),
            ['id' => $id]
        ), [
            'id' => "required|exists:loan_order_details,id",
            'extended_method' => 'required|string|in:online,cash',
        ], [
            'id.required' => 'Trường id là bắt buộc',
            'id.exists' => 'Id không tồn tại',
            'extended_method.required' => 'Trường phương thức gia hạn là bắt buộc',
            'extended_method.string' => 'Trường phương thức gia hạn phải là kiểu chuỗi',
            'extended_method.in' => 'Trường phương thức gia hạn phải là online hoặc cash',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Dữ liệu không hợp lệ",
                "errors" => $validator->errors()
            ], 400);
        }

        try {
            $orderDetail = LoanOrderDetails::find($request->id);
            $order = LoanOrders::with('loanOrderDetails')->find($orderDetail->loan_order_id);

            if ($order->status !== 'active' && $order->status !== 'extended') {
                return response()->json([
                    'status' => false,
                    'message' => 'Extension order failed',
                    'errors' => 'Không thể gia hạn đơn hàng'
                ]);
            }

            if ($order->current_extensions >= $order->max_extensions) {
                return response()->json([
                    'status' => false,
                    'message' => 'Extension order failed',
                    'errors' => 'Đơn hàng đã đạt số lần gia hạn tối đa'
                ]);
            }

            if ($order->current_due_date < now()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Extension order failed',
                    'errors' => 'Đơn hàng đã quá hạn'
                ]);
            }

            $extension_fee = count($order->loanOrderDetails) * 10000;

            if ($request->extended_method == 'online') {
                $extension = Extensions::create([
                    'loan_order_id' => $order->id,
                    'extension_date' => now(),
                    'new_due_date' => date('Y-m-d', strtotime($order->current_due_date . ' + 5 days')),
                    'extension_fee' => $extension_fee,
                    'status' => 'pending'
                ]);

                $transaction_code = intval(substr(strtotime(now()) . rand(1000, 9999), -9));

                $transaction = Transaction::create([
                    'user_id' => auth()->user()->id,
                    'transaction_code' => $transaction_code,
                    'portal' => 'payos',
                    'loan_order_id' => $order->id,
                    'transaction_type' => 'extend',
                    'transaction_method' => 'online',
                    'amount' => $extension_fee,
                    'description' => 'Thanh toán gia hạn ' . $order->order_code,
                ]);
                $body = $request->input();
                $body["amount"] = intval($extension_fee);
                $body["orderCode"] = intval($transaction->transaction_code);
                $body["description"] =  $order->order_code;
                $body["expiredAt"] = now()->addMinutes(30)->getTimestamp();
                $body["returnUrl"] = env('CLIENT_URL') . "/payment/result?portal=payos&transaction_type=extended&amount=" . $extension_fee . "&description=" . $transaction->transaction_code;
                $body["cancelUrl"] = env('CLIENT_URL') . "/payment/result?portal=payos&transaction_type=extended&amount=" . $extension_fee . "&description=" . $transaction->transaction_code;
                $payOS = new PayOS($this->payOSClientId, $this->payOSApiKey, $this->payOSChecksumKey);

                $response = $payOS->createPaymentLink($body);

                $transaction->update([
                    'status' => 'pending',
                    'expired_at' => now()->addMinutes(30),
                    'extra_info' => $response
                ]);

                $extension->extensionDetails()->createMany([[
                    'extension_id' => $extension->id,
                    'loan_order_detail_id' => $id,
                    'new_due_date' => date('Y-m-d', strtotime($orderDetail->current_due_date . ' + 5 days')),
                    'extension_fee' => 10000
                ]]);

                $extension->update([
                    'fee_transaction_id' => $transaction->id
                ]);

                $orderDetail->update([
                    'current_due_date' => date('Y-m-d', strtotime($orderDetail->current_due_date . ' + 5 days')),
                    'status' => 'extended'
                ]);

                $order->update([
                    'status' => 'extended',
                    'current_extensions' => $order->current_extensions + 1,
                    'current_due_date' => date('Y-m-d', strtotime($order->current_due_date . ' + 5 days'))
                ]);

                return response()->json([
                    'status' => true,
                    'message' => 'Extension order successfully',
                    'data' => [
                        'order' => $order,
                        'extension' => $extension,
                        'transaction' => $transaction
                    ]
                ]);
            } else {
                $extension = Extensions::create([
                    'loan_order_id' => $order->id,
                    'extension_date' => now(),
                    'new_due_date' => date('Y-m-d', strtotime($order->current_due_date . ' + 5 days')),
                    'extension_fee' => $extension_fee,
                ]);

                $extension->extensionDetails()->create([
                    'extension_id' => $extension->id,
                    'loan_order_detail_id' => $id,
                    'new_due_date' => date('Y-m-d', strtotime($orderDetail->current_due_date . ' + 4 days')),
                    'extension_fee' => 10000
                ]);

                $transaction_code = intval(substr(strtotime(now()) . rand(1000, 9999), -9));

                $transaction = Transaction::create([
                    'user_id' => auth()->user()->id,
                    'transaction_code' => $transaction_code,
                    'portal' => 'payos',
                    'loan_order_id' => $order->id,
                    'transaction_type' => 'extend',
                    'transaction_method' => 'offline',
                    'amount' => $extension_fee,
                    'description' => 'Thanh toán gia hạn ' . $order->order_code,
                ]);

                $extension->update([
                    'fee_transaction_id' => $transaction->id
                ]);

                $orderDetail->update([
                    'current_due_date' => date('Y-m-d', strtotime($orderDetail->current_due_date . ' + 4 days')),
                    'status' => 'extended'
                ]);

                $order->update([
                    'status' => 'extended',
                    'current_extensions' => $order->current_extensions + 1,
                    'current_due_date' => date('Y-m-d', strtotime($order->current_due_date . ' + 5 days'))
                ]);

                return response()->json([
                    'status' => true,
                    'message' => 'Extension order successfully',
                    'data' => [
                        'order' => $order,
                        'extension' => $extension,
                        'transaction' => $transaction
                    ]
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Extension order failed',
                'errors' => $th->getMessage()
            ]);
        }
    }
}

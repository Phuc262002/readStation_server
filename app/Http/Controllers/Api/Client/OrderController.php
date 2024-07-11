<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Api\VNPay\VnpayCreatePayment;
use App\Http\Controllers\Controller;
use App\Models\BookDetail;
use App\Models\Extensions;
use App\Models\LoanOrderDetails;
use App\Models\LoanOrders;
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
            description: 'Validation error',
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
            description: 'Validation error',
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
            required: ['payment_method', 'payment_portal', 'discount', 'delivery_method', 'total_deposit_fee', 'total_service_fee', 'total_shipping_fee', 'total_all_fee', 'order_details'],
            properties: [
                new OA\Property(property: 'payment_method', type: 'string', description: 'Phương thức thanh toán', enum: ['online', 'cash']),
                new OA\Property(property: 'payment_portal', type: 'string', description: 'Cổng thanh toán', enum: ['payos', 'vnpay']),
                new OA\Property(property: 'delivery_method', type: 'string', description: 'Phương thức vận chuyển', enum: ['library', 'shipper']),
                new OA\Property(property: 'user_note', type: 'string', description: 'Ghi chú'),
                new OA\Property(property: 'discount', type: 'number', description: 'Giảm giá'),
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
            description: 'Validation error',
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
            description: 'Validation error',
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
            description: 'Validation error',
        ),
        new OA\Response(
            response: 500,
            description: 'Cancel order failed',
        ),
    ]
)]

#[OA\Post(
    path: '/api/v1/account/orders/extension/{id}',
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
    responses: [
        new OA\Response(
            response: 200,
            description: 'Extension all order successfully'
        ),
        new OA\Response(
            response: 400,
            description: 'Validation error',
        ),
        new OA\Response(
            response: 500,
            description: 'Extension all order failed',
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
            description: 'Validation error'
        ),
        new OA\Response(
            response: 500,
            description: 'Lỗi không xác định'
        ),
    ]
)]

#[OA\Post(
    path: '/api/v1/account/orders/cancel-payment/{id}',
    operationId: 'cancelPaymentOrder',
    tags: ['Account / Orders'],
    summary: 'Cancel payment order',
    description: 'Cancel payment order',
    security: [
        ['bearerAuth' => []]
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['body'],
            properties: [
                new OA\Property(property: 'body', type: 'string', description: 'Lý do hủy đơn hàng'),
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
            description: 'Validation error',
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
            'discount' => 'nullable|numeric|min:0',
            'total_deposit_fee' => 'required|numeric|min:0',
            'total_service_fee' => 'required|integer|min:1',
            'total_shipping_fee' => 'required|numeric|min:0',
            'total_all_fee' => 'required|numeric|min:0',
            'delivery_info' => 'nullable|array',
            'shipping_method_id' => 'nullable|integer|exists:shipping_methods,id',

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
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }

        try {

            $allOrder = LoanOrders::where('user_id', auth()->user()->id)->get();

            foreach ($allOrder as $order) {
                if (in_array($order->status, ['wating_payment', 'pending', 'approved', 'ready_for_pickup', 'preparing_shipment', 'in_transit', 'active', 'extended', 'returning', 'overdue'])) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Create order failed',
                        'errors' => 'Bạn hiện đang có đơn hàng đang chờ xử lý, vui lòng chờ đơn hàng hiện tại được xử lý xong'
                    ]);
                }
            }

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
                        "message" => "Validation error",
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
                        "message" => "Validation error",
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
                    'expired_date' => $request->has('expired_date') ? date('Y-m-d', strtotime($request->expired_date)) : date('Y-m-d', strtotime('+4 days')),
                ]));

                $orderDetails = $request->order_details;
                foreach ($orderDetails as $key => $detail) {
                    $orderDetails[$key]['expired_date'] = $request->has('expired_date') ? date('Y-m-d', strtotime($request->expired_date)) : date('Y-m-d', strtotime('+4 days'));
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
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }

        try {
            $query = LoanOrders::query()->with(
                'user',
                'shippingMethod',
                'loanOrderDetails',
                'loanOrderDetails.bookDetails',
                'loanOrderDetails.extensionsDetails',
                'loanOrderDetails.bookDetails.publishingCompany',
                'loanOrderDetails.bookDetails.book',
                'loanOrderDetails.bookDetails.book.author',
                'loanOrderDetails.bookDetails.book.category',
                'loanOrderDetails.bookDetails.book.shelve',
                'loanOrderDetails.bookDetails.book.shelve.bookcase',
                'transactions',
                'extensions',
                'extensions.extensionDetails',
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
                "message" => "Validation error",
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
                $body["returnUrl"] = "http://localhost:3000/account/wallet/transaction-success?portal=payos";
                $body["cancelUrl"] = "http://localhost:3000/payment/result?portal=payos";
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

    public function cancelOrder($id)
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
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }

        try {
            $order = LoanOrders::with('loanOrderDetails')->find($id);

            if ($order->status !== 'pending' && $order->status !== 'wating_payment') {
                return response()->json([
                    'status' => false,
                    'message' => 'Cancel order failed',
                    'errors' => 'Không thể hủy đơn hàng'
                ]);
            }

            foreach ($order->loanOrderDetails as $orderDetail) {
                $orderDetail->bookDetails->update([
                    'stock' => $orderDetail->bookDetails->stock + 1
                ]);

                $orderDetail->update([
                    'status' => 'canceled'
                ]);
            }

            $order->update([
                'status' => 'canceled'
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

    public function extensionAllOrder($id)
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
                "message" => "Validation error",
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
                    'errors' => 'Đơn hàng đã hết lượt gia hạn'
                ]);
            }

            if ($order->discount != 0) {
                $extension_fee = $order->discount / 100 * (count($order->loanOrderDetails) * 10000);
            } else {
                $extension_fee = count($order->loanOrderDetails) * 10000;
            }

            $extension = Extensions::create([
                'loan_order_id' => $order->id,
                'extension_date' => now(),
                'new_due_date' => date('Y-m-d', strtotime($order->current_due_date . ' + 4 days')),
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
                'transaction_code' => $transaction_code,
                'transaction_type' => 'payment',
                'transaction_method' => 'wallet',
                'amount' => $extension_fee,
                'status' => 'completed',
                'completed_at' => now(),
                'description' => 'Thanh toán gia hạn đơn thuê ' . $order->order_code,
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
                'current_extensions' => $order->current_extensions + 1
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Extension order successfully',
                'data' => $order
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Extension order failed',
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
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }

        $orderDetail = LoanOrderDetails::find($id);

        if ($orderDetail->status != 'active' && $orderDetail->status != 'extended') {
            return response()->json([
                'status' => false,
                'message' => 'Chi tiết đơn hàng không ở trạng thái đang mượn',
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
                    "message" => "Validation error",
                    "errors" => $validator2->errors()
                ], 400);
            }
        }

        try {

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
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Hoàn thành đơn hàng thất bại',
                'errors' => $th->getMessage()
            ], 500);
        }
    }

    public function cancelPayment(Request $request, $id)
    {
        $validator = Validator::make(array_merge(
            $request->all(),
            ['transaction_code' => $id]
        ), [
            'transaction_code' => 'required|exists:transactions,transaction_code',
            'body' => 'required|array',
        ], [
            'transaction_code.required' => 'Trường id là bắt buộc',
            'body.required' => 'Trường lý do hủy đơn hàng là bắt buộc',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }

        try {
            $transaction = Transaction::where('transaction_code', $id)->first();

            if ($transaction->status !== 'pending') {
                return response()->json([
                    'status' => false,
                    'message' => 'Cancel payment order failed',
                    'errors' => 'Không thể hủy thanh toán'
                ]);
            }

            $transaction->update([
                'status' => 'canceled',
                'extra_info' => [
                    $transaction->extra_info,
                    $request->body
                ]
            ]);

            $order = LoanOrders::find($transaction->loan_order_id);

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

            return response()->json([
                'status' => true,
                'message' => 'Cancel payment order successfully',
                'data' => $transaction
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Cancel payment order failed',
                'errors' => $th->getMessage()
            ]);
        }
    }
}

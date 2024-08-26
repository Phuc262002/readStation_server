<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bookcase;
use App\Models\BookDetail;
use App\Models\BookReviews;
use App\Models\InvoiceEnter;
use App\Models\LoanOrderDetails;
use App\Models\LoanOrders;
use App\Models\Post;
use App\Models\ReturnHistory;
use App\Models\Shelve;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/admin/dashboard/statistic-pie-user',
    tags: ['Admin / Dashboard'],
    operationId: 'staticPieUser',
    summary: 'Get static user',
    description: 'Get static user',
    security: [
        ['bearerAuth' => []]
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get static user successfully!',
        ),
    ],
)]

#[OA\Get(
    path: '/api/v1/admin/dashboard/statistic-column-order',
    tags: ['Admin / Dashboard'],
    operationId: 'staticColumnOrder',
    summary: 'Get static column order',
    description: 'Get static column order',
    security: [
        ['bearerAuth' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'sort',
            in: 'query',
            required: false,
            description: 'Sort by time',
            schema: new OA\Schema(type: 'string', enum: ['all', '1m', '3m', '6m', '9m', '1y'], default: 'all')
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get static column order successfully!',
        ),
    ],
)]

#[OA\Get(
    path: '/api/v1/admin/dashboard/statistic-admin',
    tags: ['Admin / Dashboard'],
    operationId: 'statisticAdmin',
    summary: 'Get statistic admin',
    description: 'Get statistic admin',
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get statistic admin successfully!',
        ),
    ]
)]

#[OA\Get(
    path: '/api/v1/admin/dashboard/book-hire-top-by-month',
    tags: ['Admin / Dashboard'],
    operationId: 'bookHireTopByMonth',
    summary: 'Get top books hire by month',
    description: 'Get top books hire by month',
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get top books hire by month successfully!',
        ),
    ]
)]

#[OA\Get(
    path: '/api/v1/admin/dashboard/invoice-enter-by-month',
    tags: ['Admin / Dashboard'],
    operationId: 'invoiceEnterTopByMonth',
    summary: 'Get top invoice enter by month',
    description: 'Get top invoice enter by month',
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get top invoice enter by month successfully!',
        ),
    ]
)]

class DashboardController extends Controller
{
    public function staticUserPieChart()
    {
        $totalUser = User::count();

        $totalManager = User::where('role_id', '3')->count();
        $totalAdmin = User::where('role_id', '4')->count();
        $totalManagerAll = $totalManager + $totalAdmin;

        $totalStudent = User::where('role_id', '2')->count();
        $totalUserDefault = User::where('role_id', '1')->count();

        return response()->json([
            "status" => true,
            "message" => "Get static user successfully!",
            "data" => [
                "totalUser" => $totalUser,
                "totalManager" => $totalManagerAll,
                "totalStudent" => $totalStudent,
                "totalUserDefault" => $totalUserDefault
            ],
        ], 200);
    }

    public function staticOrderComlumnChart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sort' => 'required|in:all,1m,3m,6m,9m,1y',
        ], [
            'sort.required' => 'Sort là trường bắt buộc',
            'sort.in' => 'Sort phải là một trong các giá trị: all, 1m, 3m, 6m, 9m, 1y',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 400);
        }

        $sort = $request->input('sort', 'all');

        $query = LoanOrders::query();

        if ($sort !== 'all') {
            switch ($sort) {
                case '1m':
                    $startDate = now()->subMonth();
                    break;
                case '3m':
                    $startDate = now()->subMonths(3);
                    break;
                case '6m':
                    $startDate = now()->subMonths(6);
                    break;
                case '9m':
                    $startDate = now()->subMonths(9);
                    break;
                case '1y':
                    $startDate = now()->subYear();
                    break;
            }
            $query->where('created_at', '>=', $startDate);
        } else {
            $startDate = LoanOrders::min('created_at');
        }

        $endDate = now();
        $orderDetails = $query->get();

        $ordersByDate = $orderDetails->groupBy(function ($date) {
            return Carbon::parse($date->created_at)->format('Y-m-d');
        });

        $result = [];
        $period = CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $date) {
            $formattedDate = $date->format('Y-m-d');
            $orders = $ordersByDate->get($formattedDate, collect());
            $totalOrders = $orders->count();
            $completedOrders = $orders->where('status', 'completed')->count();
            $canceledOrders = $orders->where('status', 'canceled')->count();
        
            // Giả định rằng $orders là một collection của các đối tượng Order và mỗi Order có quan hệ với LoanOrderDetails
            $orderIds = $orders->pluck('id');
            $serviceFeeSum = LoanOrderDetails::whereIn('loan_order_id', $orderIds)->where('status', 'completed')->sum('service_fee');
            $fineFeeSum = LoanOrderDetails::whereIn('loan_order_id', $orderIds)->where('status', 'completed')->sum('fine_amount');
            $revenue = ($serviceFeeSum + $fineFeeSum);
        
            $result[] = [
                'date' => $formattedDate,
                'total_orders' => $totalOrders,
                'completed_orders' => $completedOrders,
                'canceled_orders' => $canceledOrders,
                'revenue' => $revenue
            ];
        }        
        // return $orderDetails;

        return response()->json([
            'status' => true,
            'data' => [
                'dataChart' => $result,
                'static' => [
                    'total' => $orderDetails->count(),
                    'completed' => $orderDetails->where('status', 'completed')->count(),
                    'pending' => $orderDetails->where('status', 'wating_payment')->count() +
                        $orderDetails->where('status', 'pending')->count() +
                        $orderDetails->where('status', 'approved')->count() +
                        $orderDetails->where('status', 'ready_for_pickup')->count() +
                        $orderDetails->where('status', 'preparing_shipment')->count() +
                        $orderDetails->where('status', 'returning')->count() +
                        $orderDetails->where('status', 'in_transit')->count(),
                    'canceled' => $orderDetails->where('status', 'canceled')->count(),
                    'active' => $orderDetails->where('status', 'active')->count() +
                        $orderDetails->where('status', 'extended')->count(),
                    'overdue' => $orderDetails->where('status', 'overdue')->count()
                ]
            ]
        ]);
    }



    public function statisticAdmin(Request $request)
    {

        $user = User::count();
        $invoiceEnter = InvoiceEnter::count();
        $bookcase = Bookcase::count();
        $shelve = Shelve::count();
        $book = BookDetail::count();
        $post = Post::count();
        $userVerified = User::where('user_verified_at', '!=', null)->count();

        $serviceFeeSum = LoanOrderDetails::where('status', 'completed')->sum('service_fee');
        $fineFeeSum = LoanOrderDetails::where('status', 'completed')->sum('fine_amount');
        $shippingFeeSum = LoanOrders::where('status', 'completed')->sum('total_shipping_fee');

        $revenue = $serviceFeeSum + $fineFeeSum + $shippingFeeSum;
        $returnHistoy = ReturnHistory::where('status', 'completed')->count();


        return response()->json([
            'status' => true,
            'data' => [
                'revenue' => $revenue,
                'userVerified' => $userVerified,
                'user' => $user,
                'invoiceEnter' => $invoiceEnter,
                'bookcase' => $bookcase,
                'shelve' => $shelve,
                'book' => $book,
                'post' => $post,
                'returnHistory' => $returnHistoy
            ]
        ]);
    }

    public function bookHireTopByMonth(Request $request)
    {
        // Get top book detail IDs based on completed orders in the last month
        $orderDetails = LoanOrderDetails::where('status', 'completed')
            ->where('created_at', '>=', now()->subMonth())
            ->select('book_details_id')
            ->groupBy('book_details_id')
            ->orderByRaw('COUNT(book_details_id) DESC')
            ->limit(20)
            ->pluck('book_details_id');

        // Fetch the book details along with their relationships
        $books = BookDetail::with([
            'book.category',
            'book.author',
            'book.shelve',
            'book.shelve.bookcase',
            'book',
        ])
            ->whereIn('id', $orderDetails)
            ->get();

        // Calculate the average rate, rating total, and hire count for each book
        $books->each(function ($book) {
            $bookReviews = BookReviews::where('book_details_id', $book->id);
            $averageRateRounded = round($bookReviews->avg('rating'), 1);

            $book->average_rate = $averageRateRounded;
            $book->rating_total = $bookReviews->count();
            $book->hire_count = $book->order_details->count(); // Count the total hires

            unset($book->order_details);
        });

        return response()->json([
            'status' => true,
            'message' => 'Top books by month',
            'data' => $books
        ]);
    }


    public function invoiceEnterTopByMonth(Request $request)
    {
        $invoiceEnters = InvoiceEnter::with([
            'user' => function ($query) {
                $query->select('id', 'fullname', 'email', 'avatar');
            },
            'supplier',
            'invoiceEnterDetails'
        ])
            ->where('created_at', '>=', now()->subMonth())
            ->orderBy('total', 'desc')
            ->limit(20)
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Top invoice enters by month',
            'data' => $invoiceEnters
        ]);
    }
}

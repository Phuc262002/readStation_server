<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bookcase;
use App\Models\BookDetail;
use App\Models\InvoiceEnter;
use App\Models\OrderDetail;
use App\Models\Post;
use App\Models\Shelve;
use App\Models\User;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

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
    public function statisticAdmin(Request $request)
    {

        $user = User::count();
        $userHasWallet = User::where('has_wallet', true)->count();
        $invoiceEnter = InvoiceEnter::count();
        $bookcase = Bookcase::count();
        $shelve = Shelve::count();
        $book = BookDetail::count();
        $post = Post::count();

        $serviceFeeSum = OrderDetail::where('status_od', 'completed')->sum('service_fee');
        $fineFeeSum = OrderDetail::where('status_od', 'completed')->sum('fine_fee');

        $revenue = $serviceFeeSum + $fineFeeSum;


        return response()->json([
            'status' => true,
            'data' => [
                'revenue' => $revenue,
                'user' => $user,
                'userActiveWallet' => $userHasWallet,
                'invoiceEnter' => $invoiceEnter,
                'bookcase' => $bookcase,
                'shelve' => $shelve,
                'book' => $book,
                'post' => $post
            ]
        ]);
    }

    public function bookHireTopByMonth(Request $request)
    {
        // Get top book detail IDs based on completed orders in the last month
        $orderDetails = OrderDetail::where('status_od', 'completed')
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
            'book' => function ($query) {
                $query->where('status', 'active');
            }
        ])
            ->whereIn('id', $orderDetails)
            ->get();

        // Calculate the average rate, rating total, and hire count for each book
        $books->each(function ($book) {
            $orderDetails = $book->order_details
                ->where('status_cmt', 'active')
                ->where('status_od', 'completed');

            $averageRate = $orderDetails->avg('rate');
            $averageRateRounded = round($averageRate, 1);

            $book->average_rate = $averageRateRounded;
            $book->rating_total = $orderDetails->count();
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

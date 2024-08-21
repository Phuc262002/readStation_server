<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Models\Book;
use App\Models\BookDetail;
use App\Models\Category;
use App\Models\LoanOrderDetails;
use App\Models\User;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/public/home/get-feautured-category',
    tags: ['Public / Home'],
    operationId: 'getFeaturedCategory',
    summary: 'Get featured categories',
    description: 'Get featured categories',
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get all categories successfully!',
        ),
    ]
)]

#[OA\Get(
    path: '/api/v1/public/home/get-feautured-book',
    tags: ['Public / Home'],
    operationId: 'getFeaturedBook',
    summary: 'Get featured books',
    description: 'Get featured books',
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get all categories successfully!',
        ),
    ]
)]

#[OA\Get(
    path: '/api/v1/public/home/get-feautured-author',
    tags: ['Public / Home'],
    operationId: 'getFeaturedAuthor',
    summary: 'Get featured authors',
    description: 'Get featured authors and their books',
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get all categories successfully!',
        ),
    ]
)]

#[OA\Get(
    path: '/api/v1/public/home/get-recommend-book',
    tags: ['Public / Home'],
    operationId: 'bookRecommend',
    summary: 'Get recommended books',
    description: 'Get recommended books',
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get all categories successfully!',
        ),
    ]
)]

#[OA\Get(
    path: '/api/v1/public/home/get-book-lastest',
    tags: ['Public / Home'],
    operationId: 'bookLatest',
    summary: 'Get latest books',
    description: 'Get latest books',
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get all categories successfully!',
        ),
    ]
)]

#[OA\Get(
    path: '/api/v1/public/home/get-statistic',
    tags: ['Public / Home'],
    operationId: 'statisticHome',
    summary: 'Get statistic home',
    description: 'Get statistic home',
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get all categories successfully!',
        ),
    ]
)]

class HomeController extends Controller
{
    public function getFeaturedCategory(Request $request)
    {
        $categories = Category::where('is_featured', true)->where('type', "book")->get();

        return response()->json([
            'status' => true,
            'message' => 'Featured categories',
            'data' => $categories
        ]);
    }

    public function getFeaturedBook()
    {
        $books = BookDetail::with(['book.category', 'book.author', 'book.shelve', 'book.shelve.bookcase', 'book' => function ($query) {
            $query->where('status', 'active');
        }])->whereHas('book', function ($q) {
            $q->where('is_featured', true);
        })->limit(7)->get();

        return response()->json([
            'status' => true,
            'message' => 'Featured books',
            'data' => [
                'bookBanner' => $books[0],
                'books' => $books->slice(1)
            ]
        ]);
    }

    public function getFeaturedAuthor()
    {
        $authors = Author::where('is_featured', true)->get();
        $books = BookDetail::with(['book.category', 'book.author', 'book.shelve', 'book.shelve.bookcase', 'book' => function ($query) {
            $query->where('status', 'active');
        }])->whereHas('book.author', function ($q) use ($authors) {
            $q->whereIn('id', $authors->pluck('id'));
        })->limit(2)->get();

        return response()->json([
            'status' => true,
            'message' => 'Featured authors and their books',
            'data' => [
                'author' => $authors[0],
                'books' => $books
            ]
        ]);
    }

    public function bookRecommend(Request $request)
    {
        $orderDetails = LoanOrderDetails::select('book_details_id')
            ->groupBy('book_details_id')
            ->orderByRaw('COUNT(book_details_id) DESC')
            ->limit(16)
            ->get();

        $books = BookDetail::with([ 'book.category', 'book.author', 'book.shelve', 'book.shelve.bookcase', 'book' => function ($query) {
            $query->where('status', 'active');
        }])
        ->whereIn('id', $orderDetails->pluck('book_details_id'))
        ->get();

        return response()->json([
            'status' => true,
            'message' => 'Recommended books',
            'data' => $books
        ]);
    }

    public function bookLatest(Request $request)
    {
        $books = BookDetail::with(['book.category', 'book.author', 'book.shelve', 'book.shelve.bookcase', 'book' => function ($query) {
            $query->where('status', 'active');
        }])
        ->orderBy('publish_date', 'desc')
        ->limit(16)
        ->get();

        return response()->json([
            'status' => true,
            'message' => 'Latest books',
            'data' => $books
        ]);
    }

    public function statisticHome(Request $request)
    {
        $totalBooks = Book::where('status', 'active')->count();
        $totalAuthors = Author::where('status', 'active')->count();
        $totalBookOrders = LoanOrderDetails::select('book_details_id')
        ->groupBy('book_details_id')
        ->orderByRaw('COUNT(book_details_id) DESC')
        ->count();
        $totalUsers = User::count();


        return response()->json([
            'status' => true,
            'message' => 'Statistic home',
            'data' => [
                'total_books' => $totalBooks,
                'total_authors' => $totalAuthors,
                'total_book_orders' => $totalBookOrders,
                'total_users' => $totalUsers
            ]
        ]);
    }

}

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

// #[OA\Post(
//     path: 'https://n8n.warriorcode.online/webhook/46036883-4977-485d-9cb7-eb8b57cbcdf1',
//     tags: ['Public / Contact'],
//     operationId: 'sendContactEmail',
//     summary: 'Send contact email',
//     description: 'Gửi đúng theo URL https://n8n.warriorcode.online/webhook/46036883-4977-485d-9cb7-eb8b57cbcdf1',
//     requestBody: new OA\RequestBody(
//         required: true,
//         content: new OA\JsonContent(
//             required: ['name', 'email', 'content'],
//             properties: [
//                 new OA\Property(property: 'name', type: 'string'),
//                 new OA\Property(property: 'email', type: 'string', format: 'email'),
//                 new OA\Property(property: 'content', type: 'string')
//             ]
//         )
//     ),
//     responses: [
//         new OA\Response(
//             response: 200,
//             description: 'Send contact email successfully!',
//         ),
//     ]
// )]

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
        $books = Book::with(['category', 'author', 'bookDetail' => function ($query) {
            $query->where('status', 'active');
        }])
        ->where('is_featured', true)
        ->whereHas('bookDetail', function ($q) {
            $q->whereNotNull('id')
                ->whereNotNull('status')
                ->where('status', 'active');
        })
        ->limit(7)->get();

        // Format response to ensure bookDetail is an array with data
        $books->each(function ($book) {
            $book->book_detail = $book->bookDetail ? $book->bookDetail->toArray() : [];
        });

        return response()->json([
            'status' => true,
            'message' => 'Featured books',
            'data' => $books
        ]);
    }

    public function getFeaturedAuthor()
    {
        $authors = Author::with(['books' => function ($query) {
            $query->whereHas('bookDetail')->with(['category', 'bookDetail'])->limit(4);
        }])->where('is_featured', true)->get();

        return response()->json([
            'status' => true,
            'message' => 'Featured authors and their books',
            'data' => $authors
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

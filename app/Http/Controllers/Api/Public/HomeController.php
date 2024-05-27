<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Models\Book;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/home/get-feautured-author',
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
    path: '/api/v1/home/get-feautured-book',
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

#[OA\Post(
    path: 'https://n8n.warriorcode.online/webhook/46036883-4977-485d-9cb7-eb8b57cbcdf1',
    tags: ['Public / Contact'],
    operationId: 'sendContactEmail',
    summary: 'Send contact email',
    description: 'Gửi đúng theo URL https://n8n.warriorcode.online/webhook/46036883-4977-485d-9cb7-eb8b57cbcdf1',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['name', 'email', 'content'],
            properties: [
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'email', type: 'string', format: 'email'),
                new OA\Property(property: 'content', type: 'string')
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Send contact email successfully!',
        ),
    ]
)]

class HomeController extends Controller
{
    public function getFeaturedAuthor()
    {
        $authors = Author::with(['books' => function ($query) {
            $query->whereHas('bookDetail')->with(['category', 'bookDetail']);
        }])->where('is_featured', true)->get();

        return response()->json([
            'status' => true,
            'message' => 'Featured authors and their books',
            'data' => $authors
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
        ->get();

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
}

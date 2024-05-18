<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Models\Book;
use Illuminate\Http\Request;

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
            $query->where('status', '!=', 'deleted');
        }])
        ->where('is_featured', true)
        ->whereHas('bookDetail', function ($q) {
            $q->whereNotNull('id')
                ->whereNotNull('status')
                ->where('status', '=', 'active');
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

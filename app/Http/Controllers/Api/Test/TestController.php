<?php

namespace App\Http\Controllers\Api\Test;

use App\Http\Controllers\Controller;
use App\Models\BookDetail;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function test()
    {
        $bookDetails = BookDetail::with('book')->where('stock', 0)->get();

        $bookArray = [];

        foreach ($bookDetails as $index => $bookDetail) {
            $bookArray[] = [
                'index' => $index,
                'book_name' => $bookDetail->book->title,
                'stock' => $bookDetail->stock,
            ];
        }

        return response()->json($bookArray);
    }

    public function test2()
    {
        $bookDetails = BookDetail::with('book')->orderBy('stock', 'desc')->get();

        $bookArray = [];

        foreach ($bookDetails as $index => $bookDetail) {
            $bookArray[] = [
                'index' => $index,
                'id' => $bookDetail->id,
                'book_name' => $bookDetail->book->title,
                'stock' => $bookDetail->stock,
            ];
        }

        return response()->json($bookArray);
    }
}

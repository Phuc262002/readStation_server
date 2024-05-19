<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BookDetailController extends Controller
{
    public function checkBookDetail() {
        // Find books without a bookDetail or with an inactive bookDetail
        $booksWithoutDetail = Book::doesntHave('bookDetail')
            ->orWhereHas('bookDetail', function ($q) {
                $q->where('status', '!=', 'active');
            })
            ->get();

        // Update the status of these books to 'needUpdateDetail'
        $booksWithoutDetail->each(function ($book) {
            if ($book->status == 'active') {
                $book->update(['status' => 'needUpdateDetail']);
            }
        });

        // Find books having an active bookDetail

        $booksWithDetail = Book::whereHas('bookDetail', function ($q) {
            $q->where('status', 'active');
        })->get();

        // Update the status of these books to 'active'
        $booksWithDetail->each(function ($book) {
            if ($book->status == 'needUpdateDetail') {
                $book->update(['status' => 'active']);
            }
        });
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'book_id' => "required|integer",
            'poster' => "required|string",
            'images' => "required|array",
            'book_version' => "required|string",
            'price' => "required|integer",
            'hire_percent' => "required|integer",
            'stock' => "required|integer",
            'publish_date' => "required|date",
            'publishing_company_id' => "required|integer",
            'issuing_company' => "required|string",
            'cardboard' => "required|string|in:hard,soft",
            'total_page' => "required|integer",
            'translator' => "nullable|string",
            'language' => "required|string",
            'book_size' => "nullable|string",
        ]);

        $customMessages = [
            'book_id.required' => 'Trường book_id là bắt buộc.',
            'poster.required' => 'Trường poster là bắt buộc.',
            'images.required' => 'Trường images là bắt buộc.',
            'book_version.required' => 'Trường book_version là bắt buộc.',
            'price.required' => 'Trường price là bắt buộc.',
            'hire_percent.required' => 'Trường hire_percent là bắt buộc.',
            'stock.required' => 'Trường stock là bắt buộc.',
            'publish_date.required' => 'Trường publish_date là bắt buộc.',
            'publishing_company_id.required' => 'Trường publishing_company_id là bắt buộc.',
            'issuing_company.required' => 'Trường issuing_company là bắt buộc.',
            'cardboard.required' => 'Trường cardboard là bắt buộc.',
            'total_page.required' => 'Trường total_page là bắt buộc.',
            'language.required' => 'Trường language là bắt buộc.',
            'images.array' => 'Trường images phải là một mảng.',
            'price.integer' => 'Trường price phải là một số.',
            'hire_percent.integer' => 'Trường hire_percent phải là một số.',
            'stock.integer' => 'Trường stock phải là một số.',
            'publishing_company_id.integer' => 'Trường publishing_company_id phải là một số.',
            'cardboard.in' => 'Trường cardboard phải là hard hoặc soft.',
            'total_page.integer' => 'Trường total_page phải là một số.'
        ];

        $validator->setCustomMessages($customMessages);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }
        try {
            $bookdetail = BookDetail::create(array_merge(
                $validator->validated(),
            ));
            $this->checkBookDetail();

            return response()->json([
                "status" => true,
                "message" => "Create book successfully!",
                "data" => $bookdetail
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => "Create book failed!",
                "errors" => $th->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, BookDetail $bookDetail)
    {
        $this->checkBookDetail();
        $id = $request->route('id');

        $validator = Validator::make(['id' => $id], [
            'id' => 'required|integer|min:1'
        ]);

        $customMessages = [
            'id.required' => 'Trường id là bắt buộc.',
            'id.integer' => 'Id phải là một số nguyên.',
            'id.min' => 'Id phải lớn hơn hoặc bằng 1.'
        ];

        $validator->setCustomMessages($customMessages);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }

        $bookdetail = BookDetail::with('book')->find($id);
        if (!$bookdetail) {
            return response()->json([
                "status" => false,
                "message" => "Book detail not found!"
            ], 404);
        }

        return response()->json([
            "status" => true,
            "message" => "Get book detail successfully!",
            "data" => $bookdetail
        ], 200);
    }

    public function update(Request $request, BookDetail $bookDetail)
    {
        $id = $request->route('id');

        $validator = Validator::make(array_merge(['id' => $id], $request->all()), [
            'id' => 'required|integer|min:1',
            'book_id' => "required|integer",
            'poster' => "required|string",
            'images' => "required|array",
            'book_version' => "required|string",
            'price' => "required|integer",
            'hire_percent' => "required|integer",
            'stock' => "required|integer",
            'publish_date' => "required|date",
            'publishing_company_id' => "required|integer",
            'issuing_company' => "required|string",
            'cardboard' => "required|string|in:hard,soft",
            'total_page' => "required|integer",
            'translator' => "nullable|string",
            'language' => "required|string",
            'book_size' => "nullable|string",
        ]);

        $customMessages = [
            'id.required' => 'Trường id là bắt buộc.',
            'id.integer' => 'Id phải là một số nguyên.',
            'id.min' => 'Id phải lớn hơn hoặc bằng 1.',
            'book_id.required' => 'Trường book_id là bắt buộc.',
            'poster.required' => 'Trường poster là bắt buộc.',
            'images.required' => 'Trường images là bắt buộc.',
            'book_version.required' => 'Trường book_version là bắt buộc.',
            'price.required' => 'Trường price là bắt buộc.',
            'hire_percent.required' => 'Trường hire_percent là bắt buộc.',
            'stock.required' => 'Trường stock là bắt buộc.',
            'publish_date.required' => 'Trường publish_date là bắt buộc.',
            'publishing_company_id.required' => 'Trường publishing_company_id là bắt buộc.',
            'issuing_company.required' => 'Trường issuing_company là bắt buộc.',
            'cardboard.required' => 'Trường cardboard là bắt buộc.',
            'total_page.required' => 'Trường total_page là bắt buộc.',
            'language.required' => 'Trường language là bắt buộc.',
            'images.array' => 'Trường images phải là một mảng.',
            'price.integer' => 'Trường price phải là một số.',
            'hire_percent.integer' => 'Trường hire_percent phải là một số.',
            'stock.integer' => 'Trường stock phải là một số.',
            'publishing_company_id.integer' => 'Trường publishing_company_id phải là một số.',
            'cardboard.in' => 'Trường cardboard phải là hard hoặc soft.',
            'total_page.integer' => 'Trường total_page phải là một số.'
        ];

        $validator->setCustomMessages($customMessages);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }

        $bookdetail = BookDetail::find($id);

        if (!$bookdetail) {
            return response()->json([
                "status" => false,
                "message" => "Author not found!"
            ], 404);
        }

        try {
            $bookdetail->update($validator->validated());
            $this->checkBookDetail();
            return response()->json([
                "status" => true,
                "message" => "Update book detail successfully!",
                "data" => $bookdetail
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => "Update book detail failed!"
            ], 500);
        }
    }

    public function destroy(Request $request, BookDetail $bookDetail)
    {
        $id = $request->route('id');

        $validator = Validator::make(['id' => $id], [
            'id' => 'required|integer|min:1'
        ]);

        $customMessages = [
            'id.required' => 'Trường id là bắt buộc.',
            'id.integer' => 'Id phải là một số nguyên.',
            'id.min' => 'Id phải lớn hơn hoặc bằng 1.'
        ];

        $validator->setCustomMessages($customMessages);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }

        $bookdetail = BookDetail::find($id);

        if (!$bookdetail) {
            return response()->json([
                "status" => false,
                "message" => "Book detail not found!"
            ], 404);
        }

        try {
            $bookdetail->delete();
            $this->checkBookDetail();
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => "Delete bookdetail failed!"
            ], 500);
        }

        return response()->json([
            "status" => true,
            "message" => "Delete bookdetail successfully!",
        ], 200);
    }
}

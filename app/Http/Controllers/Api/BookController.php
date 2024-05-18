<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $validator = validator($request->all(), [
            'page' => 'integer|min:1',
            'pageSize' => 'integer|min:1',
            'search' => 'string',
            'category_id' => 'integer',
            'author_id' => 'integer',
            'status' => 'string|in:active,inactive,deleted',
        ]);

        $customMessages = [
            'page.integer' => 'Trang phải là số nguyên.',
            'page.min' => 'Trang phải lớn hơn hoặc bằng 1.',
            'pageSize.integer' => 'Kích thước trang phải là số nguyên.',
            'pageSize.min' => 'Kích thước trang phải lớn hơn hoặc bằng 1.',
            'category_id.integer' => 'Category_id phải là một số nguyên.',
            'author_id.integer' => 'Author_id phải là một số nguyên.',
            'status.in' => 'Status phải là active, inactive hoặc deleted.'
        ];
        
        $validator->setCustomMessages($customMessages);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }

        // Lấy giá trị page và pageSize từ query parameters
        $page = $request->input('page', 1); 
        $pageSize = $request->input('pageSize', 10); 
        $search = $request->input('search');
        $category_id = $request->input('category_id');
        $author_id = $request->input('author_id');
        $status = $request->input('status');

        // Tạo query ban đầu
        $query = Book::query()->with('category', 'author');
        $totalItems = $query->count();
        $query = $query->filter($category_id, $status, $author_id);

        // Áp dụng bộ lọc tìm kiếm nếu có tham số tìm kiếm
        $query = $query->search($search);

        // Thực hiện phân trang
        $books = $query->paginate($pageSize, ['*'], 'page', $page);

        return response()->json([
            "status" => true,
            "message" => "Get all books successfully!",
            "data" => [
                "books" => $books->items(),
                "page" => $books->currentPage(),
                "pageSize" => $books->perPage(),
                "lastPage" => $books->lastPage(),
                "totalResults" => $books->total(),
                "total" => $totalItems
            ],
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = validator($request->all(), [
            'sku' => "required|string",
            'author_id' => "required|string",
            'title' => "required|string",
            'original_title' => "required|string",
            'description_summary' => "required|string",
            'description' => "required|string",
            'category_id' => "required|string",
            'shelve_id' => "nullable|number",
        ]);

        $customMessages = [
            'sku.required' => 'Trường sku là bắt buộc.',
            'sku.string' => 'Sku phải là một chuỗi.',
            'author_id.required' => 'Trường author_id là bắt buộc.',
            'author_id.number' => 'Author_id phải là một số.',
            'title.required' => 'Trường title là bắt buộc.',
            'title.string' => 'Title phải là một chuỗi.',
            'original_title.required' => 'Trường original_title là bắt buộc.',
            'original_title.string' => 'Original_title phải là một chuỗi.',
            'description_summary.required' => 'Trường description_summary là bắt buộc.',
            'description_summary.string' => 'Description_summary phải là một chuỗi.',
            'description.required' => 'Trường description là bắt buộc.',
            'description.string' => 'Description phải là một chuỗi.',
            'category_id.required' => 'Trường category_id là bắt buộc.',
            'category_id.number' => 'Category_id phải là một số.',
            'shelve_id.number' => 'Shelve_id phải là một số.',
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
            $book = Book::create(array_merge(
                $validator->validated(),
            ));

            return response()->json([
                "status" => true,
                "message" => "Create book successfully!",
                "data" => $book
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => "Create book failed!",
                "errors" => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Book $book)
    {
        $id = $request->route('id');

        $validator = validator(['id' => $id], [
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

        $book = Book::with('category', 'author')->find($id);

        if (!$book) {
            return response()->json([
                "status" => false,
                "message" => "Book not found!"
            ], 404);
        }

        return response()->json([
            "status" => true,
            "message" => "Get book successfully!",
            "data" => $book
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Book $book)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Book $book)
    {
        $id = $request->route('id');

        $validator = validator($request->all(), [
            'sku' => "required|string",
            'author_id' => "required|string",
            'title' => "required|string",
            'original_title' => "required|string",
            'description_summary' => "required|string",
            'description' => "required|string",
            'category_id' => "required|string",
            'shelve_id' => "number",
            "status" => "required|string|in:active,inactive,deleted",
        ]);

        $customMessages = [
            'sku.required' => 'Trường sku là bắt buộc.',
            'sku.string' => 'Sku phải là một chuỗi.',
            'author_id.required' => 'Trường author_id là bắt buộc.',
            'author_id.number' => 'Author_id phải là một số.',
            'title.required' => 'Trường title là bắt buộc.',
            'title.string' => 'Title phải là một chuỗi.',
            'original_title.required' => 'Trường original_title là bắt buộc.',
            'original_title.string' => 'Original_title phải là một chuỗi.',
            'description_summary.required' => 'Trường description_summary là bắt buộc.',
            'description_summary.string' => 'Description_summary phải là một chuỗi.',
            'description.required' => 'Trường description là bắt buộc.',
            'description.string' => 'Description phải là một chuỗi.',
            'category_id.required' => 'Trường category_id là bắt buộc.',
            'category_id.number' => 'Category_id phải là một số.',
            'shelve_id.number' => 'Shelve_id phải là một số.',
            'status.in' => 'Status phải là active, inactive hoặc deleted.',
            'status.required' => 'Trường status là bắt buộc.'
        ];

        $validator->setCustomMessages($customMessages);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }

        $book = Book::find($id);

        if (!$book) {
            return response()->json([
                "status" => false,
                "message" => "Book not found!"
            ], 404);
        }

        try {
            $book->update($validator->validated());

            return response()->json([
                "status" => true,
                "message" => "Update book successfully!",
                "data" => $book
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => "Update book failed!"
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Book $book)
    {
        $id = $request->route('id');

        $validator = validator(['id' => $id], [
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

        $book = Book::find($id);

        if (!$book) {
            return response()->json([
                "status" => false,
                "message" => "Book not found!"
            ], 404);
        }

        try {
            $book->delete();
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => "Delete book failed!"
            ], 500);
        }

        return response()->json([
            "status" => true,
            "message" => "Delete book successfully!",
        ], 200);
    }
}

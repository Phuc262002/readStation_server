<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/books',
    tags: ['Book'],
    operationId: 'getAllBooksPublic',
    summary: 'Get all books public',
    description: 'Get all books',
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
            name: 'search',
            in: 'query',
            required: false,
            description: 'Từ khóa tìm kiếm',
            schema: new OA\Schema(type: 'string')
        ),
        new OA\Parameter(
            name: 'category_id',
            in: 'query',
            required: false,
            description: 'Id của category',
            schema: new OA\Schema(type: 'integer')
        ),
        new OA\Parameter(
            name: 'author_id',
            in: 'query',
            required: false,
            description: 'Id của author',
            schema: new OA\Schema(type: 'integer')
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get all books successfully!',
        ),
        new OA\Response(
            response: 400,
            description: 'Validation error',
        ),
    ],
)]

#[OA\Get(
    path: '/api/v1/books/get-one/{book}',
    tags: ['Book'],
    operationId: 'getOneBook',
    summary: 'Get one book',
    description: 'Get one book',
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            description: 'Id hoặc slug của sách',
            schema: new OA\Schema(type: 'string')
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get book successfully!',
        ),
        new OA\Response(
            response: 404,
            description: 'Book not found!',
        ),
    ],
)]

#[OA\Get(
    path: '/api/v1/books/admin/get-all',
    tags: ['Book'],
    operationId: 'getAllBooks',
    summary: 'Get all books admin',
    description: 'Get all books',
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
            name: 'search',
            in: 'query',
            required: false,
            description: 'Từ khóa tìm kiếm',
            schema: new OA\Schema(type: 'string')
        ),
        new OA\Parameter(
            name: 'category_id',
            in: 'query',
            required: false,
            description: 'Id của category',
            schema: new OA\Schema(type: 'integer')
        ),
        new OA\Parameter(
            name: 'author_id',
            in: 'query',
            required: false,
            description: 'Id của author',
            schema: new OA\Schema(type: 'integer')
        ),
        new OA\Parameter(
            name: 'status',
            in: 'query',
            required: false,
            description: 'Trạng thái sách',
            schema: new OA\Schema(type: 'string', enum: ['active', 'inactive', 'deleted'])
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get all books successfully!',
        ),
        new OA\Response(
            response: 400,
            description: 'Validation error',
        ),
    ],
)]

#[OA\Post(
    path: '/api/v1/books/create',
    tags: ['Book'],
    operationId: 'createBook',
    summary: 'Create book',
    description: 'Create book',
    security: [
        ['bearerAuth' => []]
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['sku', 'author_id', 'title', 'original_title', 'description_summary', 'description', 'category_id'],
            properties: [
                new OA\Property(property: 'sku', type: 'string'),
                new OA\Property(property: 'author_id', type: 'string'),
                new OA\Property(property: 'title', type: 'string'),
                new OA\Property(property: 'original_title', type: 'string'),
                new OA\Property(property: 'description_summary', type: 'string'),
                new OA\Property(property: 'description', type: 'text'),
                new OA\Property(property: 'is_featured', type: 'boolean'),
                new OA\Property(property: 'category_id', type: 'string'),
                new OA\Property(property: 'shelve_id', type: 'string'),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Create book successfully!',
        ),
        new OA\Response(
            response: 400,
            description: 'Validation error',
        ),
        new OA\Response(
            response: 500,
            description: 'Create book failed!',
        ),
    ],
)]

#[OA\Put(
    path: '/api/v1/books/update/{id}',
    tags: ['Book'],
    operationId: 'updateBook',
    summary: 'Update book',
    description: 'Update book',
    security: [
        ['bearerAuth' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            description: 'Id của sách',
            schema: new OA\Schema(type: 'integer')
        ),
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['sku', 'author_id', 'title', 'original_title', 'description_summary', 'description', 'category_id', 'shelve_id', 'is_featured', 'status'],
            properties: [
                new OA\Property(property: 'sku', type: 'string'),
                new OA\Property(property: 'author_id', type: 'string'),
                new OA\Property(property: 'title', type: 'string'),
                new OA\Property(property: 'original_title', type: 'string'),
                new OA\Property(property: 'description_summary', type: 'string'),
                new OA\Property(property: 'description', type: 'text'),
                new OA\Property(property: 'category_id', type: 'string'),
                new OA\Property(property: 'shelve_id', type: 'string'),
                new OA\Property(property: 'is_featured', type: 'boolean'),
                new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive', 'deleted']),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Update book successfully!',
        ),
        new OA\Response(
            response: 400,
            description: 'Validation error',
        ),
        new OA\Response(
            response: 500,
            description: 'Update book failed!',
        ),
    ],
)]

#[OA\Delete(
    path: '/api/v1/books/delete/{id}',
    tags: ['Book'],
    operationId: 'deleteBook',
    summary: 'Delete book',
    description: 'Delete book',
    security: [
        ['bearerAuth' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            description: 'Id của sách',
            schema: new OA\Schema(type: 'integer')
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Delete book successfully!',
        ),
        new OA\Response(
            response: 400,
            description: 'Validation error',
        ),
        new OA\Response(
            response: 404,
            description: 'Book not found!',
        ),
        new OA\Response(
            response: 500,
            description: 'Delete book failed!',
        ),
    ],
)]


class BookController extends Controller
{
    public function checkStoreValidator($request)
    {
        $validator = Validator::make($request->all(), [
            'sku' => "required|string",
            'author_id' => "required|string",
            'title' => "required|string",
            'original_title' => "required|string",
            'description_summary' => "required|string",
            'description' => "required|string",
            'is_featured' => 'nullable|boolean',
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
            'is_featured.boolean' => 'Is featured phải là một boolean.',
            'category_id.number' => 'Category_id phải là một số.',
            'shelve_id.number' => 'Shelve_id phải là một số.',
        ];

        $validator->setCustomMessages($customMessages);

        return $validator;
    }

    public function checkUpdateValidator($request, $id)
    {
        $validator = Validator::make(array_merge(['id' => $id], $request->all()), [
            'id' => 'required|integer|min:1',
            'sku' => "required|string",
            'author_id' => "required|string",
            'title' => "required|string",
            'original_title' => "required|string",
            'description_summary' => "required|string",
            'description' => "required|string",
            'category_id' => "required|string",
            'shelve_id' => "string",
            'is_featured' => 'nullable|boolean',
            "status" => "string|in:active,inactive,deleted",
        ]);

        $customMessages = [
            'id.required' => 'Trường id là bắt buộc.',
            'id.integer' => 'Id phải là một số nguyên.',
            'id.min' => 'Id phải lớn hơn hoặc bằng 1.',
            'sku.required' => 'Trường sku là bắt buộc.',
            'sku.string' => 'Sku phải là một chuỗi.',
            'author_id.required' => 'Trường author_id là bắt buộc.',
            'author_id.string' => 'Author_id phải là một chuỗi.',
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
            'is_featured.boolean' => 'Is featured phải là một boolean.',
            'status.required' => 'Trường status là bắt buộc.'
        ];

        $validator->setCustomMessages($customMessages);

        return $validator;
    }

    public function checkBookDetail()
    {
        // Find books without a bookDetail or with an inactive bookDetail
        $booksWithoutDetail = Book::doesntHave('bookDetail')
            ->orWhereHas('bookDetail', function ($q) {
                $q->where('status', '!=', 'active');
            })
            ->get();

        // Update the status of these books to 'needUpdateDetail'
        $booksWithoutDetail->each(function ($book) {
            if ($book->status != 'needUpdateDetail') {
                $book->update(['status' => 'needUpdateDetail']);
            }
        });
    }

    public function index(Request $request)
    {
        $this->checkBookDetail();

        $validator = Validator::make($request->all(), [
            'page' => 'integer|min:1',
            'pageSize' => 'integer|min:1',
            'search' => 'string',
            'category_id' => 'integer',
            'author_id' => 'integer',
        ]);

        $customMessages = [
            'page.integer' => 'Trang phải là số nguyên.',
            'page.min' => 'Trang phải lớn hơn hoặc bằng 1.',
            'pageSize.integer' => 'Kích thước trang phải là số nguyên.',
            'pageSize.min' => 'Kích thước trang phải lớn hơn hoặc bằng 1.',
            'category_id.integer' => 'Category_id phải là một số nguyên.',
            'author_id.integer' => 'Author_id phải là một số nguyên.',
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

        // Tạo query ban đầu
        $query = Book::query()
            ->with(['category', 'author', 'bookDetail' => function ($query) {
                $query->where('status', '!=', 'deleted');
            }])
            ->whereHas('bookDetail', function ($q) {
                $q->whereNotNull('id')
                    ->whereNotNull('status')
                    ->where('status', '=', 'active');
            });

        $totalItems = $query->count();
        $query = $query->filter($category_id, null, $author_id);

        // Áp dụng bộ lọc tìm kiếm nếu có tham số tìm kiếm
        $query = $query->search($search);

        // Thực hiện phân trang
        $books = $query->orderBy('created_at', 'desc')->paginate($pageSize, ['*'], 'page', $page);

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

    public function getAllBook(Request $request)
    {
        $this->checkBookDetail();

        $validator = Validator::make($request->all(), [
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
            'status.in' => 'Status phải là active, inactive hoặc deleted.',
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
        $query = Book::query()
            ->with(['category', 'author', 'bookDetail' => function ($query) {
                $query->where('status', '!=', 'deleted');
            }])
            ->whereHas('bookDetail', function ($q) {
                $q->whereNotNull('id')
                    ->whereNotNull('status')
                    ->where('status', '=', 'active');
            });

        $totalItems = $query->count();
        $query = $query->filter($category_id, $status, $author_id);

        // Áp dụng bộ lọc tìm kiếm nếu có tham số tìm kiếm
        $query = $query->search($search);

        // Thực hiện phân trang
        $books = $query->orderBy('created_at', 'desc')->paginate($pageSize, ['*'], 'page', $page);

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

    public function store(Request $request)
    {
        $this->checkBookDetail();

        $validator = $this->checkStoreValidator($request);

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

    public function show(Request $request, Book $book)
    {
        $this->checkBookDetail();
        $id = $request->route('book');

        $validator = Validator::make(['book' => $id], [
            'book' => 'required'
        ]);

        $customMessages = [
            'id.required' => 'Phải có id hoặc slug để lấy thông tin sách.',
        ];

        $validator->setCustomMessages($customMessages);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }

        if (is_numeric($id)) {
            $book = Book::with('category', 'author', 'bookDetail')->find($id);
        } else {
            $book = Book::with('category', 'author', 'bookDetail')->where('slug', $id)->first();
        }

        if (!$book) {
            return response()->json([
                "status" => false,
                "message" => "Book not found!"
            ], 404);
        } elseif ($book->status != 'active') {
            return response()->json([
                "status" => false,
                "message" => "Book is not active!"
            ], 404);
        }

        return response()->json([
            "status" => true,
            "message" => "Get book successfully!",
            "data" => $book
        ], 200);
    }

    public function update(Request $request, Book $book)
    {
        $id = $request->route('id');
        $validator = $this->checkUpdateValidator($request, $id);
        

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
            $this->checkBookDetail();
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

    public function destroy(Request $request, Book $book)
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

        $book = Book::find($id);

        if (!$book) {
            return response()->json([
                "status" => false,
                "message" => "Book not found!"
            ], 404);
        } elseif ($book->status == 'deleted') {
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

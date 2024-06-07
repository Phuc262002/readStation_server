<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/books/admin/get-all',
    tags: ['Admin / Book'],
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

#[OA\Get(
    path: '/api/v1/books/admin/get-one/{id}',
    tags: ['Admin / Book'],
    operationId: 'showBook',
    summary: 'Show book',
    description: 'Show book',
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
            description: 'Get book detail successfully!',
        ),
        new OA\Response(
            response: 400,
            description: 'Validation error',
        ),
        new OA\Response(
            response: 404,
            description: 'Book detail not found!',
        ),
    ],
)]

#[OA\Post(
    path: '/api/v1/books/create',
    tags: ['Admin / Book'],
    operationId: 'createBook',
    summary: 'Create book',
    description: 'Create book',
    security: [
        ['bearerAuth' => []]
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['sku_generated', 'author_id', 'title', 'original_title', 'description_summary', 'description', 'category_id'],
            properties: [
                new OA\Property(property: 'sku_generated', type: 'string'),
                new OA\Property(property: 'author_id', type: 'string'),
                new OA\Property(property: 'title', type: 'string'),
                new OA\Property(property: 'original_title', type: 'string'),
                new OA\Property(property: 'description_summary', type: 'string'),
                new OA\Property(property: 'description', type: 'string'),
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


#[OA\Post(
    path: '/api/v1/books/create-full',
    tags: ['Admin / Book'],
    operationId: 'createFullBook',
    summary: 'Create full book',
    description: 'Có thể tạo sách với nhiều chi tiết sách khác nhau theo dạng mảng book_detail',
    security: [
        ['bearerAuth' => []]
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['author_id', 'title', 'original_title', 'description_summary', 'description', 'category_id', 'book_detail'],
            properties: [
                new OA\Property(property: 'author_id', type: 'string'),
                new OA\Property(property: 'title', type: 'string'),
                new OA\Property(property: 'original_title', type: 'string'),
                new OA\Property(property: 'description_summary', type: 'string'),
                new OA\Property(property: 'description', type: 'string'),
                new OA\Property(property: 'is_featured', type: 'boolean'),
                new OA\Property(property: 'category_id', type: 'string'),
                new OA\Property(property: 'shelve_id', type: 'string'),
                new OA\Property(
                    property: 'book_detail',
                    type: 'array',
                    items: new OA\Items(
                        type: 'object',
                        required: [
                            'sku_origin', 'poster', 'images', 'book_version', 'price', 'hire_percent', 'stock',
                            'publish_date', 'publishing_company_id', 'issuing_company', 'cardboard',
                            'total_page', 'language'
                        ],
                        properties: [
                            new OA\Property(property: 'sku_origin', type: 'string'),
                            new OA\Property(property: 'poster', type: 'string'),
                            new OA\Property(property: 'images', type: 'array', items: new OA\Items(type: 'string')), // Specify the type of items in the array
                            new OA\Property(property: 'book_version', type: 'string'),
                            new OA\Property(property: 'price', type: 'string'),
                            new OA\Property(property: 'hire_percent', type: 'string'),
                            new OA\Property(property: 'stock', type: 'string'),
                            new OA\Property(property: 'publish_date', type: 'string', format: 'date'), // Use 'string' type with 'date' format for dates
                            new OA\Property(property: 'publishing_company_id', type: 'string'),
                            new OA\Property(property: 'issuing_company', type: 'string'),
                            new OA\Property(property: 'cardboard', type: 'string', enum: ['hard', 'soft']),
                            new OA\Property(property: 'total_page', type: 'string'),
                            new OA\Property(property: 'translator', type: 'string'),
                            new OA\Property(property: 'language', type: 'string'),
                            new OA\Property(property: 'book_size', type: 'string'),
                        ]
                    )
                )
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
    tags: ['Admin / Book'],
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
            required: ['sku_generated', 'author_id', 'title', 'original_title', 'description_summary', 'description', 'category_id', 'shelve_id', 'is_featured', 'status'],
            properties: [
                new OA\Property(property: 'sku_generated', type: 'string'),
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
    tags: ['Admin / Book'],
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
    public function __construct()
    {
        $this->checkBookDetail();
    }

    public function checkStoreValidator($request)
    {
        $validator = Validator::make($request->all(), [
            'author_id' => "required|string",
            'title' => "required|string",
            'original_title' => "required|string",
            'description_summary' => "required|string",
            'description' => "required|string",
            'is_featured' => 'nullable|boolean',
            'category_id' => "required|string",
            'shelve_id' => "nullable",
        ], [
            'author_id.required' => 'Trường author_id là bắt buộc.',
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
        ]);

        return $validator;
    }

    public function checkUpdateValidator($request, $id)
    {
        $validator = Validator::make(array_merge(['id' => $id], $request->all()), [
            'id' => 'required|integer|min:1|exists:books,id',
            'author_id' => "string",
            'title' => "string",
            'original_title' => "string",
            'description_summary' => "string",
            'description' => "string",
            'category_id' => "string",
            'shelve_id' => "nullable",
            'is_featured' => 'nullable|boolean',
            "status" => "string|in:active,inactive,deleted",
        ], [
            'id.required' => 'Trường id là bắt buộc.',
            'id.integer' => 'Id phải là một số nguyên.',
            'id.min' => 'Id phải lớn hơn hoặc bằng 1.',
            'id.exists' => 'Sách không tồn tại.',
            'author_id.string' => 'Author_id phải là một chuỗi.',
            'title.string' => 'Title phải là một chuỗi.',
            'original_title.string' => 'Original_title phải là một chuỗi.',
            'description_summary.string' => 'Description_summary phải là một chuỗi.',
            'description.string' => 'Description phải là một chuỗi.',
            'status.in' => 'Status phải là active, inactive hoặc deleted.',
            'is_featured.boolean' => 'Is featured phải là một boolean.',
        ]);
        return $validator;
    }

    public function checkBookDetail()
    {
        // Find books without a bookDetail or with an inactive bookDetail
        $booksWithoutDetail = Book::doesntHave('bookDetail')->get();

        // Update the status of these books to 'needUpdateDetail'
        $booksWithoutDetail->each(function ($book) {
            if ($book->status != 'needUpdateDetail') {
                $book->update(['status' => 'needUpdateDetail']);
            }
        });
    }

    public function getAllBook(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'page' => 'integer|min:1',
            'pageSize' => 'integer|min:1',
            'search' => 'string',
            'category_id' => 'integer',
            'author_id' => 'integer',
            'status' => 'string|in:active,inactive,deleted,needUpdateDetail',
        ], [
            'page.integer' => 'Trang phải là số nguyên.',
            'page.min' => 'Trang phải lớn hơn hoặc bằng 1.',
            'pageSize.integer' => 'Kích thước trang phải là số nguyên.',
            'pageSize.min' => 'Kích thước trang phải lớn hơn hoặc bằng 1.',
            'category_id.integer' => 'Category_id phải là một số nguyên.',
            'author_id.integer' => 'Author_id phải là một số nguyên.',
            'status.in' => 'Status phải là active, inactive, needUpdateDetail hoặc deleted.',
        ]);

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
            ->with(['category', 'author', 'shelve', 'shelve.bookcase', 'shelve.category', 'bookDetail']);

        $totalItems = $query->count();
        $query = $query->filter($category_id, $status, $author_id, true);

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
                "totalPages" => $books->lastPage(),
                "totalResults" => $books->total(),
                "total" => $totalItems
            ],
        ], 200);
    }

    public function show($id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|integer|min:1|exists:books,id'
        ]);

        $customMessages = [
            'id.required' => 'Trường id là bắt buộc.',
            'id.integer' => 'Id phải là một số nguyên.',
            'id.min' => 'Id phải lớn hơn hoặc bằng 1.',
            'id.exists' => 'Id không tồn tại.'
        ];

        $validator->setCustomMessages($customMessages);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }

        $bookdetail = Book::with('bookDetail', 'category', 'author', 'shelve', 'shelve.bookcase', 'shelve.category')->find($id);
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

    public function store(Request $request)
    {
        $validator = $this->checkStoreValidator($request);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }

        try {


            $count_feutured = Book::where('is_featured', true)->count();
            if ($count_feutured <= 7) {
                $book = Book::create(array_merge(
                    $validator->validated(),
                    ['is_featured' => true]
                ));
            } else {
                $book = Book::create(array_merge(
                    $validator->validated(),
                ));
            }

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

    public function update(Request $request, $id)
    {
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

            if ($book->status == 'inactive') {
                $book->bookDetail()->update(['status' => 'inactive']);
            }

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

    public function destroy($id)
    {
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

    public function createFullBook(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sku_generated' => "nullable|string",
            'author_id' => "required",
            'title' => "required|string",
            'original_title' => "required|string",
            'description_summary' => "required|string",
            'description' => "required|string",
            'is_featured' => 'nullable|boolean',
            'category_id' => "required",
            'shelve_id' => "nullable",
            'book_detail' => "required|array",
            'book_detail.*.sku_origin' => "required",
            'book_detail.*.poster' => "required",
            'book_detail.*.images' => "required|array",
            'book_detail.*.book_version' => "required",
            'book_detail.*.price' => "required",
            'book_detail.*.hire_percent' => "required",
            'book_detail.*.stock' => "required",
            'book_detail.*.publish_date' => "required|date",
            'book_detail.*.publishing_company_id' => "required",
            'book_detail.*.issuing_company' => "required",
            'book_detail.*.cardboard' => "required|in:hard,soft",
            'book_detail.*.total_page' => "required",
            'book_detail.*.translator' => "nullable",
            'book_detail.*.language' => "required",
            'book_detail.*.book_size' => "nullable",

        ], [
            'sku_generated.required' => 'Trường sku_generated là bắt buộc.',
            'sku_generated.string' => 'Sku_generated phải là một chuỗi.',
            'author_id.required' => 'Trường author_id là bắt buộc.',
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
            'book_detail.required' => 'Trường book_detail là bắt buộc.',
            'book_detail.array' => 'Book_detail phải là một mảng.',
            'book_detail.*.sku_origin.required' => 'Trường sku_origin là bắt buộc.',
            'book_detail.*.poster.required' => 'Trường poster là bắt buộc.',
            'book_detail.*.images.required' => 'Trường images là bắt buộc.',
            'book_detail.*.book_version.required' => 'Trường book_version là bắt buộc.',
            'book_detail.*.price.required' => 'Trường price là bắt buộc.',
            'book_detail.*.hire_percent.required' => 'Trường hire_percent là bắt buộc.',
            'book_detail.*.stock.required' => 'Trường stock là bắt buộc.',
            'book_detail.*.publish_date.required' => 'Trường publish_date là bắt buộc.',
            'book_detail.*.publishing_company_id.required' => 'Trường publishing_company_id là bắt buộc.',
            'book_detail.*.issuing_company.required' => 'Trường issuing_company là bắt buộc.',
            'book_detail.*.cardboard.required' => 'Trường cardboard là bắt buộc.',
            'book_detail.*.total_page.required' => 'Trường total_page là bắt buộc.',
            'book_detail.*.language.required' => 'Trường language là bắt buộc.',
            'book_detail.*.images.array' => 'Trường images phải là một mảng.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }

        try {
            $validatedData = $validator->validated();

            $count_feutured = Book::where('is_featured', true)->count();
            if ($count_feutured <= 7) {
                $book = Book::create(array_merge(
                    $validatedData,
                    [
                        'is_featured' => true,
                        'status' => 'active'
                    ]
                ));
            } else {
                $book = Book::create(array_merge(
                    $validatedData,
                    [
                        'status' => 'active'
                    ]
                ));
            }

            $bookDetails = $validatedData['book_detail'];
            foreach ($bookDetails as &$detail) {
                $detail['book_id'] = $book->id;
            }

            $book->bookDetail()->createMany($bookDetails);

            return response()->json([
                "status" => true,
                "message" => "Create book successfully!",
                "data" => Book::with('bookDetail')->find($book->id)
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => "Create book failed!",
                "errors" => $th->getMessage()
            ], 500);
        }
    }
}

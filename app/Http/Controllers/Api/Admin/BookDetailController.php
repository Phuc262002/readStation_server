<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/admin/book-details',
    tags: ['Admin / BookDetail'],
    operationId: 'getAllBookDetails',
    summary: 'Get all book details',
    description: 'Get all book details',
    security: [
        ['bearerAuth' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'page',
            in: 'query',
            description: 'Page number',
            schema: new OA\Schema(type: 'integer')
        ),
        new OA\Parameter(
            name: 'pageSize',
            in: 'query',
            description: 'Number of items per page',
            schema: new OA\Schema(type: 'integer')
        ),
        new OA\Parameter(
            name: 'search',
            in: 'query',
            description: 'Search keyword',
            schema: new OA\Schema(type: 'string')
        ),
        new OA\Parameter(
            name: 'category_id',
            in: 'query',
            description: 'Category id',
            schema: new OA\Schema(type: 'integer')
        ),
        new OA\Parameter(
            name: 'author_id',
            in: 'query',
            description: 'Author id',
            schema: new OA\Schema(type: 'integer')
        ),
        new OA\Parameter(
            name: 'status',
            in: 'query',
            description: 'Status',
            schema: new OA\Schema(type: 'string')
        )
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get all book details successfully!'
        ),
        new OA\Response(
            response: 400,
            description: 'Dữ liệu không hợp lệ'
        )
    ],
)]

#[OA\Get(
    path: '/api/v1/admin/book-details/{id}',
    tags: ['Admin / BookDetail'],
    operationId: 'getBookDetail',
    summary: 'Get book detail',
    description: 'Get book detail',
    security: [
        ['bearerAuth' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            description: 'Id of book detail',
            schema: new OA\Schema(type: 'string')
        )
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get book detail successfully!'
        ),
        new OA\Response(
            response: 400,
            description: 'Dữ liệu không hợp lệ'
        ),
        new OA\Response(
            response: 404,
            description: 'Book detail not found!'
        )
    ],
)]

#[OA\Post(
    path: '/api/v1/admin/book-details/create',
    tags: ['Admin / BookDetail'],
    operationId: 'createBookDetail',
    summary: 'Create book detail',
    description: 'Create book detail',
    security: [
        ['bearerAuth' => []]
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: [
                'book_id',
                'sku_origin',
                'poster',
                'images',
                'book_version',
                'price',
                'hire_percent',
                'stock',
                'publish_date',
                'publishing_company_id',
                'issuing_company',
                'cardboard',
                'total_page',
                'language',
            ],
            properties: [
                new OA\Property(property: 'book_id', type: 'string'),
                new OA\Property(property: 'sku_origin', type: 'string'),
                new OA\Property(property: 'poster', type: 'string'),
                new OA\Property(property: 'images', type: 'array', items: new OA\Items(type: 'string')),
                new OA\Property(property: 'book_version', type: 'string'),
                new OA\Property(property: 'price', type: 'string'),
                new OA\Property(property: 'hire_percent', type: 'string'),
                new OA\Property(property: 'stock', type: 'string'),
                new OA\Property(property: 'publish_date', type: 'date'),
                new OA\Property(property: 'publishing_company_id', type: 'string'),
                new OA\Property(property: 'issuing_company', type: 'string'),
                new OA\Property(property: 'cardboard', type: 'string'),
                new OA\Property(property: 'total_page', type: 'string'),
                new OA\Property(property: 'translator', type: 'string'),
                new OA\Property(property: 'language', type: 'string'),
                new OA\Property(property: 'book_size', type: 'string'),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Create book detail successfully!'
        ),
        new OA\Response(
            response: 400,
            description: 'Dữ liệu không hợp lệ'
        ),
        new OA\Response(
            response: 500,
            description: 'Create book detail failed!'
        )
    ],
)]

#[OA\Put(
    path: '/api/v1/admin/book-details/update/{id}',
    tags: ['Admin / BookDetail'],
    operationId: 'updateBookDetail',
    summary: 'Update book detail',
    description: 'Update book detail',
    security: [
        ['bearerAuth' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            description: 'Id of book detail',
            schema: new OA\Schema(type: 'string')
        )
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: [
                'book_id',
                'sku_origin',
                'poster',
                'images',
                'book_version',
                'price',
                'hire_percent',
                'stock',
                'publish_date',
                'publishing_company_id',
                'issuing_company',
                'cardboard',
                'total_page',
                'language',
            ],
            properties: [
                new OA\Property(property: 'book_id', type: 'string'),
                new OA\Property(property: 'sku_origin', type: 'string'),
                new OA\Property(property: 'poster', type: 'string'),
                new OA\Property(property: 'images', type: 'array', items: new OA\Items(type: 'string')),
                new OA\Property(property: 'book_version', type: 'string'),
                new OA\Property(property: 'price', type: 'string'),
                new OA\Property(property: 'hire_percent', type: 'string'),
                new OA\Property(property: 'stock', type: 'string'),
                new OA\Property(property: 'publish_date', type: 'date'),
                new OA\Property(property: 'publishing_company_id', type: 'string'),
                new OA\Property(property: 'issuing_company', type: 'string'),
                new OA\Property(property: 'cardboard', type: 'string'),
                new OA\Property(property: 'total_page', type: 'string'),
                new OA\Property(property: 'translator', type: 'string'),
                new OA\Property(property: 'language', type: 'string'),
                new OA\Property(property: 'book_size', type: 'string'),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Update book detail successfully!'
        ),
        new OA\Response(
            response: 400,
            description: 'Dữ liệu không hợp lệ'
        ),
        new OA\Response(
            response: 404,
            description: 'Book detail not found!'
        ),
        new OA\Response(
            response: 500,
            description: 'Update book detail failed!'
        )
    ],
)]

#[OA\Delete(
    path: '/api/v1/admin/book-details/delete/{id}',
    tags: ['Admin / BookDetail'],
    operationId: 'deleteBookDetail',
    summary: 'Delete book detail',
    description: 'Delete book detail',
    security: [
        ['bearerAuth' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            description: 'Id of book detail',
            schema: new OA\Schema(type: 'string')
        )
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Delete book detail successfully!'
        ),
        new OA\Response(
            response: 400,
            description: 'Book detail is not found!'
        ),
        new OA\Response(
            response: 404,
            description: 'Book detail not found!'
        ),
        new OA\Response(
            response: 500,
            description: 'Delete book detail failed!'
        )
    ],
)]

class BookDetailController extends Controller
{
    public function index(Request $request)
    {
        $this->checkBookDetail();
        $validator = Validator::make($request->all(), [
            'page' => 'integer|min:1',
            'pageSize' => 'integer|min:1',
            'search' => 'string',
            'category_id' => 'integer',
            'author_id' => 'integer',
            'status' => 'string|in:active,inactive,deleted',
        ], [
            'page.integer' => 'Trang phải là số nguyên.',
            'page.min' => 'Trang phải lớn hơn hoặc bằng 1.',
            'pageSize.integer' => 'Kích thước trang phải là số nguyên.',
            'pageSize.min' => 'Kích thước trang phải lớn hơn hoặc bằng 1.',
            'category_id.integer' => 'Category_id phải là một số nguyên.',
            'author_id.integer' => 'Author_id phải là một số nguyên.',
            'status.in' => 'Status phải là active, inactive hoặc deleted.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Dữ liệu không hợp lệ",
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

        $query = BookDetail::with('book', 'book.category', 'book.author', 'book.shelve', 'book.shelve.bookcase', 'publishingCompany');

        $totalItems = $query->count();
        // Apply filters
        if ($search) {
            $query->whereHas('book', function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('original_title', 'like', '%' . $search . '%');
            });
        }

        if ($category_id) {
            $query->whereHas('book.category', function ($q) use ($category_id) {
                $q->where('id', $category_id);
            });
        }

        if ($author_id) {
            $query->whereHas('book.author', function ($q) use ($author_id) {
                $q->where('id', $author_id);
            });
        }

        if ($status) {
            $query->where('status', $status);
        } else {
            $query->where('status', '!=', 'deleted');
        }

        $bookdetails = $query->orderBy('created_at', 'desc')->paginate($pageSize, ['*'], 'page', $page);

        return response()->json([
            "status" => true,
            "message" => "Get all bookdetails successfully!",
            "data" => [
                "books" => $bookdetails->items(),
                "page" => $bookdetails->currentPage(),
                "pageSize" => $bookdetails->perPage(),
                "totalPages" => $bookdetails->lastPage(),
                "totalResults" => $bookdetails->total(),
                "total" => $totalItems
            ],
        ], 200);
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
            'book_id' => "required",
            'sku_origin' => 'required|string',
            'poster' => "required",
            'images' => "required|array",
            'book_version' => "required",
            'price' => "required",
            'hire_percent' => "required",
            'stock' => "required",
            'publish_date' => "required|date",
            'publishing_company_id' => "required",
            'issuing_company' => "required",
            'cardboard' => "required|in:hard,soft",
            'total_page' => "required",
            'translator' => "nullable",
            'language' => "required",
            'book_size' => "nullable",
        ], [
            'book_id.required' => 'Trường book_id là bắt buộc.',
            'sku_origin.required' => 'Trường sku_origin là bắt buộc.',
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
            'images.array' => 'Trường images phải là một mảng.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Dữ liệu không hợp lệ",
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

    public function show(Request $request, $id)
    {
        $this->checkBookDetail();
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|integer|min:1|exists:book_details,id'
        ],[
            'id.required' => 'Trường id là bắt buộc.',
            'id.integer' => 'Id phải là một số nguyên.',
            'id.min' => 'Id phải lớn hơn hoặc bằng 1.',
            'id.exists' => 'Id không tồn tại.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Dữ liệu không hợp lệ",
                "errors" => $validator->errors()
            ], 400);
        }

        $bookdetail = BookDetail::with('book', 'book.category', 'book.author', 'book.shelve', 'book.shelve.bookcase', 'publishingCompany')->find($id);
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

    public function update(Request $request, $id)
    {
        $validator = Validator::make(array_merge(['id' => $id], $request->all()), [
            'id' => 'required|min:1|exists:book_details,id',
            'book_id' => "required",
            'sku_origin' => 'required|string',
            'poster' => "required|string",
            'images' => "required|array",
            'book_version' => "required|string",
            'price' => "required",
            'hire_percent' => "required",
            'stock' => "required",
            'publish_date' => "required|date",
            'publishing_company_id' => "required|exists:publishing_companies,id",
            'issuing_company' => "required|string",
            'cardboard' => "required|string|in:hard,soft",
            'total_page' => "required",
            'translator' => "nullable|string",
            'language' => "required|string",
            'book_size' => "nullable|string",
        ], [
            'id.required' => 'Trường id là bắt buộc.',
            'id.min' => 'Id phải lớn hơn hoặc bằng 1.',
            'id.exists' => 'id không tồn tai.',
            'book_id.required' => 'Trường book_id là bắt buộc.',
            'sku_origin.required' => 'Trường sku_origin là bắt buộc.',
            'poster.required' => 'Trường poster là bắt buộc.',
            'images.required' => 'Trường images là bắt buộc.',
            'book_version.required' => 'Trường book_version là bắt buộc.',
            'price.required' => 'Trường price là bắt buộc.',
            'hire_percent.required' => 'Trường hire_percent là bắt buộc.',
            'stock.required' => 'Trường stock là bắt buộc.',
            'publish_date.required' => 'Trường publish_date là bắt buộc.',
            'publishing_company_id.required' => 'Trường publishing_company_id là bắt buộc.',
            'publishing_company_id.exists' => 'Trường publishing_company_id không tồn tại.',
            'issuing_company.required' => 'Trường issuing_company là bắt buộc.',
            'cardboard.required' => 'Trường cardboard là bắt buộc.',
            'total_page.required' => 'Trường total_page là bắt buộc.',
            'language.required' => 'Trường language là bắt buộc.',
            'images.array' => 'Trường images phải là một mảng.',
            'cardboard.in' => 'Trường cardboard phải là hard hoặc soft.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Dữ liệu không hợp lệ",
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
                "message" => "Update book detail failed!",
                "errors" => $th->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|integer|min:1|exists:book_details,id'
        ],[
            'id.required' => 'Trường id là bắt buộc.',
            'id.integer' => 'Id phải là một số nguyên.',
            'id.min' => 'Id phải lớn hơn hoặc bằng 1.',
            'id.exists' => 'Id không tồn tại.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Dữ liệu không hợp lệ",
                "errors" => $validator->errors()
            ], 400);
        }

        $bookdetail = BookDetail::find($id);

        if (!$bookdetail) {
            return response()->json([
                "status" => false,
                "message" => "Book detail not found!"
            ], 404);
        } elseif ($bookdetail->status == 'deleted') {
            return response()->json([
                "status" => false,
                "message" => "Book detail is not found!"
            ], 400);
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

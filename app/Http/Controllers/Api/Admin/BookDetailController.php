<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/api/v1/book-details/create',
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
            description: 'Validation error'
        ),
        new OA\Response(
            response: 500,
            description: 'Create book detail failed!'
        )
    ],
)]

#[OA\Get(
    path: '/api/v1/book-details/get-one/{id}',
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
            description: 'Validation error'
        ),
        new OA\Response(
            response: 404,
            description: 'Book detail not found!'
        )
    ],
)]

#[OA\Put(
    path: '/api/v1/book-details/update/{id}',
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
            description: 'Validation error'
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
    path: '/api/v1/book-details/delete/{id}',
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
            'images.array' => 'Trường images phải là một mảng.'
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

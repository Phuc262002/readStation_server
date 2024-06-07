<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Author;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/authors/admin/get-all',
    tags: ['Admin / Author'],
    operationId: 'getAllAuthors',
    summary: 'Get all authors (admin)',
    description: 'Get all authors',
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
            name: 'status',
            in: 'query',
            required: false,
            description: 'Trạng thái của tác giả',
            schema: new OA\Schema(type: 'string', enum: ['active', 'inactive', 'deleted'])
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get all authors successfully',
        ),
        new OA\Response(
            response: 400,
            description: 'Validation error',
        ),
    ],
)]

#[OA\Get(
    path: '/api/v1/authors/get-one/{id}',
    tags: ['Admin / Author'],
    operationId: 'getAuthor',
    summary: 'Get author by id',
    description: 'Get author by id',
    security: [
        ['bearerAuth' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            description: 'Id của tác giả',
            schema: new OA\Schema(type: 'integer')
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get author successfully',
        ),
        new OA\Response(
            response: 400,
            description: 'Validation error',
        ),
        new OA\Response(
            response: 404,
            description: 'Author not found',
        ),
    ],
)]

#[OA\Post(
    path: '/api/v1/authors/create',
    tags: ['Admin / Author'],
    operationId: 'createAuthor',
    summary: 'Create author',
    description: 'Create author',
    security: [
        ['bearerAuth' => []]
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['Admin / Author'],
            properties: [
                new OA\Property(property: 'author', type: 'string'),
                new OA\Property(property: 'avatar', type: 'string'),
                new OA\Property(property: 'is_featured', type: 'boolean'),
                new OA\Property(property: 'description', type: 'string'),
                new OA\Property(property: 'dob', type: 'date'),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Create author successfully',
        ),
        new OA\Response(
            response: 400,
            description: 'Validation error',
        ),
    ],
)]

#[OA\Put(
    path: '/api/v1/authors/update/{id}',
    tags: ['Admin / Author'],
    operationId: 'updateAuthor',
    summary: 'Update author',
    description: 'Update author',
    security: [
        ['bearerAuth' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            description: 'Id của tác giả',
            schema: new OA\Schema(type: 'integer')
        ),
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['Admin / Author'],
            properties: [
                new OA\Property(property: 'author', type: 'string'),
                new OA\Property(property: 'avatar', type: 'string'),
                new OA\Property(property: 'is_featured', type: 'boolean'),
                new OA\Property(property: 'description', type: 'string'),
                new OA\Property(property: 'dob', type: 'date'),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Update author successfully',
        ),
        new OA\Response(
            response: 400,
            description: 'Validation error',
        ),
        new OA\Response(
            response: 404,
            description: 'Author not found',
        ),
    ],
)]

#[OA\Delete(
    path: '/api/v1/authors/delete/{id}',
    tags: ['Admin / Author'],
    operationId: 'deleteAuthor',
    summary: 'Delete author',
    description: 'Delete author',
    security: [
        ['bearerAuth' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            description: 'Id của tác giả',
            schema: new OA\Schema(type: 'integer')
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Delete author successfully',
        ),
        new OA\Response(
            response: 400,
            description: 'Validation error',
        ),
        new OA\Response(
            response: 404,
            description: 'Author not found',
        ),
    ],
)]

class AuthorController extends Controller
{
    public function getAllAuthor(Request $request)
    {
        // Validate request parameters
        $validator = Validator::make($request->all(), [
            'page' => 'integer|min:1',
            'pageSize' => 'integer|min:1',
            'search' => 'string',
            'status' => 'string|in:active,inactive,deleted',
            'author' => 'string'
        ], [
            'page.integer' => 'Trang phải là số nguyên.',
            'page.min' => 'Trang phải lớn hơn hoặc bằng 1.',
            'pageSize.integer' => 'Kích thước trang phải là số nguyên.',
            'pageSize.min' => 'Kích thước trang phải lớn hơn hoặc bằng 1.',
            'author.string' => 'Tác giả phải là một chuỗi.',
            'status.in' => 'Status phải là active, inactive hoặc deleted'
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
        $type = $request->input('author');
        $status = $request->input('status');

        // Tạo query ban đầu
        $query = Author::query();

        // Lấy tổng số mục trong DB trước khi áp dụng bộ lọc tìm kiếm

        // Áp dụng bộ lọc theo type
        $totalItems = $query->count();
        $query = $query->filter($status, true);

        // Áp dụng bộ lọc tìm kiếm nếu có tham số tìm kiếm
        $query = $query->search($search);

        // Thực hiện phân trang
        $authors = $query->orderBy('created_at', 'desc')->paginate($pageSize, ['*'], 'page', $page);

        return response()->json([
            "status" => true,
            "message" => "Get all authors successfully!",
            "data" => [
                "authors" => $authors->items(),
                "page" => $authors->currentPage(),
                "pageSize" => $authors->perPage(),
                "totalPages" => $authors->lastPage(),
                "totalResults" => $authors->total(),
                "total" => $totalItems
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'author' => 'required|string',
            'avatar' => 'nullable|string',
            'is_featured' => 'nullable|boolean',
            'description' => 'nullable|string',
            'dob' => 'nullable|date',
        ]);

        $customMessages = [
            'author.required' => 'Tên tác giả không được để trống.',
            'author.string' => 'Tên tác giả phải là một chuỗi.',
            'avatar.string' => 'Avatar phải là một chuỗi.',
            'is_featured.boolean' => 'Is featured phải là một boolean.',
            'description.string' => 'Mô tả phải là một chuỗi.',
            'dob.date' => 'Ngày sinh phải là một ngày.'
        ];

        $validator->setCustomMessages($customMessages);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }

        if ($request->input('is_featured') == true) {
            if ($request->boolean('is_featured')) {
                Author::query()->update(['is_featured' => false]);
            }
        }

        if (Author::where('is_featured', true)->count() == 0) {
            $author = Author::create(array_merge(
                $validator->validated(),
                ['is_featured' => true]
            ));
        } else {
            $author = Author::create($validator->validated());
        }


        return response()->json([
            "status" => true,
            "message" => "Create author successfully!",
            "data" => $author
        ], 200);
    }

    public function show($id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|integer|min:1'
        ], [
            'id.required' => 'Trường id là bắt buộc.',
            'id.integer' => 'Id phải là một số nguyên.',
            'id.min' => 'Id phải lớn hơn hoặc bằng 1.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }

        $category = Author::find($id);

        if (!$category) {
            return response()->json([
                "status" => false,
                "message" => "Author not found!"
            ], 404);
        }

        return response()->json([
            "status" => true,
            "message" => "Get author successfully!",
            "data" => $category
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make(array_merge(['id' => $id], $request->all()), [
            'id' => 'required|integer|exists:authors,id|min:1',
            'author' => 'required|string',
            'avatar' => 'nullable|string',
            'description' => 'nullable|string',
            'is_featured' => 'nullable|boolean',
            'dob' => 'nullable|date',
        ], [
            'id.required' => 'Trường id là bắt buộc.',
            'id.integer' => 'Id phải là một số nguyên.',
            'id.min' => 'Id phải lớn hơn hoặc bằng 1.',
            'author.required' => 'Tên tác giả không được để trống.',
            'author.string' => 'Tên tác giả phải là một chuỗi.',
            'avatar.string' => 'Avatar phải là một chuỗi.',
            'description.string' => 'Mô tả phải là một chuỗi.',
            'is_featured.boolean' => 'Is featured phải là một boolean.',
            'dob.date' => 'Ngày sinh phải là một ngày.',
            'id.exists' => 'Tác giả không tồn tại.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }

        $author = Author::find($id);

        if (!$author) {
            return response()->json([
                "status" => false,
                "message" => "Author not found!"
            ], 404);
        }

        try {
            if ($request->input('is_featured') == true) {
                Author::query()->where('id', '!=', $id)->update(['is_featured' => false]);
                $author->update(array_merge(
                    $validator->validated(),
                    [
                        'is_featured' => true,
                        'status' => 'active'
                    ]
                ));
            } else {
                if (Author::where('is_featured', true)->count() == 0) {
                    $author->update(array_merge(
                        $validator->validated(),
                        [
                            'is_featured' => true,
                            'status' => 'active'
                        ]
                    ));
                } else {
                    $author->update($validator->validated());
                }
            }

            return response()->json([
                "status" => true,
                "message" => "Update author successfully!",
                "data" => $author
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => "Update author failed!"
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

        $author = Author::find($id);

        if (!$author) {
            return response()->json([
                "status" => false,
                "message" => "Author not found!"
            ], 404);
        } elseif ($author->status == 'deleted') {
            return response()->json([
                "status" => false,
                "message" => "Author not found!"
            ], 400);
        }

        try {
            $author->delete();

            if ($author->status == 'deleted') {
                return response()->json([
                    "status" => true,
                    "message" => "Tác giả đạ thêm vào thùng rác! Bạn có thể khôi phục lại sau này!",
                ], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => "Delete author failed!"
            ], 500);
        }

        return response()->json([
            "status" => true,
            "message" => "Danh mục đã được xóa vĩnh viễn! Bạn không thể khôi phục lại sau này!",
        ], 200);
    }
}

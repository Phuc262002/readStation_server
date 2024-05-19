<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Author;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Validate request parameters
        $validator = Validator::make($request->all(), [
            'page' => 'integer|min:1',
            'pageSize' => 'integer|min:1',
            'search' => 'string',
            'status' => 'string|in:active,inactive,deleted',
            'author' => 'string'
        ]);

        $customMessages = [
            'page.integer' => 'Trang phải là số nguyên.',
            'page.min' => 'Trang phải lớn hơn hoặc bằng 1.',
            'pageSize.integer' => 'Kích thước trang phải là số nguyên.',
            'pageSize.min' => 'Kích thước trang phải lớn hơn hoặc bằng 1.',
            'author.string' => 'Tác giả phải là một chuỗi.',
            'status.in' => 'Status phải là active, inactive hoặc deleted'
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
        $type = $request->input('author');
        $status = $request->input('status');

        // Tạo query ban đầu
        $query = Author::query();

        // Lấy tổng số mục trong DB trước khi áp dụng bộ lọc tìm kiếm

        // Áp dụng bộ lọc theo type
        $totalItems = $query->count();
        $query = $query->filter($type, $status);

        // Áp dụng bộ lọc tìm kiếm nếu có tham số tìm kiếm
        $query = $query->search($search);

        // Thực hiện phân trang
        $authors = $query->paginate($pageSize, ['*'], 'page', $page);

        return response()->json([
            "status" => true,
            "message" => "Get all authors successfully!",
            "data" => [
                "authors" => $authors->items(),
                "page" => $authors->currentPage(),
                "pageSize" => $authors->perPage(),
                "lastPage" => $authors->lastPage(),
                "totalResults" => $authors->total(),
                "total" => $totalItems
            ],
        ]);
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

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Author $author)
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

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Author $author)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Author $author)
    {
        $id = $request->route('id');

        $validator = Validator::make(array_merge(['id' => $id], $request->all()), [
            'id' => 'required|integer|min:1',
            'author' => 'required|string',
            'avatar' => 'nullable|string',
            'description' => 'nullable|string',
            'is_featured' => 'nullable|boolean',
            'dob' => 'nullable|date',
        ]);

        $customMessages = [
            'id.required' => 'Trường id là bắt buộc.',
            'id.integer' => 'Id phải là một số nguyên.',
            'id.min' => 'Id phải lớn hơn hoặc bằng 1.',
            'author.required' => 'Tên tác giả không được để trống.',
            'author.string' => 'Tên tác giả phải là một chuỗi.',
            'avatar.string' => 'Avatar phải là một chuỗi.',
            'description.string' => 'Mô tả phải là một chuỗi.',
            'is_featured.boolean' => 'Is featured phải là một boolean.',
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

        $author = Author::find($id);

        if (!$author) {
            return response()->json([
                "status" => false,
                "message" => "Author not found!"
            ], 404);
        }


        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }

        try {
            if ($request->input('is_featured') == true) {
                if ($request->boolean('is_featured')) {
                    Author::query()->update(['is_featured' => false]);
                }
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "At least one author must be featured!"
                ], 400);
            }


            $author->update($validator->validated());

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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Author $author)
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

        $author = Author::find($id);

        if (!$author) {
            return response()->json([
                "status" => false,
                "message" => "Author not found!"
            ], 404);
        }

        try {
            $author->delete();
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => "Delete author failed!"
            ], 500);
        }

        return response()->json([
            "status" => true,
            "message" => "Delete author successfully!",
        ], 200);
    }
}

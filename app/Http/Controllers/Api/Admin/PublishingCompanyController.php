<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\PublishingCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/admin/publishing-companies',
    tags: ['Admin / Publishing Company'],
    operationId: 'getAllPublishingCompany',
    summary: 'Get all publishing companies',
    description: 'Get all publishing companies',
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
            description: 'Trạng thái nhà xuất bản',
            schema: new OA\Schema(type: 'string', enum: ['active', 'inactive', 'deleted'])
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get all publishing companies successfully',
        ),
        new OA\Response(
            response: 400,
            description: 'Validation error',
        ),
    ],
)]

#[OA\Get(
    path: '/api/v1/admin/publishing-companies/{id}',
    tags: ['Admin / Publishing Company'],
    operationId: 'getPublishingCompany',
    summary: 'Get a publishing company',
    description: 'Get a publishing company',
    security: [
        ['bearerAuth' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            description: 'ID của nhà xuất bản',
            schema: new OA\Schema(type: 'integer')
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get publishing company successfully',
        ),
        new OA\Response(
            response: 400,
            description: 'Validation error',
        ),
        new OA\Response(
            response: 404,
            description: 'Publishing company not found!',
        ),
    ],
)]

#[OA\Post(
    path: '/api/v1/admin/publishing-companies/create',
    tags: ['Admin / Publishing Company'],
    operationId: 'createPublishingCompany',
    summary: 'Create a new publishing company',
    description: 'Create a new publishing company',
    security: [
        ['bearerAuth' => []]
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['name'],
            properties: [
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'description', type: 'string'),
                new OA\Property(property: 'logo_company', type: 'string'),
                new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive', 'deleted']),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Create publishing company successfully',
        ),
        new OA\Response(
            response: 400,
            description: 'Validation error',
        ),
    ],
)]

#[OA\Put(
    path: '/api/v1/admin/publishing-companies/update/{id}',
    tags: ['Admin / Publishing Company'],
    operationId: 'updatePublishingCompany',
    summary: 'Update a publishing company',
    description: 'Update a publishing company',
    security: [
        ['bearerAuth' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            description: 'ID của danh mục',
            schema: new OA\Schema(type: 'integer')
        ),
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['name'],
            properties: [
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'description', type: 'string'),
                new OA\Property(property: 'logo_company', type: 'string'),
                new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive', 'deleted']),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Update category successfully',
        ),
        new OA\Response(
            response: 400,
            description: 'Validation error',
        ),
        new OA\Response(
            response: 404,
            description: 'Publishing company not found!',
        ),
        new OA\Response(
            response: 500,
            description: 'Update publishing company failed',
        ),
    ],
)]

#[OA\Delete(
    path: '/api/v1/admin/publishing-companies/delete/{id}',
    tags: ['Admin / Publishing Company'],
    operationId: 'deletePublishingCompany',
    summary: 'Delete a publishing company',
    description: 'Delete a publishing company',
    security: [
        ['bearerAuth' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            description: 'ID của danh mục',
            schema: new OA\Schema(type: 'integer')
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Delete publishing company successfully',
        ),
        new OA\Response(
            response: 400,
            description: 'Validation error',
        ),
        new OA\Response(
            response: 404,
            description: 'Publishing company not found!',
        ),
        new OA\Response(
            response: 500,
            description: 'Delete publishing company failed',
        ),
    ],
)]


class PublishingCompanyController extends Controller
{
    
    public function getAllPublishingCompany(Request $request)
    {
        // Validate request parameters
        $validator = Validator::make($request->all(), [
            'page' => 'integer|min:1',
            'pageSize' => 'integer|min:1',
            'search' => 'string',
            'status' => 'string|in:active,inactive,deleted',
        ],[
            'page.integer' => 'Trang phải là số nguyên.',
            'page.min' => 'Trang phải lớn hơn hoặc bằng 1.',
            'pageSize.integer' => 'Kích thước trang phải là số nguyên.',
            'pageSize.min' => 'Kích thước trang phải lớn hơn hoặc bằng 1.',
            'status.in' => 'Status phải là active, inactive hoặc deleted',
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
        $query = PublishingCompany::query();

        // Lấy tổng số mục trong DB trước khi áp dụng bộ lọc tìm kiếm

        // Áp dụng bộ lọc theo type
        $totalItems = $query->count();
        $query = $query->filter($status, true);

        // Áp dụng bộ lọc tìm kiếm nếu có tham số tìm kiếm
        $query = $query->search($search);

        // Thực hiện phân trang
        $publishingCompany = $query->orderBy('created_at', 'desc')->paginate($pageSize, ['*'], 'page', $page);

        return response()->json([
            "status" => true,
            "message" => "Get all publishing company successfully",
            "data" => [
                "publishing_companies" => $publishingCompany->items(),
                "page" => $publishingCompany->currentPage(),
                "pageSize" => $publishingCompany->perPage(),
                "totalPages" => $publishingCompany->lastPage(),
                "totalResults" => $publishingCompany->total(),
                "total" => $totalItems
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'logo_company' => 'nullable|string',
            'description' => 'nullable|string',
            'status' => 'string|in:active,inactive,deleted',
        ],[
            'name.required' => 'Tên nhà xuất bản không được để trống.',
            'name.string' => 'Tên nhà xuất bản phải là chuỗi.',
            'logo_company.string' => 'Logo nhà xuất bản phải là chuỗi.',
            'description.string' => 'Mô tả nhà xuất bản phải là chuỗi.',
            'status.string' => 'Trạng thái nhà xuất bản phải là chuỗi.',
            'status.in' => 'Trạng thái nhà xuất bản không hợp lệ.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }

        $publishingCompany = PublishingCompany::create(array_merge(
            $validator->validated(),
        ));

        return response()->json([
            "status" => true,
            "message" => "Create publishing company successfully",
            "data" => $publishingCompany
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|integer|min:1|exists:publishing_companies,id'
        ],[
            'id.required' => 'Trường id là bắt buộc.',
            'id.integer' => 'Id phải là một số nguyên.',
            'id.min' => 'Id phải lớn hơn hoặc bằng 1.',
            'id.exists' => 'Id không tồn tại.',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }

        $publishingCompany = PublishingCompany::find($id);

        if (!$publishingCompany) {
            return response()->json([
                "status" => false,
                "message" => "Publishing company not found!"
            ], 404);
        }

        return response()->json([
            "status" => true,
            "message" => "Get publishing company successfully",
            "data" => $publishingCompany
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make(array_merge(['id' => $id], $request->all()), [
            'id' => 'required|integer|min:1',
            'name' => 'string',
            'logo_company' => 'nullable|string',
            'description' => 'nullable|string',
            'status' => 'string|in:active,inactive,deleted',
        ],[
            'id.integer' => 'Id phải là một số nguyên.',
            'id.min' => 'Id phải lớn hơn hoặc bằng 1.',
            'name.string' => 'Tên nhà xuất bản phải là một chuỗi.',
            'logo_company.string' => 'Logo nhà xuất bản phải là chuỗi.',
            'description.string' => 'Mô tả nhà xuất bản phải là chuỗi.',
            'status.string' => 'Trạng thái nhà xuất bản phải là chuỗi.',
            'status.in' => 'Trạng thái nhà xuất bản phải là active,inactive,deleted.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }

        $publishingCompany = PublishingCompany::find($id);

        if (!$publishingCompany) {
            return response()->json([
                "status" => false,
                "message" => "Publishing company not found!"
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
            $publishingCompany->update($validator->validated());

            return response()->json([
                "status" => true,
                "message" => "Update publishingCompany successfully",
                "data" => $publishingCompany
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => "Update publishingCompany failed",
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|integer|min:1|exists:publishing_companies,id'
        ],[
            'id.required' => 'Trường id là bắt buộc.',
            'id.integer' => 'Id phải là một số nguyên.',
            'id.min' => 'Id phải lớn hơn hoặc bằng 1.',
            'id.exists' => 'Id không tồn tại.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }

        $publishingCompany = PublishingCompany::find($id);

        if (!$publishingCompany) {
            return response()->json([
                "status" => false,
                "message" => "Publish company not found!"
            ], 404);
        }

        try {
            $publishingCompany->delete();

            if ($publishingCompany->status == 'deleted') {
                return response()->json([
                    "status" => true,
                    "message" => "Nhau xuất bản đã được thêm vào thùng rác. Bạn có thể khôi phục lại sau này!",
                ], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => "Delete publish company failed",
            ], 500);
        }

        return response()->json([
            "status" => true,
            "message" => "Nhà xuất bản đã được xóa vĩnh viễn. Bạn không thể khôi phục lại sau này!",
        ], 200);
    }
}

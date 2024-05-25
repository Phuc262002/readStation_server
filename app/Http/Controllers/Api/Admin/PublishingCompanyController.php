<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\PublishingCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/api/v1/publishing-companies/create',
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
    path: '/api/v1/publishing-companies/update/{id}',
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
    path: '/api/v1/publishing-companies/delete/{id}',
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
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
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
    public function show(PublishingCompany $publishingCompany)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PublishingCompany $publishingCompany)
    {
        $id = $request->route('id');

        $validator = Validator::make(array_merge(['id' => $id], $request->all()), [
            'id' => 'required|integer|min:1',
            'name' => 'required|string',
            'logo_company' => 'nullable|string',
            'description' => 'nullable|string',
            'status' => 'string|in:active,inactive,deleted',
        ],[
            'id.required' => 'Trường id là bắt buộc.',
            'id.integer' => 'Id phải là một số nguyên.',
            'id.min' => 'Id phải lớn hơn hoặc bằng 1.',
            'name.required' => 'Trường name là bắt buộc.',
            'name.string' => 'Name phải là một chuỗi.',
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
    public function destroy(Request $request, PublishingCompany $publishingCompany)
    {
        $id = $request->route('id');

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
        } else if ($publishingCompany->status == 'deleted') {
            return response()->json([
                "status" => false,
                "message" => "Publish company has been deleted!"
            ], 400);
        }

        try {
            $publishingCompany->delete();
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => "Delete publish company failed",
            ], 500);
        }

        return response()->json([
            "status" => true,
            "message" => "Delete publish company successfully",
        ], 200);
    }
}

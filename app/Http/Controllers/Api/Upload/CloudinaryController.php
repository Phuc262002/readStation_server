<?php

namespace App\Http\Controllers\Api\Upload;

use App\Http\Controllers\Controller;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/api/v1/upload/image',
    operationId: 'upload',
    tags: ['Upload'],
    summary: 'Upload image',
    description: 'Upload image',
    security: [
        ['bearerAuth' => []]
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(
                type: 'object',
                required: ['image'],
                properties: [
                    new OA\Property(
                        property: 'image',
                        type: 'array',
                        items: new OA\Items(type: 'string', format: 'binary')
                    )
                ]
            )
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Upload images successfully'
        ),
        new OA\Response(
            response: 400,
            description: 'Validation error'
        ),
        new OA\Response(
            response: 500,
            description: 'Upload error'
        )
    ]
)]


class CloudinaryController extends Controller
{
    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ], [
            'image.required' => 'Vui lòng chọn ảnh',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $uploadedFileUrl = Cloudinary::upload($request->file('image')->getRealPath())->getSecurePath();

            if (!$uploadedFileUrl) {
                return response()->json([
                    'status' => false,
                    'message' => 'Upload error',
                    'errors' => 'Upload file error'
                ], 500);
            }

            return response()->json([
                "status" => true,
                "message" => "Get book successfully!",
                'url' => $uploadedFileUrl
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Upload error',
                'errors' => $th->getMessage()
            ], 500);
        }
    }
}

<?php

namespace App\Http\Controllers\Api\Upload;

use App\Http\Controllers\Controller;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/api/v1/upload/images',
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
            description: 'Upload image successfully'
        ),
        new OA\Response(
            response: 400,
            description: 'Dữ liệu không hợp lệ'
        ),
        new OA\Response(
            response: 500,
            description: 'Upload error'
        )
    ]
)]

#[OA\Get(
    path: '/api/v1/upload/images',
    operationId: 'getAllImages',
    tags: ['Upload'],
    summary: 'Get all images',
    description: 'Get all images',
    security: [
        ['bearerAuth' => []]
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get all files successfully'
        )
    ]
)]

#[OA\Delete(
    path: '/api/v1/upload/images/delete/{publicId}',
    operationId: 'deleteImage',
    tags: ['Upload'],
    summary: 'Delete image',
    description: 'Delete image',
    security: [
        ['bearerAuth' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'publicId',
            in: 'path',
            required: true,
            description: 'Public ID of image',
            schema: new OA\Schema(type: 'string')
        )
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Delete image successfully'
        ),
        new OA\Response(
            response: 500,
            description: 'Delete image error'
        )
    ]
)]


class CloudinaryController extends Controller
{
    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ], [
            'image.required' => 'Vui lòng chọn ảnh',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $uploadedFileUrl = Cloudinary::upload($request->file('image')->getRealPath());

            if (!$uploadedFileUrl) {
                return response()->json([
                    'status' => false,
                    'message' => 'Upload error',
                    'errors' => 'Upload file error'
                ], 500);
            }

            return response()->json([
                "status" => true,
                "message" => "Upload image successfully",
                'data' => [
                    'url' => $uploadedFileUrl->getSecurePath(),
                    'publicId' => $uploadedFileUrl->getPublicId(),
                ]
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Upload error',
                'errors' => $th->getMessage()
            ], 500);
        }
    }

    public function getAllImages()
    {
        try {
            $allFiles = [];
            $nextCursor = null;

            do {
                $response = Cloudinary::search()
                    ->expression('resource_type:image')
                    ->maxResults(50)
                    ->nextCursor($nextCursor)
                    ->execute();

                if (isset($response['resources']) && is_array($response['resources'])) {
                    $allFiles = array_merge($allFiles, $response['resources']);
                }

                $nextCursor = $response['next_cursor'] ?? null;
            } while ($nextCursor);

            return response()->json([
                'status' => true,
                'message' => 'Get all files successfully',
                'data' => $allFiles
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to get files',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteImage($publicId)
    {
        $result = Cloudinary::destroy($publicId);

        if ($result['result'] === 'ok') {
            return response()->json([
                'status' => true,
                'message' => 'Delete image successfully',
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Delete image error',
            'errors' => 'Delete image error'
        ], 500);
    }
}

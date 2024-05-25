<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;




#[OA\Post(
    path: '/api/v1/posts/create',
    tags: ['Post'],
    operationId: 'createPost',
    summary: 'Create a new post',
    description: 'Create a new post',
    security: [
        ['bearerAuth' => []]
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['category_id', 'title', 'content', 'summary', 'image'],
            properties: [
                new OA\Property(property: 'category_id', type: 'string'),
                new OA\Property(property: 'title', type: 'string'),
                new OA\Property(property: 'content', type: 'string'),
                new OA\Property(property: 'summary', type: 'string'),
                new OA\Property(property: 'image', type: 'string'),
                new OA\Property(property: 'status', type: 'string', enum: ['published', 'draft', 'wating_approve']),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Create post successfully!',
        ),
        new OA\Response(
            response: 400,
            description: 'Validation error',
        ),
        new OA\Response(
            response: 500,
            description: 'Create post failed!',
        ),
    ]
)]

#[OA\Put(
    path: '/api/v1/posts/update/{id}',
    tags: ['Post'],
    operationId: 'updatePost',
    summary: 'Update a post',
    description: 'Update a post by ID',
    security: [
        ['bearerAuth' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            description: 'ID của post',
            schema: new OA\Schema(type: 'integer')
        ),
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['category_id', 'title', 'content', 'summary', 'image', 'status'],
            properties: [
                new OA\Property(property: 'category_id', type: 'string'),
                new OA\Property(property: 'title', type: 'string'),
                new OA\Property(property: 'content', type: 'string'),
                new OA\Property(property: 'summary', type: 'string'),
                new OA\Property(property: 'image', type: 'string'),
                new OA\Property(property: 'status', type: 'string', enum: ['published', 'draft']),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Update post successfully!',
        ),
        new OA\Response(
            response: 400,
            description: 'Validation error',
        ),
        new OA\Response(
            response: 404,
            description: 'Post not found',
        ),
        new OA\Response(
            response: 500,
            description: 'Update post failed!',
        ),
    ]
)]

#[OA\Delete(
    path: '/api/v1/posts/delete/{id}',
    tags: ['Post'],
    operationId: 'deletePost',
    summary: 'Delete a post',
    description: 'Delete a post by ID',
    security: [
        ['bearerAuth' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            description: 'ID của post',
            schema: new OA\Schema(type: 'integer')
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Delete post successfully!',
        ),
        new OA\Response(
            response: 400,
            description: 'Validation error',
        ),
        new OA\Response(
            response: 404,
            description: 'Post not found',
        ),
        new OA\Response(
            response: 500,
            description: 'Delete post failed!',
        ),
    ]
)]

class PostController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|string',
            'title' => 'required|string|max:255',
            "content" => "required|string",
            "summary" => "required|string",
            "image" => "required|string",
            "status" => "string|in:published,draft,wating_approve",
        ], [
            'category_id.required' => 'Category_id không được để trống.',
            'category_id.string' => 'Category_id phải là một chuỗi.',
            'title.required' => 'Title không được để trống.',
            'title.string' => 'Title phải là một chuỗi.',
            'title.max' => 'Title không được vượt quá 255 ký tự.',
            'content.required' => 'Content không được để trống.',
            'content.string' => 'Content phải là một chuỗi.',
            'summary.required' => 'Summary không được để trống.',
            'summary.string' => 'Summary phải là một chuỗi.',
            'image.required' => 'Image không được để trống.',
            'image.string' => 'Image phải là một chuỗi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }

        try {
            if (auth()->user()->role->name == 'admin' || auth()->user()->role->name == 'manager') {
                $post = Post::create(array_merge(
                    $validator->validated(),
                    ["user_id" => auth()->user()->id]
                ));
            } else {
                $post = Post::create(array_merge(
                    $validator->validated(),
                    [
                        "user_id" => auth()->user()->id, 
                        "status" => $request->status == 'published' ? 'wating_approve' : $request->status
                    ]
                ));
            }

            return response()->json([
                "status" => true,
                "message" => "Create post successfully!",
                "data" => $post,
                "user" => auth()->user()->role->name
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => "Create post failed!",
                "errors" => $th->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Post $post)
    {
        $id = $request->route('id');

        $validator = Validator::make(array_merge(
            $request->all(),
            ["id" => $id]
        ), [
            'id' => 'required|exists:posts,id',
            'category_id' => 'required|string',
            'title' => 'required|string|max:255',
            "content" => "required|string",
            "summary" => "required|string",
            "image" => "required|string",
            "status" => "string|in:published,draft",
        ], [
            'id.required' => 'ID không được để trống.',
            'id.exists' => 'ID không tồn tại.',
            'category_id.required' => 'Category_id không được để trống.',
            'category_id.string' => 'Category_id phải là một chuỗi.',
            'title.required' => 'Title không được để trống.',
            'title.string' => 'Title phải là một chuỗi.',
            'title.max' => 'Title không được vượt quá 255 ký tự.',
            'content.required' => 'Content không được để trống.',
            'content.string' => 'Content phải là một chuỗi.',
            'summary.required' => 'Summary không được để trống.',
            'summary.string' => 'Summary phải là một chuỗi.',
            'image.required' => 'Image không được để trống.',
            'image.string' => 'Image phải là một chuỗi.',
            'status.required' => 'Status không được để trống.',
            'status.in' => 'Status phải là published hoặc draft.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }

        $post = Post::find($id);

        if (!$post) {
            return response()->json([
                "status" => false,
                "message" => "Post not found",
            ], 404);
        }

        try {
            $post->update($validator->validated());

            return response()->json([
                "status" => true,
                "message" => "Update post successfully!",
                "data" => $post
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => "Update post failed!",
                "errors" => $th->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request)
    {
        $id = $request->route('id');

        $validator = Validator::make(['id' => $id], [
            'id' => 'required|exists:posts,id',
        ], [
            'id.required' => 'ID không được để trống.',
            'id.exists' => 'ID không tồn tại.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }

        $post = Post::find($id);

        if (!$post) {
            return response()->json([
                "status" => false,
                "message" => "Post not found",
            ], 404);
        } else if ($post->status == 'deleted') {
            return response()->json([
                "status" => false,
                "message" => "Post not found",
            ], 404);
        }

        try {
            $post->delete();

            return response()->json([
                "status" => true,
                "message" => "Delete post successfully!",
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => "Delete post failed!",
                "errors" => $th->getMessage()
            ], 500);
        }
    }
}

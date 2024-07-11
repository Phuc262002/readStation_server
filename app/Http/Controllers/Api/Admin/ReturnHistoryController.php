<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReturnHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/admin/return-histories',
    operationId: 'getReturnHistories',
    summary: 'Get all return histories',
    description: 'Get all return histories',
    tags: ['Admin / Return History'],
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
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get all return histories successfully',
        ),
        new OA\Response(
            response: 400,
            description: 'Validation error',
        ),
    ],
)]

class ReturnHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'page' => 'integer|min:1',
            'pageSize' => 'integer|min:1',
        ], [
            'page.integer' => 'Page phải là số nguyên.',
            'pageSize.integer' => 'PageSize phải là số nguyên.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }

        $page = $request->input('page', 1);
        $pageSize = $request->input('pageSize', 10);

        try {
            $query = ReturnHistory::query();
            $totalItems = $query->count();

            $returnHistory = $query->orderBy('created_at', 'desc')->paginate($pageSize, ['*'], 'page', $page);

            return response()->json([
                "status" => true,
                "message" => "Get all return histories successfully!",
                "data" => [
                    "returnHistory" => $returnHistory->items(),
                    "page" => $returnHistory->currentPage(),
                    "pageSize" => $returnHistory->perPage(),
                    "totalPages" => $returnHistory->lastPage(),
                    "totalResults" => $returnHistory->total(),
                    "total" => $totalItems
                ],
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to get all return histories',
            ], 500);
        }
    }

    public function store(Request $request)
    {
        //
    }

    public function show(ReturnHistory $returnHistory)
    {
        //
    }

    public function update(Request $request, ReturnHistory $returnHistory)
    {
        //
    }

}

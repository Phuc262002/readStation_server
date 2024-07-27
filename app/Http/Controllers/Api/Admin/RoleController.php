<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/admin/roles',
    tags: ['Admin / Role'],
    operationId: 'adminRoleIndex',
    summary: 'Get all roles',
    description: 'Get all roles',
    security: [
        ['bearerAuth' => []]
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get all roles',
        ),
        new OA\Response(
            response: 400,
            description: 'Dữ liệu không hợp lệ',
        ),
    ],
)]

class RoleController extends Controller
{
    public function index()
    {
        try {
            $role = Role::where('id', '!=', 3)->get();

            return response()->json([
                'status' => true,
                'message' => 'Get all roles',
                'data' => $role
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to get all roles',
            ], 500);
        }
    }
}

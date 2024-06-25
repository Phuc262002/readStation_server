<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Mail\InfomationAccount;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/admin/users',
    tags: ['Admin / User'],
    operationId: 'getUsers',
    summary: 'Get all users',
    description: 'Get all users',
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
    path: '/api/v1/admin/users/get-one/{id}',
    tags: ['Admin / User'],
    operationId: 'getUser',
    summary: 'Get a user',
    description: 'Get a user',
    security: [
        ['bearerAuth' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            description: 'Id của user',
            schema: new OA\Schema(type: 'string')
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get user successfully',
        ),
        new OA\Response(
            response: 400,
            description: 'Validation error',
        ),
    ],
)]

#[OA\Post(
    path: '/api/v1/admin/users/create',
    tags: ['Admin / User'],
    operationId: 'createUser',
    summary: 'Create a new user',
    description: 'Create a new user',
    security: [
        ['bearerAuth' => []]
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['fullname', 'email', 'phone'],
            properties: [
                new OA\Property(property: 'role_id', type: 'integer', description: 'Role id', default: 1),
                new OA\Property(property: 'avatar', type: 'string', description: 'Avatar'),
                new OA\Property(property: 'fullname', type: 'string', description: 'Fullname'),
                new OA\Property(property: 'job', type: 'string', description: 'Job'),
                new OA\Property(property: 'story', type: 'string', description: 'Story'),
                new OA\Property(property: 'gender', type: 'string', enum: ['male', 'female', 'other']),
                new OA\Property(property: 'dob', type: 'date', description: 'Date of birth'),
                new OA\Property(property: 'email', type: 'string', description: 'Email'),
                new OA\Property(
                    property: 'citizen_identity_card',
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'citizen_name', type: 'string', description: 'Tên công dân'),
                        new OA\Property(property: 'citizen_code', type: 'string', description: 'Mã CCCD'),
                        new OA\Property(property: 'date_of_issue', type: 'date', description: 'Ngày cấp'),
                        new OA\Property(property: 'place_of_issue', type: 'string', description: 'Nơi cấp'),
                    ],
                ),
                new OA\Property(
                    property: 'student_id_card',
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'student_name', type: 'string', description: 'Tên sinh viên'),
                        new OA\Property(property: 'student_code', type: 'string', description: 'Mã số sinh viên'),
                        new OA\Property(property: 'student_card_expired', type: 'date', description: 'Ngày hết hạn'),
                        new OA\Property(property: 'place_of_study', type: 'string', description: 'Tên trường'),
                    ],
                ),
                new OA\Property(property: 'street', type: 'string', description: 'Street'),
                new OA\Property(property: 'province', type: 'string', description: 'Province'),
                new OA\Property(property: 'district', type: 'string', description: 'District'),
                new OA\Property(property: 'ward', type: 'string', description: 'Ward'),
                new OA\Property(property: 'address_detail', type: 'string', description: 'Address detail'),
                new OA\Property(property: 'phone', type: 'string', description: 'Phone'),
                new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive', 'banned', 'deleted']),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: 'Create user successfully',
        ),
        new OA\Response(
            response: 400,
            description: 'Validation error',
        ),
        new OA\Response(
            response: 500,
            description: 'Create user failed',
        ),
    ],
)]

#[OA\Put(
    path: '/api/v1/admin/users/update/{id}',
    tags: ['Admin / User'],
    operationId: 'updateUser',
    summary: 'Update a user',
    description: 'Update a user',
    security: [
        ['bearerAuth' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            description: 'Id của user',
            schema: new OA\Schema(type: 'string')
        ),
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['fullname', 'email', 'phone'],
            properties: [
                new OA\Property(property: 'role_id', type: 'integer', description: 'Role id', default: 1),
                new OA\Property(property: 'avatar', type: 'string', description: 'Avatar'),
                new OA\Property(property: 'fullname', type: 'string', description: 'Fullname'),
                new OA\Property(property: 'job', type: 'string', description: 'Job'),
                new OA\Property(property: 'story', type: 'string', description: 'Story'),
                new OA\Property(property: 'gender', type: 'string', enum: ['male', 'female', 'other']),
                new OA\Property(property: 'dob', type: 'date', description: 'Date of birth'),
                new OA\Property(
                    property: 'citizen_identity_card',
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'citizen_name', type: 'string', description: 'Tên công dân'),
                        new OA\Property(property: 'citizen_code', type: 'string', description: 'Mã CCCD'),
                        new OA\Property(property: 'date_of_issue', type: 'date', description: 'Ngày cấp'),
                        new OA\Property(property: 'place_of_issue', type: 'string', description: 'Nơi cấp'),
                    ],
                ),
                new OA\Property(
                    property: 'student_id_card',
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'student_name', type: 'string', description: 'Tên sinh viên'),
                        new OA\Property(property: 'student_code', type: 'string', description: 'Mã số sinh viên'),
                        new OA\Property(property: 'student_card_expired', type: 'date', description: 'Ngày hết hạn'),
                        new OA\Property(property: 'place_of_study', type: 'string', description: 'Tên trường'),
                    ],
                ),
                new OA\Property(property: 'street', type: 'string', description: 'Street'),
                new OA\Property(property: 'province', type: 'string', description: 'Province'),
                new OA\Property(property: 'district', type: 'string', description: 'District'),
                new OA\Property(property: 'ward', type: 'string', description: 'Ward'),
                new OA\Property(property: 'address_detail', type: 'string', description: 'Address detail'),
                new OA\Property(property: 'phone', type: 'string', description: 'Phone'),
                new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive', 'banned', 'deleted']),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Update user successfully',
        ),
        new OA\Response(
            response: 400,
            description: 'Validation error',
        ),
        new OA\Response(
            response: 500,
            description: 'Update user failed',
        ),
    ],
)]


class UserController extends Controller
{
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'page' => 'integer|min:1',
            'pageSize' => 'integer|min:1',
            'search' => 'string',
            'status' => 'string|in:active,inactive,deleted,needUpdateDetail',
        ], [
            'page.integer' => 'Trang phải là số nguyên.',
            'page.min' => 'Trang phải lớn hơn hoặc bằng 1.',
            'pageSize.integer' => 'Kích thước trang phải là số nguyên.',
            'pageSize.min' => 'Kích thước trang phải lớn hơn hoặc bằng 1.',
            'status.in' => 'Status phải là active, inactive, needUpdateDetail hoặc deleted.',
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
        $status = $request->input('status');

        // Tạo query ban đầu
        $query = User::query()
            ->with(['role', 'province', 'district', 'ward']);

        $totalItems = $query->count();
        $query = $query->filter($status);

        // Áp dụng bộ lọc tìm kiếm nếu có tham số tìm kiếm
        $query = $query->search($search);

        // Thực hiện phân trang
        $users = $query->orderBy('created_at', 'desc')->paginate($pageSize, ['*'], 'page', $page);

        $users->getCollection()->transform(function ($user) {
            return array_merge($user->toArray(), [
                'id' => $user->id,
            ]);
        });

        return response()->json([
            "status" => true,
            "message" => "Get all users successfully!",
            "data" => [
                "users" => $users->items(),
                "page" => $users->currentPage(),
                "pageSize" => $users->perPage(),
                "totalPages" => $users->lastPage(),
                "totalResults" => $users->total(),
                "total" => $totalItems
            ],
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role_id' => 'nullable|integer|exists:roles,id',
            'avatar' => 'nullable|string',
            'fullname' => 'required|string',
            'job' => 'nullable|string',
            'story' => 'nullable|string',
            'gender' => 'nullable|string|in:male,female,other',
            'dob' => 'nullable|date',
            'email' => 'required|email|unique:users,email',
            'citizen_identity_card' => 'nullable|array',
            'student_id_card' => 'nullable|array',
            'street' => 'nullable|string',
            'province' => 'nullable|string',
            'district' => 'nullable|string',
            'ward' => 'nullable|string',
            'address_detail' => 'nullable|string',
            'phone' => 'required|string',
            'status' => 'nullable|string|in:active,inactive,banned,deleted',
        ], [
            'role_id.required' => 'Role id không được để trống.',
            'role_id.integer' => 'Role id phải là số nguyên.',
            'role_id.exists' => 'Role id không tồn tại.',
            'fullname.required' => 'Fullname không được để trống.',
            'fullname.string' => 'Fullname phải là chuỗi.',
            'status.in' => 'Status phải là active, inactive, banned hoặc deleted.',
            'email.required' => 'Email không được để trống.',
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email đã tồn tại.',
            'phone.required' => 'Số điện thoại không được để trống.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }

        try {
            $password = md5(uniqid());

            if ($request->role_id == 2) {
                $validator2 = Validator::make($request->all(), [
                    'student_id_card' => 'required|array',
                    'student_id_card.student_name' => 'required|string',
                    'student_id_card.student_code' => 'required|string',
                    'student_id_card.student_card_expired' => 'required|date',
                    'student_id_card.place_of_study' => 'required|string',
                ], [
                    'student_id_card.required' => 'student_id_card không được để trống.',
                    'student_id_card.array' => 'student_id_card phải là dạng mảng.',
                    'student_id_card.student_name.required' => 'Tên sinh viên không được để trống.',
                    'student_id_card.student_name.string' => 'Tên sinh viên phải là chuỗi.',
                    'student_id_card.student_code.required' => 'Mã số sinh viên không được để trống.',
                    'student_id_card.student_code.string' => 'Mã số sinh viên phải là chuỗi.',
                    'student_id_card.student_card_expired.required' => 'Ngày hết hạn không được để trống.',
                    'student_id_card.student_card_expired.date' => 'Ngày hết hạn phải là ngày.',
                    'student_id_card.place_of_study.required' => 'Tên trường không được để trống.',
                    'student_id_card.school_name.string' => 'Tên trường phải là chuỗi.',
                ]);

                if ($validator2->fails()) {
                    return response()->json([
                        "status" => false,
                        "message" => "Validation error",
                        "errors" => $validator2->errors()
                    ], 400);
                }
            }

            if ($request->has('citizen_identity_card')) {
                $validator3 = Validator::make($request->all(), [
                    'citizen_identity_card' => 'required|array',
                    'citizen_identity_card.citizen_name' => 'required|string',
                    'citizen_identity_card.citizen_code' => 'required|string',
                    'citizen_identity_card.date_of_issue' => 'required|date',
                    'citizen_identity_card.place_of_issue' => 'required|string',
                ], [
                    'citizen_identity_card.required' => 'citizen_identity_card không được để trống.',
                    'citizen_identity_card.array' => 'citizen_identity_card phải là dạng mảng.',
                    'citizen_identity_card.citizen_name.required' => 'Tên công dân không được để trống.',
                    'citizen_identity_card.citizen_name.string' => 'Tên công dân phải là chuỗi.',
                    'citizen_identity_card.citizen_code.required' => 'Mã CCCD không được để trống.',
                    'citizen_identity_card.citizen_code.string' => 'Mã CCCD phải là chuỗi.',
                    'citizen_identity_card.date_of_issue.required' => 'Ngày cấp không được để trống.',
                    'citizen_identity_card.date_of_issue.date' => 'Ngày cấp phải là ngày.',
                    'citizen_identity_card.place_of_issue.required' => 'Nơi cấp không được để trống.',
                    'citizen_identity_card.place_of_issue.string' => 'Nơi cấp phải là chuỗi.',
                ]);

                if ($validator3->fails()) {
                    return response()->json([
                        "status" => false,
                        "message" => "Validation error",
                        "errors" => $validator3->errors()
                    ], 400);
                }
            }

            $user = User::create(array_merge($validator->validated(), [
                'password' => $password,
                'user_verified_at' => $request->has('citizen_identity_card') ? now() : null,
                'has_wallet' => $request->has('citizen_identity_card') ? true : false,
                'email_verified_at' => $request->has('citizen_identity_card') || $request->has('student_id_card') ? now() : null,
            ]));

            if ($request->has('citizen_identity_card')) {
                $user->createWallet();
            }

            // Gửi email thông báo tạo tài khoản thành công

            try {
                Mail::to($user->email)->send(new InfomationAccount($user, $password));
                return response()->json([
                    "status" => true,
                    "message" => "Create user successfully!"
                ], 201);
            } catch (\Exception $err) {
                $user->delete();
                return [
                    'status' => false,
                    'message' => 'Không thể gửi email xác nhận, vui lòng thử lại.',
                    'errors' => $err->getMessage()
                ];
            }
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => "Create user failed!",
                "errors" => $th->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|min:1|exists:users,id',
        ], [
            'id.required' => 'Id không được để trống.',
            'id.exists' => 'Id không tồn tại.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }

        $user = User::with(['role', 'province', 'district', 'ward'])->find($id);

        return response()->json([
            "status" => true,
            "message" => "Get user successfully!",
            "data" => array_merge($user->toArray(), [
                'id' => $user->id,
            ]),
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make(array_merge($request->all(), [
            'id' => $id
        ]), [
            'id' => 'required|string|exists:users,id',
            'role_id' => 'nullable|integer|exists:roles,id',
            'avatar' => 'nullable|string',
            'fullname' => 'required|string',
            'job' => 'nullable|string',
            'story' => 'nullable|string',
            'gender' => 'nullable|string|in:male,female,other',
            'dob' => 'nullable|date',
            'citizen_identity_card' => 'nullable|array',
            'student_id_card' => 'nullable|array',
            'street' => 'nullable|string',
            'province' => 'nullable|string',
            'district' => 'nullable|string',
            'ward' => 'nullable|string',
            'address_detail' => 'nullable|string',
            'phone' => 'required|string',
            'status' => 'nullable|string|in:active,inactive,banned,deleted',
        ], [
            'id.required' => 'Id không được để trống.',
            'id.exists' => 'Id không tồn tại.',
            'role_id.required' => 'Role id không được để trống.',
            'role_id.integer' => 'Role id phải là số nguyên.',
            'role_id.exists' => 'Role id không tồn tại.',
            'fullname.required' => 'Fullname không được để trống.',
            'fullname.string' => 'Fullname phải là chuỗi.',
            'status.in' => 'Status phải là active, inactive, banned hoặc deleted.',
            'phone.required' => 'Số điện thoại không được để trống.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }

        try {
            $user = User::find($id);

            if ($request->role_id == 2) {
                $validator2 = Validator::make($request->all(), [
                    'student_id_card' => 'required|array',
                    'student_id_card.student_name' => 'required|string',
                    'student_id_card.student_code' => 'required|string',
                    'student_id_card.student_card_expired' => 'required|date',
                    'student_id_card.place_of_study' => 'required|string',
                ], [
                    'student_id_card.required' => 'student_id_card không được để trống.',
                    'student_id_card.array' => 'student_id_card phải là dạng mảng.',
                    'student_id_card.student_name.required' => 'Tên sinh viên không được để trống.',
                    'student_id_card.student_name.string' => 'Tên sinh viên phải là chuỗi.',
                    'student_id_card.student_code.required' => 'Mã số sinh viên không được để trống.',
                    'student_id_card.student_code.string' => 'Mã số sinh viên phải là chuỗi.',
                    'student_id_card.student_card_expired.required' => 'Ngày hết hạn không được để trống.',
                    'student_id_card.student_card_expired.date' => 'Ngày hết hạn phải là ngày.',
                    'student_id_card.place_of_study.required' => 'Tên trường không được để trống.',
                    'student_id_card.school_name.string' => 'Tên trường phải là chuỗi.',
                ]);

                if ($validator2->fails()) {
                    return response()->json([
                        "status" => false,
                        "message" => "Validation error",
                        "errors" => $validator2->errors()
                    ], 400);
                }
            }

            if ($request->has('citizen_identity_card')) {
                $validator3 = Validator::make($request->all(), [
                    'citizen_identity_card' => 'required|array',
                    'citizen_identity_card.citizen_name' => 'required|string',
                    'citizen_identity_card.citizen_code' => 'required|string',
                    'citizen_identity_card.date_of_issue' => 'required|date',
                    'citizen_identity_card.place_of_issue' => 'required|string',
                ], [
                    'citizen_identity_card.required' => 'citizen_identity_card không được để trống.',
                    'citizen_identity_card.array' => 'citizen_identity_card phải là dạng mảng.',
                    'citizen_identity_card.citizen_name.required' => 'Tên công dân không được để trống.',
                    'citizen_identity_card.citizen_name.string' => 'Tên công dân phải là chuỗi.',
                    'citizen_identity_card.citizen_code.required' => 'Mã CCCD không được để trống.',
                    'citizen_identity_card.citizen_code.string' => 'Mã CCCD phải là chuỗi.',
                    'citizen_identity_card.date_of_issue.required' => 'Ngày cấp không được để trống.',
                    'citizen_identity_card.date_of_issue.date' => 'Ngày cấp phải là ngày.',
                    'citizen_identity_card.place_of_issue.required' => 'Nơi cấp không được để trống.',
                    'citizen_identity_card.place_of_issue.string' => 'Nơi cấp phải là chuỗi.',
                ]);

                if ($validator3->fails()) {
                    return response()->json([
                        "status" => false,
                        "message" => "Validation error",
                        "errors" => $validator3->errors()
                    ], 400);
                }
            }

            $user->update(array_merge($validator->validated(), [
                'user_verified_at' => $request->has('citizen_identity_card') ? now() : null,
                'has_wallet' => $request->has('citizen_identity_card') ? true : false,
            ]));

            if ($user->email_verified_at == null && ($request->has('citizen_identity_card') || $request->has('student_id_card'))) {
                $user->update(['email_verified_at' => now()]);
            }

            if ($request->has('citizen_identity_card') && !$user->wallet) {
                $user->createWallet();
            }

            return response()->json([
                "status" => true,
                "message" => "Update user successfully!"
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => "Update user failed!",
                "errors" => $th->getMessage()
            ], 500);
        }
    }

    public function destroy(User $user)
    {
        //
    }
}

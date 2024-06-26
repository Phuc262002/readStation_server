<?php

namespace App\Http\Controllers\Api\PayOS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/api/v1/general/check-citizen',
    operationId: 'checkCCCD',
    tags: ['General / Check Citizen'],
    summary: 'Check CCCD',
    description: 'Check CCCD',
    security: [
        ['bearerAuth' => []]
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['legal_id', 'legal_name'],
            properties: [
                new OA\Property(property: 'legal_id', type: 'string', description: 'Legal ID'),
                new OA\Property(property: 'legal_name', type: 'string', description: 'Legal Name'),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Successful operation'
        ),
        new OA\Response(
            response: 400,
            description: 'Bad request'
        )
    ]
)]

class CheckCCCDController extends Controller
{
    public function checkCCCD(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'legal_id' => 'required|string',
            'legal_name' => 'required|string',
        ], [
            'legal_id.required' => 'Mã số CCCD là bắt buộc.',
            'legal_name.required' => 'Tên chủ thẻ là bắt buộc.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "message" => $validator->errors()->first(),
            ], 400);
        }

        $url = env('PAYOS_URL_CCCD');
        $token = DB::table('payos')->first()->token;

        $cccd = [
            "legal_id" => $request->legal_id,
            "legal_name" => $request->legal_name,
        ];

        $data = json_encode($cccd);

        try {
            $ch = curl_init($url);

            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token,
            ]);

            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Get HTTP status code

            if ($http_code == 401) {
                // If unauthorized, attempt to login again
                $loginResponse = $this->loginPayOS();

                // Check if login was successful
                if ($loginResponse->getStatusCode() == 200) {
                    // Retry checkCCCD with new token
                    $token = DB::table('payos')->first()->token;
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        'Content-Type: application/json',
                        'Authorization: Bearer ' . $token,
                    ]);
                    $response = curl_exec($ch);
                } else {
                    // Return error if login failed
                    return response()->json([
                        "status" => false,
                        "message" => "Failed to authenticate.",
                    ], 401);
                }
            }

            curl_close($ch);

            return response()->json([
                "status" => json_decode($response)->code == 00 ? true : false,
                "message" => json_decode($response)->desc,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage(),
            ]);
        }
    }

    public function checkCCCDUser($legal_id, $legal_name)
    {
        $url = env('PAYOS_URL_CCCD');
        $token = DB::table('payos')->first()->token;

        $cccd = [
            "legal_id" => $legal_id,
            "legal_name" => $legal_name,
        ];

        $data = json_encode($cccd);

        try {
            $ch = curl_init($url);

            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token,
            ]);

            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Get HTTP status code

            if ($http_code == 401) {
                // If unauthorized, attempt to login again
                $loginResponse = $this->loginPayOS();

                // Check if login was successful
                if ($loginResponse->getStatusCode() == 200) {
                    // Retry checkCCCD with new token
                    $token = DB::table('payos')->first()->token;
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        'Content-Type: application/json',
                        'Authorization: Bearer ' . $token,
                    ]);
                    $response = curl_exec($ch);
                } else {
                    // Return error if login failed
                    return false;
                }
            }

            curl_close($ch);

            return json_decode($response)->code == 00 ? true : false;
        } catch (\Throwable $th) {
            return false;
        }
    }

    public function loginPayOS()
    {
        $url = env('PAYOS_URL_LOGIN');
        $email = env('PAYOS_EMAIL');
        $password = env('PAYOS_PASSWORD');

        // Check if environment variables are set
        if (!$url || !$email || !$password) {
            return response()->json([
                "status" => "error",
                "message" => "Environment variables not configured properly.",
            ], 500);
        }

        $data = [
            "email" => $email,
            "password" => $password,
        ];

        $data = json_encode($data);

        try {
            $ch = curl_init($url);

            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data),
            ]);

            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                $error_msg = curl_error($ch);
                curl_close($ch);
                return response()->json([
                    "status" => "error",
                    "message" => $error_msg,
                ], 500);
            }

            curl_close($ch);

            $response = json_decode($response);

            // Update or insert token into database
            DB::table('payos')->updateOrInsert(['id' => 1], ['token' => $response->data->token]);

            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage(),
            ]);
        }
    }
}

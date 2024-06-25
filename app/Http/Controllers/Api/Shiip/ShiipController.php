<?php

namespace App\Http\Controllers\Api\Shiip;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\Province;
use App\Models\Ward;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/public/shiip/province',
    tags: ['Public / Shiip'],
    operationId: 'getProvince public',
    summary: 'Get all province',
    description: 'Get all province',
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get all province successfully!',
        ),
        new OA\Response(
            response: 400,
            description: 'Validation error',
        ),
    ],
)]

#[OA\Get(
    path: '/api/v1/public/shiip/district?province_id={province_id}',
    tags: ['Public / Shiip'],
    operationId: 'getDistrict public',
    summary: 'Get all district',
    description: 'Get all district',
    parameters: [
        new OA\Parameter(
            name: 'province_id',
            in: 'query',
            required: false,
            description: 'ID của tỉnh thành',
            schema: new OA\Schema(type: 'integer')
        )
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get all district successfully!',
        ),
        new OA\Response(
            response: 400,
            description: 'Validation error',
        ),
    ],
)]

#[OA\Get(
    path: '/api/v1/public/shiip/ward?district_id={district_id}',
    tags: ['Public / Shiip'],
    operationId: 'getWard public',
    summary: 'Get all ward',
    description: 'Get all ward',
    parameters: [
        new OA\Parameter(
            name: 'district_id',
            in: 'query',
            required: false,
            description: 'ID của quận huyện',
            schema: new OA\Schema(type: 'integer')
        )
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get all ward successfully!',
        ),
        new OA\Response(
            response: 400,
            description: 'Validation error',
        ),
    ],
)]



class ShiipController extends Controller
{
    public function getProvince()
    {
        try {
            $province = Province::all();
            return response()->json([
                'status' => true,
                'message' => 'Success',
                'data' => $province
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Error',
                'errors' => $th->getMessage(),
            ]);
        }
    }

    public function getDistrict(Request $request)
    {
        try {
            $province_id = $request->province_id;
            if ($province_id) {
                $district = District::where('ProvinceID', $province_id)->get();
            } else {
                $district = District::all();
            }
            return response()->json([
                'status' => true,
                'message' => 'Success',
                'data' => $district
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Error',
                'errors' => $th->getMessage(),
            ]);
        }
    }

    public function getWard(Request $request)
    {
        try {
            $district_id = $request->district_id;
            if ($district_id) {
                $ward = Ward::where('DistrictID', $district_id)->get();
            } else {
                $ward = Ward::all();
            }
            return response()->json([
                'status' => true,
                'message' => 'Success',
                'data' => $ward
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Error',
                'errors' => $th->getMessage(),
            ]);
        }
    }

    public function createProvince(Request $request)
    {
        try {
            foreach ($request->data as $province) {
                Province::create([
                    'id' => $province['ProvinceID'],
                    'ProvinceName' => $province['ProvinceName'],
                    'NameExtension' => isset($province['NameExtension']) ? $province['NameExtension'] : []
                ]);
            }
            return response()->json([
                'status' => true,
                'message' => 'Success',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Error',
                'errors' => $th->getMessage(),
            ]);
        }
    }

    public function createDistrict(Request $request)
    {
        try {
            foreach ($request->data as $district) {
                District::create([
                    'id' => $district['DistrictID'],
                    'DistrictName' => $district['DistrictName'],
                    'ProvinceID' => $district['ProvinceID'],
                    'NameExtension' => isset($district['NameExtension']) ? $district['NameExtension'] : []
                ]);
            }
            return response()->json([
                'status' => true,
                'message' => 'Success',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Error',
                'errors' => $th->getMessage(),
            ]);
        }
    }

    public function createWard(Request $request)
    {
        try {
            foreach ($request->data as $ward) {
                Ward::create([
                    'id' => $ward['WardCode'],
                    'WardName' => $ward['WardName'],
                    'DistrictID' => $ward['DistrictID'],
                    'NameExtension' => isset($ward['NameExtension']) ? $ward['NameExtension'] : []
                ]);
            }
            return response()->json([
                'status' => true,
                'message' => 'Success',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Error',
                'errors' => $th->getMessage(),
            ]);
        }
    }

}

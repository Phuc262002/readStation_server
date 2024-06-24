<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\ShippingMethod;
use Illuminate\Http\Request;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/admin/public/shipping-methods',
    tags: ['Public / Shipping Method'],
    operationId: 'getAllShippingMethodsPublic',
    summary: 'Get all ShippingMethods',
    description: 'Get all ShippingMethods',
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get all ShippingMethods successfully',
        ),
        new OA\Response(
            response: 400,
            description: 'Validation error',
        ),
    ],
)]


class ShippingMethodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $shippingMethods = ShippingMethod::all();

        return response()->json([
            'success' => true,
            'data' => $shippingMethods,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(shippingMethod $shippingMethod)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(shippingMethod $shippingMethod)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, shippingMethod $shippingMethod)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(shippingMethod $shippingMethod)
    {
        //
    }
}

<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PayOS\PayOS;

class WalletController extends Controller
{
    private string $payOSClientId;
    private string $payOSApiKey;
    private string $payOSChecksumKey;

    public function __construct()
    {
        $this->payOSClientId = env("PAYOS_CLIENT_ID");
        $this->payOSApiKey = env("PAYOS_API_KEY");
        $this->payOSChecksumKey = env("PAYOS_CHECKSUM_KEY");
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function storeDeposit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:10000',
            'description' => 'required|string'
        ], [
            'amount.required' => 'Vui lòng nhập số tiền',
            'amount.numeric' => 'Số tiền phải là số',
            'amount.min' => 'Số tiền tối thiểu là 10,000 VND',
            'description.required' => 'Vui lòng nhập mô tả'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 1,
                'message' => $validator->errors()->first(),
                'data' => null
            ]);
        }

        $wallet = Wallet::where('user_id', auth()->user()->id)->first();

        if (!$wallet) {
            return response()->json([
                'error' => 1,
                'message' => 'Tài khoản không tồn tại',
                'data' => null
            ]);
        }

        $body = $request->input();

        $body["amount"] = intval($body["amount"]);

        $payOS = new PayOS($this->payOSClientId, $this->payOSApiKey, $this->payOSChecksumKey);

        try {
            $response = $payOS->createPaymentLink($body);
            return response()->json([
                "error" => 0,
                "message" => "Success",
                "data" => $response["checkoutUrl"]
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                "error" => $th->getCode(),
                "message" => $th->getMessage(),
                "data" => null
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
    }

    /**
     * Display the specified resource.
     */
    public function show(Wallet $wallet)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Wallet $wallet)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Wallet $wallet)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Wallet $wallet)
    {
        //
    }
}

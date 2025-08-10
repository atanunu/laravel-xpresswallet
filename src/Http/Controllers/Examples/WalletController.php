<?php

namespace Atanunu\XpressWallet\Http\Controllers\Examples;

use Atanunu\XpressWallet\Facades\XpressWallet;
use Illuminate\Routing\Controller;

class WalletController extends Controller
{
    public function index(): \Illuminate\Http\JsonResponse
    {
        return response()->json(XpressWallet::wallets()->all());
    }

    public function create(): \Illuminate\Http\JsonResponse
    {
        $payload = request()->all();

        return response()->json(XpressWallet::wallets()->create($payload));
    }
}

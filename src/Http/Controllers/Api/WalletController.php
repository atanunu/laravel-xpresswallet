<?php

namespace Atanunu\XpressWallet\Http\Controllers\Api;

use Atanunu\XpressWallet\Facades\XpressWallet;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Atanunu\XpressWallet\Http\Requests\WalletCreateRequest;
use Atanunu\XpressWallet\Http\Requests\WalletAdjustRequest;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    /**
     * List all wallets (optionally paginated upstream if API supports query params added later).
     */
    public function index(): JsonResponse
    {
        return response()->json(XpressWallet::wallets()->all());
    }

    /**
     * Retrieve wallets belonging to a specific customer.
     *
     * @param string $customerId Upstream customer identifier.
     */
    public function customer(string $customerId): JsonResponse
    {
        return response()->json(XpressWallet::wallets()->customerWallet($customerId));
    }

    /**
     * Create a wallet for a customer.
     * Body validated by WalletCreateRequest (needs customer_id or customer_identifier, currency, type).
     */
    public function store(WalletCreateRequest $request): JsonResponse
    {
        return response()->json(XpressWallet::wallets()->create($request->validated()));
    }

    /**
     * Credit a wallet (increase balance). Uses WalletAdjustRequest for amount + metadata.
     */
    public function credit(WalletAdjustRequest $request): JsonResponse
    {
        return response()->json(XpressWallet::wallets()->credit($request->validated()));
    }

    /**
     * Debit a wallet (decrease balance). Validated amount.
     */
    public function debit(WalletAdjustRequest $request): JsonResponse
    {
        return response()->json(XpressWallet::wallets()->debit($request->validated()));
    }

    /**
     * Freeze (disable) all activity for a customer's wallet(s) based on customer id.
     */
    public function freeze(string $customerId): JsonResponse
    {
        return response()->json(XpressWallet::wallets()->freeze($customerId));
    }

    /**
     * Unfreeze (re-enable) wallet(s) for a customer.
     */
    public function unfreeze(string $customerId): JsonResponse
    {
        return response()->json(XpressWallet::wallets()->unfreeze($customerId));
    }

    /**
     * Batch credit multiple wallets.
     * Body structure defined by WalletAdjustRequest (expects array of operations upstream conventions).
     */
    public function batchCredit(WalletAdjustRequest $request): JsonResponse
    {
        return response()->json(XpressWallet::wallets()->batchCredit($request->validated()));
    }

    /**
     * Batch debit multiple wallets.
     */
    public function batchDebit(WalletAdjustRequest $request): JsonResponse
    {
        return response()->json(XpressWallet::wallets()->batchDebit($request->validated()));
    }

    /**
     * Batch credit using customer identifiers list.
     */
    public function customerBatchCredit(WalletAdjustRequest $request): JsonResponse
    {
        return response()->json(XpressWallet::wallets()->customerBatchCredit($request->validated()));
    }

    /**
     * Fund sandbox merchant wallet (non-production utility). Requires amount integer in body.
     */
    public function fundSandbox(Request $request): JsonResponse
    {
        $amount = (int) $request->input('amount');
        return response()->json(XpressWallet::wallets()->fundMerchantSandboxWallet($amount));
    }
}

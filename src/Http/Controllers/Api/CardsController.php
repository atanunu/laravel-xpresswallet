<?php

namespace Atanunu\XpressWallet\Http\Controllers\Api;

use Atanunu\XpressWallet\Facades\XpressWallet;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Atanunu\XpressWallet\Http\Requests\CardSetupRequest;
use Atanunu\XpressWallet\Http\Requests\CardActivateRequest;
use Atanunu\XpressWallet\Http\Requests\CardFundRequest;
use Illuminate\Http\Request;

class CardsController extends Controller
{
    /**
     * List cards with optional filters (page, status).
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['page','status']);
        return response()->json(XpressWallet::cards()->all(array_filter($filters)));
    }

    /**
     * Initiate card setup (virtual or physical).
     */
    public function setup(CardSetupRequest $request): JsonResponse
    {
        return response()->json(XpressWallet::cards()->setup($request->validated()));
    }

    /**
     * Activate a card using activation code.
     */
    public function activate(CardActivateRequest $request): JsonResponse
    {
        return response()->json(XpressWallet::cards()->activate($request->validated()));
    }

    /**
     * Retrieve balance details for a card (requires customerId & cardId).
     */
    public function balance(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'customerId' => 'required|string',
            'cardId' => 'required|string',
        ]);
        return response()->json(XpressWallet::cards()->balance($filters));
    }

    /**
     * Fund a card with amount/currency.
     */
    public function fund(CardFundRequest $request): JsonResponse
    {
        return response()->json(XpressWallet::cards()->fund($request->validated()));
    }
}

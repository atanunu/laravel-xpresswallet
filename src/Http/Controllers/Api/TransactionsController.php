<?php

namespace Atanunu\XpressWallet\Http\Controllers\Api;

use Atanunu\XpressWallet\Facades\XpressWallet;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionsController extends Controller
{
    /**
     * List merchant transactions with optional filters.
     *
     * Query Parameters (all optional unless upstream requires):
     *  - page (int)
     *  - from (date/datetime)
     *  - to (date/datetime)
     *  - status (string)
     *  - type (CREDIT|DEBIT|ALL)
     */
    public function merchant(Request $request): JsonResponse
    {
        $filters = $request->only(['page','from','to','status','type']);
        return response()->json(XpressWallet::transactions()->merchant(array_filter($filters, fn($v) => $v !== null && $v !== '')));
    }

    /**
     * Retrieve a single transaction by reference.
     */
    public function show(string $reference): JsonResponse
    {
        return response()->json(XpressWallet::transactions()->details($reference));
    }
}

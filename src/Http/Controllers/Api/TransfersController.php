<?php

namespace Atanunu\XpressWallet\Http\Controllers\Api;

use Atanunu\XpressWallet\Facades\XpressWallet;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Atanunu\XpressWallet\Http\Requests\TransferBankRequest;
use Atanunu\XpressWallet\Http\Requests\TransferBankCustomerRequest;
use Atanunu\XpressWallet\Http\Requests\TransferBankBatchRequest;
use Atanunu\XpressWallet\Http\Requests\TransferWalletRequest;

class TransfersController extends Controller
{
    /**
     * List supported banks for bank transfers.
     */
    public function banks(): JsonResponse
    {
        return response()->json(XpressWallet::transfers()->banks());
    }

    /**
     * Resolve account holder details given sort code & account number.
     * Query: sort_code, account_number (both required).
     */
    public function accountDetails(Request $request): JsonResponse
    {
        $data = $request->validate([
            'sort_code' => 'required|string',
            'account_number' => 'required|string',
        ]);
        return response()->json(XpressWallet::transfers()->accountDetails($data['sort_code'], $data['account_number']));
    }

    /**
     * Initiate a single bank transfer.
     * Body validated by TransferBankRequest.
     */
    public function bank(TransferBankRequest $request): JsonResponse
    {
        return response()->json(XpressWallet::transfers()->bank($request->validated()));
    }

    /**
     * Initiate a bank transfer for a specific customer (customer_id + bank details).
     */
    public function bankCustomer(TransferBankCustomerRequest $request): JsonResponse
    {
        return response()->json(XpressWallet::transfers()->bankCustomer($request->validated()));
    }

    /**
     * Perform multiple bank transfers in a single batch (1-100 entries).
     */
    public function bankBatch(TransferBankBatchRequest $request): JsonResponse
    {
        return response()->json(XpressWallet::transfers()->bankBatch($request->validated()));
    }

    /**
     * Wallet to wallet internal transfer.
     */
    public function wallet(TransferWalletRequest $request): JsonResponse
    {
        return response()->json(XpressWallet::transfers()->wallet($request->validated()));
    }
}

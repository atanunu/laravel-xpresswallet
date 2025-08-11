<?php

namespace Atanunu\XpressWallet\Http\Controllers\Api;

use Atanunu\XpressWallet\Facades\XpressWallet;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Atanunu\XpressWallet\Http\Requests\CustomerCreateRequest;
use Atanunu\XpressWallet\Http\Requests\CustomerUpdateRequest;

class CustomerController extends Controller
{
    /**
     * List customers.
     *
     * Query Parameters:
     *  - page (int, optional, default=1): Pagination page index.
     *
     * Returns raw upstream API JSON (paginated list + meta if provided).
     */
    public function index(Request $request): JsonResponse
    {
        $page = (int) $request->query('page', 1);
        return response()->json(XpressWallet::customers()->all($page));
    }

    /**
     * Create a new customer.
     *
     * Body: validated by CustomerCreateRequest (first_name, last_name, email, phone, optional metadata etc.).
     */
    public function store(CustomerCreateRequest $request): JsonResponse
    {
        return response()->json(XpressWallet::customers()->create($request->validated()));
    }

    /**
     * Retrieve a customer by internal identifier.
     */
    public function show(string $id): JsonResponse
    {
        return response()->json(XpressWallet::customers()->findById($id));
    }

    /**
     * Find a customer by phone number.
     *
     * Query Parameters:
     *  - phone (string, required)
     */
    public function findByPhone(Request $request): JsonResponse
    {
        $phone = (string) $request->query('phone');
        return response()->json(XpressWallet::customers()->findByPhone($phone));
    }

    /**
     * Update an existing customer (partial update allowed).
     *
     * Body: validated by CustomerUpdateRequest ("sometimes" rules permit sparse payloads).
     */
    public function update(CustomerUpdateRequest $request, string $id): JsonResponse
    {
        return response()->json(XpressWallet::customers()->update($id, $request->validated()));
    }
}

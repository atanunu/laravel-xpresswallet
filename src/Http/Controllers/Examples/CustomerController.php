<?php

namespace Atanunu\XpressWallet\Http\Controllers\Examples;

use Illuminate\Routing\Controller;
use Atanunu\XpressWallet\Facades\XpressWallet;

class CustomerController extends Controller
{
    public function index(): \Illuminate\Http\JsonResponse
    {
        $page = (int) request('page', 1);
        return response()->json(XpressWallet::customers()->all($page));
    }

    public function show(string $id): \Illuminate\Http\JsonResponse
    {
        return response()->json(XpressWallet::customers()->findById($id));
    }
}
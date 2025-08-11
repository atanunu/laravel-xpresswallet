<?php

namespace Atanunu\XpressWallet\Http\Controllers\Api;

use Atanunu\XpressWallet\Facades\XpressWallet;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Atanunu\XpressWallet\Http\Requests\MerchantCompleteRegistrationRequest;
use Atanunu\XpressWallet\Http\Requests\MerchantChangePasswordRequest;

class MerchantController extends Controller
{
    /**
     * Request a merchant password change (sends reset code to email).
     */
    public function requestPasswordChange(Request $request): JsonResponse
    {
        $data = $request->validate(['email' => 'required|email']);
        return response()->json(XpressWallet::merchant()->requestPasswordChange($data['email']));
    }

    /**
     * Change merchant password using reset code & new password.
     */
    public function changePassword(MerchantChangePasswordRequest $request): JsonResponse
    {
        $data = $request->validated();
        return response()->json(XpressWallet::merchant()->changePassword($data['reset_code'], $data['password']));
    }

    /**
     * Verify merchant using verification code.
     */
    public function verify(Request $request): JsonResponse
    {
        $data = $request->validate(['code' => 'required|string']);
        return response()->json(XpressWallet::merchant()->verify($data['code']));
    }

    /**
     * Resend verification code (optional email override).
     */
    public function resendVerification(Request $request): JsonResponse
    {
        $email = $request->input('email');
        return response()->json(XpressWallet::merchant()->resendVerification($email));
    }

    /**
     * Resend activation code (optional email override).
     */
    public function resendActivation(Request $request): JsonResponse
    {
        $email = $request->input('email');
        return response()->json(XpressWallet::merchant()->resendActivation($email));
    }

    /**
     * Complete merchant registration with business / contact details.
     */
    public function completeRegistration(MerchantCompleteRegistrationRequest $request): JsonResponse
    {
        return response()->json(XpressWallet::merchant()->completeRegistration($request->validated()));
    }

    /**
     * Retrieve current merchant profile.
     */
    public function profile(): JsonResponse
    {
        return response()->json(XpressWallet::merchant()->profile());
    }

    /**
     * List current access keys (public/secret pairs) for merchant.
     */
    public function accessKeys(): JsonResponse
    {
        return response()->json(XpressWallet::merchant()->accessKeys());
    }

    /**
     * Generate new access keys (rotates credentials).
     */
    public function generateAccessKeys(): JsonResponse
    {
        return response()->json(XpressWallet::merchant()->generateAccessKeys());
    }

    /**
     * Return current account mode (LIVE or SANDBOX).
     */
    public function accountMode(): JsonResponse
    {
        return response()->json(XpressWallet::merchant()->accountMode());
    }

    /**
     * Switch account mode between LIVE and SANDBOX.
     */
    public function switchAccountMode(Request $request): JsonResponse
    {
        $data = $request->validate(['mode' => 'required|in:LIVE,SANDBOX']);
        return response()->json(XpressWallet::merchant()->switchAccountMode($data['mode']));
    }

    /**
     * Retrieve summary metrics snapshot for merchant.
     */
    public function summary(): JsonResponse
    {
        return response()->json(XpressWallet::merchant()->summary());
    }

    /**
     * Retrieve merchant primary wallet details.
     */
    public function wallet(): JsonResponse
    {
        return response()->json(XpressWallet::merchant()->wallet());
    }
}

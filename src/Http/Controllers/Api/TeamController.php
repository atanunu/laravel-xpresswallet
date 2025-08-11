<?php

namespace Atanunu\XpressWallet\Http\Controllers\Api;

use Atanunu\XpressWallet\Facades\XpressWallet;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Atanunu\XpressWallet\Http\Requests\TeamInviteRequest;
use Atanunu\XpressWallet\Http\Requests\TeamResendInvitationRequest;
use Atanunu\XpressWallet\Http\Requests\TeamAcceptInvitationRequest;

class TeamController extends Controller
{
    /**
     * List pending invitations for the current merchant/team context.
     */
    public function invitations(): JsonResponse
    {
        return response()->json(XpressWallet::team()->invitations());
    }

    /**
     * Invite a new team member (email + role).
     */
    public function invite(TeamInviteRequest $request): JsonResponse
    {
        return response()->json(XpressWallet::team()->invite($request->validated()));
    }

    /**
     * Resend an existing invitation by invitation_id.
     */
    public function resendInvitation(TeamResendInvitationRequest $request): JsonResponse
    {
        return response()->json(XpressWallet::team()->resendInvitation($request->validated()));
    }

    /**
     * Accept an invitation (invitation_id + code).
     */
    public function acceptInvitation(TeamAcceptInvitationRequest $request): JsonResponse
    {
        return response()->json(XpressWallet::team()->acceptInvitation($request->validated()));
    }

    /**
     * List team members.
     */
    public function members(): JsonResponse
    {
        return response()->json(XpressWallet::team()->members());
    }

    /**
     * List merchants accessible to current user (for switching context).
     */
    public function merchants(): JsonResponse
    {
        return response()->json(XpressWallet::team()->merchants());
    }

    /**
     * Switch active merchant context.
     */
    public function switchMerchant(Request $request): JsonResponse
    {
        $data = $request->validate(['merchant_id' => 'required|string']);
        return response()->json(XpressWallet::team()->switchMerchant($data['merchant_id']));
    }

    /**
     * List permission definitions.
     */
    public function permissions(): JsonResponse
    {
        return response()->json(XpressWallet::team()->permissions());
    }

    /**
     * List role definitions.
     */
    public function roles(): JsonResponse
    {
        return response()->json(XpressWallet::team()->roles());
    }
}

<?php

namespace Atanunu\XpressWallet\Routes;

use Illuminate\Support\Facades\Route;
use Atanunu\XpressWallet\Http\Controllers\Api\CustomerController;
use Atanunu\XpressWallet\Http\Controllers\Api\WalletController;
use Atanunu\XpressWallet\Http\Controllers\Api\TransactionsController;
use Atanunu\XpressWallet\Http\Controllers\Api\TransfersController;
use Atanunu\XpressWallet\Http\Controllers\Api\CardsController;
use Atanunu\XpressWallet\Http\Controllers\Api\MerchantController;
use Atanunu\XpressWallet\Http\Controllers\Api\TeamController;

if (! function_exists(__NAMESPACE__.'\\routes')) {
    /**
     * Register package API proxy routes if enabled via config.
     */
    function routes(): void
    {
        $cfg = config('xpresswallet.routes');
        if (! ($cfg['enabled'] ?? false)) {
            return; // feature disabled
        }

        Route::group([
            'prefix' => $cfg['prefix'] ?? 'xpresswallet',
            'middleware' => $cfg['middleware'] ?? ['api'],
            'as' => 'xpresswallet.',
        ], function () {
            // ------------------------------------------------------------------
            // Customers: CRUD-style operations for end users of the wallet system
            // ------------------------------------------------------------------
            Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
            Route::post('customers', [CustomerController::class, 'store'])->name('customers.store');
            Route::get('customers/{id}', [CustomerController::class, 'show'])->name('customers.show');
            Route::get('customers/phone/{phone}', [CustomerController::class, 'findByPhone'])->name('customers.find-by-phone');
            Route::put('customers/{id}', [CustomerController::class, 'update'])->name('customers.update');

            // ------------------------------------------------------------------
            // Wallets: balance operations (credit/debit), freezing, batch actions
            // ------------------------------------------------------------------
            Route::get('wallets', [WalletController::class, 'index'])->name('wallets.index');
            Route::get('wallets/customer/{customerId}', [WalletController::class, 'customer'])->name('wallets.customer');
            Route::post('wallets', [WalletController::class, 'store'])->name('wallets.store');
            Route::post('wallets/{id}/credit', [WalletController::class, 'credit'])->name('wallets.credit');
            Route::post('wallets/{id}/debit', [WalletController::class, 'debit'])->name('wallets.debit');
            Route::post('wallets/{id}/freeze', [WalletController::class, 'freeze'])->name('wallets.freeze');
            Route::post('wallets/{id}/unfreeze', [WalletController::class, 'unfreeze'])->name('wallets.unfreeze');
            Route::post('wallets/batch/credit', [WalletController::class, 'batchCredit'])->name('wallets.batch.credit');
            Route::post('wallets/batch/debit', [WalletController::class, 'batchDebit'])->name('wallets.batch.debit');
            Route::post('wallets/customer/batch/credit', [WalletController::class, 'customerBatchCredit'])->name('wallets.customer.batch.credit');
            Route::post('wallets/fund-sandbox', [WalletController::class, 'fundSandbox'])->name('wallets.fund-sandbox');

            // ------------------------------------------------------------------
            // Transactions: merchant-wide transaction listing and detail lookup
            // ------------------------------------------------------------------
            Route::get('transactions', [TransactionsController::class, 'merchant'])->name('transactions.merchant');
            Route::get('transactions/{reference}', [TransactionsController::class, 'show'])->name('transactions.show');

            // ------------------------------------------------------------------
            // Transfers: bank & wallet transfers including batch processing
            // ------------------------------------------------------------------
            Route::get('transfers/banks', [TransfersController::class, 'banks'])->name('transfers.banks');
            Route::get('transfers/account-details', [TransfersController::class, 'accountDetails'])->name('transfers.account-details');
            Route::post('transfers/bank', [TransfersController::class, 'bank'])->name('transfers.bank');
            Route::post('transfers/bank/customer', [TransfersController::class, 'bankCustomer'])->name('transfers.bank.customer');
            Route::post('transfers/bank/batch', [TransfersController::class, 'bankBatch'])->name('transfers.bank.batch');
            Route::post('transfers/wallet', [TransfersController::class, 'wallet'])->name('transfers.wallet');

            // ------------------------------------------------------------------
            // Cards: issuance, activation, balance & funding operations
            // ------------------------------------------------------------------
            Route::get('cards', [CardsController::class, 'index'])->name('cards.index');
            Route::post('cards/setup', [CardsController::class, 'setup'])->name('cards.setup');
            Route::post('cards/activate', [CardsController::class, 'activate'])->name('cards.activate');
            Route::get('cards/{cardId}/balance', [CardsController::class, 'balance'])->name('cards.balance');
            Route::post('cards/fund', [CardsController::class, 'fund'])->name('cards.fund');

            // ------------------------------------------------------------------
            // Merchant: account lifecycle, profile, access key management, mode
            // ------------------------------------------------------------------
            Route::post('merchant/password/request-change', [MerchantController::class, 'requestPasswordChange'])->name('merchant.password.request-change');
            Route::post('merchant/password/change', [MerchantController::class, 'changePassword'])->name('merchant.password.change');
            Route::post('merchant/verify', [MerchantController::class, 'verify'])->name('merchant.verify');
            Route::post('merchant/resend-verification', [MerchantController::class, 'resendVerification'])->name('merchant.resend-verification');
            Route::post('merchant/resend-activation', [MerchantController::class, 'resendActivation'])->name('merchant.resend-activation');
            Route::post('merchant/complete-registration', [MerchantController::class, 'completeRegistration'])->name('merchant.complete-registration');
            Route::get('merchant/profile', [MerchantController::class, 'profile'])->name('merchant.profile');
            Route::get('merchant/access-keys', [MerchantController::class, 'accessKeys'])->name('merchant.access-keys');
            Route::post('merchant/access-keys/generate', [MerchantController::class, 'generateAccessKeys'])->name('merchant.access-keys.generate');
            Route::get('merchant/account-mode', [MerchantController::class, 'accountMode'])->name('merchant.account-mode');
            Route::post('merchant/account-mode/switch', [MerchantController::class, 'switchAccountMode'])->name('merchant.account-mode.switch');
            Route::get('merchant/summary', [MerchantController::class, 'summary'])->name('merchant.summary');
            Route::get('merchant/wallet', [MerchantController::class, 'wallet'])->name('merchant.wallet');

            // ------------------------------------------------------------------
            // Team: invitations, membership, merchant switching, RBAC metadata
            // ------------------------------------------------------------------
            Route::get('team/invitations', [TeamController::class, 'invitations'])->name('team.invitations');
            Route::post('team/invitations', [TeamController::class, 'invite'])->name('team.invitations.invite');
            Route::post('team/invitations/resend', [TeamController::class, 'resendInvitation'])->name('team.invitations.resend');
            Route::post('team/invitations/accept', [TeamController::class, 'acceptInvitation'])->name('team.invitations.accept');
            Route::get('team/members', [TeamController::class, 'members'])->name('team.members');
            Route::get('team/merchants', [TeamController::class, 'merchants'])->name('team.merchants');
            Route::post('team/merchants/switch', [TeamController::class, 'switchMerchant'])->name('team.merchants.switch');
            Route::get('team/permissions', [TeamController::class, 'permissions'])->name('team.permissions');
            Route::get('team/roles', [TeamController::class, 'roles'])->name('team.roles');
        });
    }
}

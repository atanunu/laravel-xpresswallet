# API Endpoints (Wrapped by the Package)

> All calls automatically attach `X-Access-Token` and `X-Refresh-Token` from the token store.

## Auth
- `POST auth/login` — `XpressWallet::login($email, $password)`
- `POST auth/refresh/token` — `XpressWallet::refresh()`

## Customers
- `GET customer?page={n}` — `XpressWallet::customers()->all($page)`
- `GET customer/{customerId}` — `XpressWallet::customers()->findById($customerId)`
- `GET customer/phone?phoneNumber=...` — `XpressWallet::customers()->findByPhone($phone)`
- `PUT customer/{customerId}` — `XpressWallet::customers()->update($customerId, $payload)`
- `POST customer` — `XpressWallet::customers()->create($payload)`

## Wallets
- `POST wallet` — create customer wallet — `wallets()->create($payload)`
- `GET wallet` — list wallets — `wallets()->all()`
- `GET wallet/customer?customerId=...` — `wallets()->customerWallet($customerId)`
- `POST wallet/credit` — credit a wallet — `wallets()->credit($payload)`
- `POST wallet/debit` — debit a wallet — `wallets()->debit($payload)`
- `POST wallet/close` — freeze — `wallets()->freeze($customerId)`
- `POST wallet/enable` — unfreeze — `wallets()->unfreeze($customerId)`
- `POST wallet/batch-credit-customer-wallet` — `wallets()->batchCredit($payload)`
- `POST wallet/batch-debit-customer-wallet` — `wallets()->batchDebit($payload)`
- `POST wallet/customer-batch-credit-customer-wallet` — `wallets()->customerBatchCredit($payload)`
- `POST merchant/fund-wallet` — sandbox fund — `wallets()->fundMerchantSandboxWallet($amount)`

## Transactions
- `GET merchant/transactions?{filters}` — `transactions()->merchant($filters)`  
  Filters: `page`, `type` (`CREDIT|DEBIT|ALL`), `status`, `category`, `search`
- `GET merchant/transaction/{reference}` — `transactions()->details($reference)`

## Merchant
- `POST merchant/request-merchant-password-change` — `merchant()->requestPasswordChange($email)`
- `POST merchant/change-merchant-password` — `merchant()->changePassword($resetCode, $password)`
- `POST merchant/verify` — `merchant()->verify($code)`
- `POST merchant/verify/resend` — `merchant()->resendVerification($email?)`
- `POST merchant/resend-activation-code` — `merchant()->resendActivation($email?)`
- `POST merchant/complete-merchant-registration` — `merchant()->completeRegistration($payload)`
- `GET merchant/profile` — `merchant()->profile()`
- `GET merchant/my-access-keys` — `merchant()->accessKeys()`
- `POST merchant/generate-access-keys` — `merchant()->generateAccessKeys()`
- `GET merchant/account-mode` — `merchant()->accountMode()`
- `PATCH merchant/account-mode` — `merchant()->switchAccountMode($mode)`
- `GET merchant` — `merchant()->summary()`
- `GET merchant/wallet` — `merchant()->wallet()`

## Transfers
- `GET transfer/banks` — `transfers()->banks()`
- `GET transfer/account/details?sortCode=&accountNumber=` — `transfers()->accountDetails($sortCode, $acct)`
- `POST transfer/bank` — `transfers()->bank($payload)`
- `POST transfer/bank/customer` — `transfers()->bankCustomer($payload)`
- `POST transfer/bank/batch` — `transfers()->bankBatch($payload)`
- `POST transfer/wallet` — `transfers()->wallet($payload)`

## Cards
- `POST card/setup` — `cards()->setup($payload)`
- `GET card` — `cards()->all($filters)`
- `POST card/activate` — `cards()->activate($payload)`
- `GET card/balance` — `cards()->balance($filters)`
- `POST card/fund` — `cards()->fund($payload)`

## Team
- `GET team/invitations` — `team()->invitations($query)`
- `POST team/invitations` — `team()->invite($payload)`
- `POST team/invitations/resend` — `team()->resendInvitation($payload)`
- `POST team/invitations/accept` — `team()->acceptInvitation($payload)`
- `GET team/members` — `team()->members()`
- `GET team/merchants` — `team()->merchants()`
- `POST team/merchants/switch` — `team()->switchMerchant($payload)`
- `GET team/permissions` — `team()->permissions()`
- `GET team/roles` — `team()->roles()`
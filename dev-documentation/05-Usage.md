# Usage

## Facade

```php
use Atanunu\XpressWallet\Facades\XpressWallet;

$customers = XpressWallet::customers()->all(1);
$wallet = XpressWallet::wallets()->create([
    'bvn' => '22181029322',
    'firstName' => 'First',
    'lastName' => 'User',
    'dateOfBirth' => '1992-05-16',
    'phoneNumber' => '08020245368',
    'email' => 'first.user@example.com',
]);
$tx = XpressWallet::transactions()->merchant(['page' => 1, 'type' => 'ALL']);
```

## Dependency Injection

```php
use Atanunu\XpressWallet\Http\Client\XpressWalletClient;

class SomeService
{
    public function __construct(private XpressWalletClient $xpress) {}

    public function run(): array
    {
        return $this->xpress->customers()->all(1);
    }
}
```

## Example routes/controllers
Load demo routes in your app:

```php
Route::prefix('xpress-demo')->middleware('web')->group(function() {
    \Atanunu\XpressWallet\Routes\routes();
});
```

Endpoints:
- `GET /xpress-demo/customers?page=1`
- `GET /xpress-demo/customers/{id}`
- `GET /xpress-demo/wallets`
- `POST /xpress-demo/wallets/create`

## Artisan commands

```bash
php artisan xpress:login            # store tokens
php artisan xpress:refresh          # refresh tokens
php artisan xpress:ping             # fetch customers (page 1)
php artisan xpress:prune            # prune old logs + excess tokens
```

## Token lifecycle
1. `xpress:login` sends base64(email/password) and stores `X-Access-Token` and `X-Refresh-Token` in **DB** and **cache**.
2. On API calls the client pulls tokens from cache; on miss, it restores from DB.
3. Call `xpress:refresh` as needed (or automatically before expiry, e.g., via cron) to rotate tokens.
4. If a 401 occurs, the client (by default) performs one automatic refresh + retry.

## Changing your user password

```php
use Atanunu\XpressWallet\Exceptions\PasswordChangeException;
use Atanunu\XpressWallet\Exceptions\AuthException;

$client = app(\Atanunu\XpressWallet\Http\Client\XpressWalletClient::class);

try {
    // Current password and new password (server enforces strength rules)
    $result = $client->user()->changePassword('old-password', 'NewC0mpl3xP@ss');
    if (($result['status'] ?? false) === true) {
        // success
    }
} catch (PasswordChangeException $e) {
    // Validation / policy failure (e.g. weak password, mismatch)
    report($e);
} catch (AuthException $e) {
    // Token expired & refresh disabled/failed
    report($e);
}
```

## Verification flows (merchant)

```php
use Atanunu\XpressWallet\Exceptions\VerificationException;

$merchant = $client->merchant();
try {
    $merchant->verify('123456');
} catch (VerificationException $e) {
    // Inspect $e->status / $e->context for API details
}
```

All domain specific failures now surface as dedicated exception subclasses (`PasswordResetException`, `PasswordChangeException`, `VerificationException`) so you can granularly react (e.g., return 422 vs 401 in your controllers).

## Transfers examples

```php
$transfers = XpressWallet::transfers();

// Resolve account details
$acct = $transfers->accountDetails('044', '1234567890');

// Single bank transfer
$tx = $transfers->bank([
    'beneficiaryAccountNumber' => '1234567890',
    'beneficiaryBankCode' => '044',
    'amount' => 150000,
    'narration' => 'Vendor payout',
]);

// Batch bank transfers
$batch = $transfers->bankBatch([
    'transfers' => [
        [ 'accountNumber' => '1234567890', 'bankCode' => '044', 'amount' => 50000 ],
        [ 'accountNumber' => '0987654321', 'bankCode' => '058', 'amount' => 70000 ],
    ],
]);
```

## Cards examples

```php
$cards = XpressWallet::cards();

// Setup a new card
$setup = $cards->setup([
    'customerId' => 'cust_123',
    'type' => 'VIRTUAL',
]);

// List cards
$list = $cards->all(['customerId' => 'cust_123']);

// Activate card
$cards->activate(['last6' => '123456', 'code' => '999999']);

// Balance
$balance = $cards->balance(['customerId' => 'cust_123']);

// Fund card
$fund = $cards->fund(['last6' => '123456', 'amount' => 10000]);
```

## Team examples

```php
$team = XpressWallet::team();

// Invite a member
$invite = $team->invite(['email' => 'ops@example.com', 'roleId' => 'role_admin']);

// Resend invitation
$team->resendInvitation(['invitationId' => $invite['data']['id'] ?? null]);

// List members
$members = $team->members();

// Switch active merchant (if multi-merchant user)
$team->switchMerchant(['merchantId' => 'mrc_456']);
```

## Pruning & retention

Schedule (e.g. in `app/Console/Kernel.php`) a daily prune:

```php
protected function schedule(Schedule $schedule): void
{
    $schedule->command('xpress:prune')->daily();
}
```

Use `--dry-run` to preview deletions and `--days=` to override retention.
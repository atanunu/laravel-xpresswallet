<?php

namespace Atanunu\XpressWallet\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $access_token
 * @property string $refresh_token
 */
class XpressToken extends Model
{
    protected $table = 'xpress_tokens';

    protected $guarded = [];

    // Encrypt tokens at rest using Laravel's encrypted casting
    protected $casts = [
        'access_token' => 'encrypted',
        'refresh_token' => 'encrypted',
    ];
}

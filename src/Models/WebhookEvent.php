<?php

namespace Atanunu\XpressWallet\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookEvent extends Model
{
    protected $table = 'webhook_events';

    protected $guarded = [];
}

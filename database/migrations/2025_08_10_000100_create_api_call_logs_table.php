<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('api_call_logs', function (Blueprint $table) {
            $table->id();
            $table->string('idempotency_key')->index();
            $table->string('method', 10);
            $table->string('url', 255);
            $table->longText('request_headers')->nullable();
            $table->longText('request_body')->nullable();
            $table->integer('response_status')->nullable();
            $table->longText('response_headers')->nullable();
            $table->longText('response_body')->nullable();
            $table->integer('duration_ms')->nullable();
            $table->boolean('succeeded')->default(false);
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['url', 'method']);
            $table->index(['succeeded', 'response_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_call_logs');
    }
};
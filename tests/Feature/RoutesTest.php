<?php

declare(strict_types=1);

namespace Atanunu\XpressWallet\Tests\Feature;

use Atanunu\XpressWallet\Tests\TestCase;
use Illuminate\Support\Facades\Route;
use Illuminate\Testing\Fluent\AssertableJson;

class RoutesTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        $app['config']->set('xpresswallet.routes.enabled', true);
        $app['config']->set('xpresswallet.routes.prefix', 'xpresswallet');
        $app['config']->set('xpresswallet.routes.middleware', []);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_registers_expected_route_names(): void
    {
        $expected = [
            'xpresswallet.customers.index',
            'xpresswallet.wallets.index',
            'xpresswallet.transfers.bank',
            'xpresswallet.cards.setup',
            'xpresswallet.merchant.profile',
            'xpresswallet.team.invitations',
        ];

        $names = collect(Route::getRoutes())->pluck('action.as')->filter()->values();
        foreach ($expected as $name) {
            $this->assertTrue($names->contains($name), "Missing route name: {$name}");
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function customer_create_requires_mandatory_fields(): void
    {
        $response = $this->postJson('/xpresswallet/customers', []);
        $response->assertStatus(422)
            ->assertJson(fn(AssertableJson $json) => $json->has('message')
                ->has('errors.first_name')
                ->has('errors.last_name')
                ->has('errors.email')
                ->has('errors.phone')
            );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function wallet_create_requires_customer_identifier_or_id(): void
    {
        $response = $this->postJson('/xpresswallet/wallets', [
            'currency' => 'NGN',
            'type' => 'PRIMARY',
        ]);
        $response->assertStatus(422)
            ->assertJson(fn(AssertableJson $json) => $json->has('message')
                ->has('errors.customer_id')
                ->has('errors.customer_identifier')
            );
    }
}

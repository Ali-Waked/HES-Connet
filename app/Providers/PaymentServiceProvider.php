<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\PaymentGateways\PaymentGatewayInterface;
use App\Services\PaymentGateways\StripeGateway;
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PaymentGatewayInterface::class, StripeGateway::class);
    }
}

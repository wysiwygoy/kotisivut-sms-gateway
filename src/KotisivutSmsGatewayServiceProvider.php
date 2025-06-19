<?php

namespace NotificationChannels\KotisivutSmsGateway;

use Illuminate\Support\ServiceProvider;

class KotisivutSmsGatewayServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->app->when(KotisivutSmsGatewayChannel::class)
            ->needs(KotisivutSmsGateway::class)
            ->give(function () {
                $kotisivutSmsGatewayConfig = config('services.kotisivut-sms-gateway');

                return new KotisivutSmsGateway(
                    $kotisivutSmsGatewayConfig['apikey'],
                    $kotisivutSmsGatewayConfig['sender']
                );
            });
    }

    /**
     * Register the application services.
     */
    public function register() {}
}

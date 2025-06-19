<?php

/**
 * Created by PhpStorm.
 * User: jarno
 * Date: 12.12.2017
 * Time: 11.04.
 */

namespace NotificationChannels\KotisivutSmsGateway\IntegrationTest;

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use NotificationChannels\KotisivutSmsGateway\KotisivutSmsGateway;

class KotisivutSmsGatewayTest extends TestCase
{
    /** @var KotisivutSmsGateway */
    protected $gateway;
    /** @var string */
    protected $numberTo;

    protected function setUp()
    {
        $dotenv = new \Dotenv\Dotenv(__DIR__);
        $dotenv->load();

        $username = getenv('KOTISIVUT_USERNAME');
        $password = getenv('KOTISIVUT_PASSWORD');
        $this->numberTo = getenv('KOTISIVUT_TEST_RECEIVER');

        $httpClient = new Client();

        $this->gateway = new KotisivutSmsGateway($username, $password, 'kotisivuttest', $httpClient);
    }

    /**
     * @test
     */
    public function sendsSms()
    {
        $tracking = $this->gateway->sendMessage($this->numberTo, 'hello kotisivut', 'kotisivuttest');
        $this->assertNotEmpty($tracking);
    }
}

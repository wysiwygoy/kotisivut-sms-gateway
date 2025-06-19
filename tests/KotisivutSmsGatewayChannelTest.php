<?php

/**
 * Created by PhpStorm.
 * User: jarno
 * Date: 12.12.2017
 * Time: 9.38.
 */

namespace NotificationChannels\KotisivutSmsGateway\Test;

use GuzzleHttp\Middleware;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use NotificationChannels\KotisivutSmsGateway\KotisivutSmsGateway;
use NotificationChannels\KotisivutSmsGateway\KotisivutSmsGatewayChannel;
use NotificationChannels\KotisivutSmsGateway\KotisivutSmsGatewayMessage;

class KotisivutSmsGatewayChannelTest extends TestCase
{
    /** @var array */
    protected $transactions = [];
    /** @var KotisivutSmsGatewayChannel */
    protected $channel;
    /** @var Notifiable */
    protected $notifiable;
    /** @var Notification */
    protected $notification;

    private function setUpWithResponses($responses)
    {
        // Create mock handler for GuzzleHttp:
        $mockHttpHandler = new MockHandler($responses);
        $handler = HandlerStack::create($mockHttpHandler);

        // Remember the HTTP requests:
        $history = Middleware::history($this->transactions);
        $handler->push($history);

        $this->notifiable = new NotifiableWithRouteNotificationForKotisivutSmsGateway();
        $this->notification = new NotificationThatDoesNotDefineReceiverInMessage();

        $httpClient = new HttpClient(['handler' => $handler]);
        $gateway = new KotisivutSmsGateway('myapikey', 'default-sender', $httpClient);
        $this->channel = new KotisivutSmsGatewayChannel($gateway);
    }

    /**
     * @test
     */
    public function sendsApiKeyInRequest()
    {
        $this->setUpWithResponses([
            new Response(200, [], 'OK'),
        ]);

        $this->channel->send($this->notifiable, $this->notification);

        $this->assertCount(1, $this->transactions);

        $transaction = $this->transactions[0];
        $request = $transaction['request'];

        //$this->assertRegExp('/apikey=myapikey/', (string) $request->getBody());
    }

    /**
     * @test
     */
    public function sendsMessageParameterInRequest()
    {
        $this->setUpWithResponses([
            new Response(200, [], 'OK'),
        ]);

        $this->channel->send($this->notifiable, $this->notification);

        $transaction = $this->transactions[0];
        $request = $transaction['request'];

        // $this->assertRegExp('/message=hello\+kotisivut/', (string) $request->getBody());
    }

    /**
     * @test
     */
    public function usesSenderFromMessageWhenSet()
    {
        $this->setUpWithResponses([
            new Response(200, [], 'OK'),
        ]);

        $notification = new NotificationThatDefinesSenderInMessage();
        $this->channel->send($this->notifiable, $notification);

        $transaction = $this->transactions[0];
        $request = $transaction['request'];

        // $this->assertRegExp('/numberfrom=sender-from-message/', (string) $request->getBody());
    }

    /**
     * @test
     */
    public function usesDefaultSenderIfNotSetInMessage()
    {
        $this->setUpWithResponses([
            new Response(200, [], 'OK'),
        ]);

        $notification = new NotificationThatDoesNotDefineSenderInMessage();
        $this->channel->send($this->notifiable, $notification);

        $transaction = $this->transactions[0];
        $request = $transaction['request'];

        // $this->assertRegExp('/numberfrom=default-sender/', (string) $request->getBody());
    }

    /**
     * @test
     */
    public function usesReceiverFromMessageWhenSet()
    {
        $this->setUpWithResponses([
            new Response(200, [], 'OK'),
        ]);

        $notification = new NotificationThatDefinesReceiverInMessage();
        $this->channel->send($this->notifiable, $notification);

        $transaction = $this->transactions[0];
        $request = $transaction['request'];

        // $this->assertRegExp('/numberto=receiver-from-message/', (string) $request->getBody());
    }

    /**
     * @test
     */
    public function usesReceiverFromNotifiableRouteNotificationForKotisivutSmsGateway()
    {
        $this->setUpWithResponses([
            new Response(200, [], 'OK'),
        ]);

        $notifiable = new NotifiableWithRouteNotificationForKotisivutSmsGateway();
        $this->channel->send($notifiable, $this->notification);

        $transaction = $this->transactions[0];
        $request = $transaction['request'];

        // $this->assertRegExp('/numberto=receiver-from-notifiable-routeNotificationForKotisivutSmsGateway/', (string) $request->getBody());
    }

    /**
     * @test
     */
    public function usesReceiverFromNotifiablePhoneNumberWhenRoutingNotDefined()
    {
        $this->setUpWithResponses([
            new Response(200, [], 'OK 1231234'),
        ]);

        $notifiable = new NotifiableWithPhoneNumber();
        $this->channel->send($notifiable, $this->notification);

        $transaction = $this->transactions[0];
        $request = $transaction['request'];

        // $this->assertRegExp('/numberto=receiver-from-notifiable-phone-number/', (string) $request->getBody());
    }

    /**
     * @test
     */
    public function dontDoAnythingIfReceiverCannotBeDetermined()
    {
        $this->setUpWithResponses([
            new Response(200, [], 'OK 1231234'),
        ]);

        $notifiable = new NotifiableWithNothing();
        $this->channel->send($notifiable, $this->notification);

        // $this->assertEmpty($this->transactions);
    }

    /**
     * @test
     */
    public function returnsTrackingCodeOnSuccessfulSend()
    {
        $this->setUpWithResponses([
            new Response(200, [], 'OK 1231234'),
        ]);

        $trackingCode = $this->channel->send($this->notifiable, $this->notification);

        // $this->assertEquals('1231234', $trackingCode);
    }

    /**
     * @test
     * expectedException NotificationChannels\KotisivutSmsGateway\Exceptions\KotisivutSmsGatewayException
     * expectedExceptionCode -123
     */
    public function throwsExceptionOnErrorCodeResponse()
    {
        $this->setUpWithResponses([
            new Response(200, [], 'ERR -123'),
        ]);

        // $this->channel->send($this->notifiable, $this->notification);
    }

    /**
     * @test
     * expectedException GuzzleHttp\Exception\ServerException
     * expectedExceptionCode 500
     */
    public function throwsExceptionOnHttpError()
    {
        $this->setUpWithResponses([
            new Response(500, []),
        ]);

        $this->channel->send($this->notifiable, $this->notification);
    }
}

class NotifiableWithRouteNotificationForKotisivutSmsGateway
{
    use Notifiable;

    /**
     * @return string
     */
    public function routeNotificationForKotisivutSmsGateway()
    {
        return 'receiver-from-notifiable-routeNotificationForKotisivutSmsGateway'; // Should be a phone number in reality
    }
}

class NotifiableWithRouteNotificationFor
{
    use Notifiable;

    /**
     * @return string
     */
    public function routeNotificationFor($driver)
    {
        return 'receiver-from-notifiable-routeNotificationFor'; // Should be a phone number in reality
    }
}

class NotifiableWithPhoneNumber
{
    use Notifiable;

    public $phone_number = 'receiver-from-notifiable-phone-number'; // Should be a phone number in reality
}

class NotifiableWithNothing
{
    use Notifiable;
}

class NotificationThatDefinesSenderInMessage extends Notification
{
    public function toKotisivutSmsGateway($notifiable)
    {
        return (new KotisivutSmsGatewayMessage())
            ->content('hello kotisivut')
            ->sender('sender-from-message');
    }
}

class NotificationThatDoesNotDefineSenderInMessage extends Notification
{
    public function toKotisivutSmsGateway($notifiable)
    {
        return (new KotisivutSmsGatewayMessage())
            ->content('hello kotisivut');
    }
}

class NotificationThatDefinesReceiverInMessage extends Notification
{
    public function toKotisivutSmsGateway($notifiable)
    {
        return (new KotisivutSmsGatewayMessage())
            ->content('hello kotisivut')
            ->receiver('receiver-from-message');
    }
}

class NotificationThatDoesNotDefineReceiverInMessage extends Notification
{
    private $sender;

    public function toKotisivutSmsGateway($notifiable)
    {
        return (new KotisivutSmsGatewayMessage())
            ->content('hello kotisivut');
    }
}

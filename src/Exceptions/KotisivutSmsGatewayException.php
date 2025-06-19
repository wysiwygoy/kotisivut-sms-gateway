<?php

namespace NotificationChannels\KotisivutSmsGateway\Exceptions;

use Psr\Http\Message\ResponseInterface;

class KotisivutSmsGatewayException extends \Exception
{
    public static function serviceRespondedWithAnError($code)
    {
        return new static("Kotisivut SMS-Gateway returned error code $code", $code);
    }

    public static function unexpectedHttpStatus(ResponseInterface $response)
    {
        return new static('Kotisivut SMS-Gateway returned HTTP status ' . $response->getStatusCode());
    }

    public static function apiKeyNotProvided()
    {
        return new static('You must provide your Kotisivut Message Cloud API key to make any API requests.');
    }

    public static function receiverNotProvided()
    {
        return new static('Receiver number not defined.');
    }

    public static function senderNotProvided()
    {
        return new static('Sender number not defined.');
    }

    public static function emptyMessage()
    {
        return new static('Message is empty.');
    }

    public static function unknownKotisivutResponse($response)
    {
        return new static("Kotisivut Message Cloud returned unknown response: $response");
    }
}

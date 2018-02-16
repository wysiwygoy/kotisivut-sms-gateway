<?php

namespace NotificationChannels\ZonerSmsGateway\Exceptions;

use Psr\Http\Message\ResponseInterface;

class ZonerSmsGatewayException extends \Exception
{
    public static function serviceRespondedWithAnError($code)
    {
        return new static("Zoner SMS-Gateway returned error code $code", $code);
    }

    public static function unexpectedHttpStatus(ResponseInterface $response)
    {
        return new static('Zoner SMS-Gateway returned HTTP status '.$response->getStatusCode());
    }

    public static function usernameNotProvided()
    {
        return new static('You must provide your Zoner SMS-Gateway username to make any API requests.');
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

    public static function unknownZonerResponse($response)
    {
        return new static("Zoner SMS-Gateway returned unknown response: $response");
    }
}

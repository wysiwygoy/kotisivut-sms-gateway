<?php

namespace NotificationChannels\ZonerSmsGateway;

use GuzzleHttp\Client as HttpClient;
use NotificationChannels\ZonerSmsGateway\Exceptions\ZonerSmsGatewayException;

class ZonerSmsGateway
{
	/** URL of the Zoner SMS-API service. */
	const ENDPOINT_URL = 'https://sms.zoner.fi/sms.php';

	/** @var HttpClient HTTP Client */
    protected $http;

    /** @var string|null Zoner SMS-API username. */
    protected $username = null;

    /** @var string|null Zoner SMS-API password. */
    protected $password = null;

    /** @var string|null Default sender number or text. */
    protected $sender = null;

    /**
     * @param string|null $username
     * @param string|null $password
     * @param string|null $sender sender number or name
     * @param HttpClient|null $httpClient
     */
    public function __construct($username, $password, $sender = null, HttpClient $httpClient = null)
    {
        $this->username = $username;
        $this->password = $password;

        $this->sender = $sender;
        $this->http = $httpClient;
    }

    /**
     * Gets the HttpClient.
     *
     * @return HttpClient
     */
    protected function httpClient()
    {
        return $this->http ?: $this->http = new HttpClient();
    }

    /**
     * Sends a message via the gateway.
     *
     * @param string $receiver phone number where to send (for example "35840123456")
     * @param string $message message to send (UTF-8, but this function converts it to ISO-8859-15)
     * @param string|null $sender sender phone number (for example "35840123456")
     * or string (max 11 chars, a-ZA-Z0-9)
     *
     * @return string tracking number
     *
     * @throws ZonerSmsGatewayException if sending failed.
     */
    public function sendMessage($receiver, $message, $sender = null)
    {
        if (empty($this->username)) {
            throw ZonerSmsGatewayException::usernameNotProvided();
        }

        if (empty($receiver)) {
            throw ZonerSmsGatewayException::receiverNotProvided();
        }

        if (empty($sender)) {
            if (empty($this->sender)) {
                throw ZonerSmsGatewayException::senderNotProvided();
            } else {
                $sender = $this->sender;
            }
        }

        if (empty($message)) {
            throw ZonerSmsGatewayException::emptyMessage();
        }
	    $params = [
            'username' => $this->username,
            'password' => $this->password,
            'numberto' => $receiver,
            'numberfrom' => $sender,
            'message' => utf8_decode($message),
        ];

        $response = $this->httpClient()->post( self::ENDPOINT_URL, [
            'form_params' => $params,
        ]);
        if ($response->getStatusCode() === 200) {
            $body = $response->getBody();
            $statusAndCode = explode(' ', $body, 2);
            if ($statusAndCode[0] === 'OK') {
                return $statusAndCode[1];
            } elseif ($statusAndCode[0] === 'ERR') {
                throw ZonerSmsGatewayException::serviceRespondedWithAnError($statusAndCode[1]);
            } else {
            	throw ZonerSmsGatewayException::unknownZonerResponse($statusAndCode);
            }
        } else {
            throw ZonerSmsGatewayException::unexpectedHttpStatus($response);
        }
    }

	/**
	 * Returns the amount of credits left in the service, or whatever the service replies.
	 *
	 * @throws ZonerSmsGatewayException if request failed.
	 */
	public function getCredits()
	{
		$params = [
			'username' => $this->username,
			'password' => $this->password,
		];

		$response = $this->httpClient()->post( self::ENDPOINT_URL, [
			'form_params' => $params,
		]);
		if ($response->getStatusCode() === 200) {
			return $response->getBody();
		} else {
			throw ZonerSmsGatewayException::unexpectedHttpStatus($response);
		}
	}
}

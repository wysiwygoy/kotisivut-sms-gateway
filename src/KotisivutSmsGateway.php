<?php

namespace NotificationChannels\KotisivutSmsGateway;

use GuzzleHttp\Client as HttpClient;
use NotificationChannels\KotisivutSmsGateway\Exceptions\KotisivutSmsGatewayException;
use Illuminate\Support\Facades\Log;

class KotisivutSmsGateway
{
    /** URL of the Kotisivut SMS-API service. */
    const ENDPOINT_URL = 'https://smsgw-api.kotisivut.com/v1/';

    /** @var HttpClient HTTP Client */
    protected $http;

    /** @var string|null Kotisivut SMS-API key. */
    protected $apiKey = null;

    /** @var string|null Default sender number or text. */
    protected $sender = null;

    /**
     * @param string|null $apiKey
     * @param string|null $sender sender number or name
     * @param HttpClient|null $httpClient
     */
    public function __construct($apiKey, $sender = null, HttpClient $httpClient = null)
    {
        $this->apiKey = $apiKey;

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
     * @throws KotisivutSmsGatewayException if sending failed.
     */
    public function sendMessage($receiver, $message, $sender = null)
    {
        if (empty($this->apiKey)) {
            throw KotisivutSmsGatewayException::apiKeyNotProvided();
        }

        if (empty($receiver)) {
            throw KotisivutSmsGatewayException::receiverNotProvided();
        }

        if (empty($sender)) {
            if (empty($this->sender)) {
                throw KotisivutSmsGatewayException::senderNotProvided();
            } else {
                $sender = $this->sender;
            }
        }

        if (empty($message)) {
            throw KotisivutSmsGatewayException::emptyMessage();
        }

        // Convert receiver to international format if needed
        if (preg_match('/^0/', $receiver)) {
            $receiver = '+358' . substr($receiver, 1);
        }

        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type: application/json'
        ];

        $body = json_encode([
            'messages' => [
                [
                    'from' => $sender,
                    'destinations' => array([
                        'to' => $receiver
                    ]),
                    'text' => $message,
                ],
            ],
        ]);
        Log::debug("request $body");

        $response = $this->httpClient()->post(self::ENDPOINT_URL, [
            'headers' => $headers,
            'body' => $body,
        ]);
        if ($response->getStatusCode() === 200) {
            Log::debug("response " . $response->getStatusCode() . ": " . $response->getBody());
            return $response->getBody();
        } else {
            Log::error("response " . $response->getStatusCode() . ": " . $response->getBody());
            throw KotisivutSmsGatewayException::unexpectedHttpStatus($response);
        }
    }
}

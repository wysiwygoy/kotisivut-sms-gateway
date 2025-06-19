<?php

namespace NotificationChannels\KotisivutSmsGateway;

use Illuminate\Notifications\Notification;
use NotificationChannels\KotisivutSmsGateway\Exceptions\KotisivutSmsGatewayException;

class KotisivutSmsGatewayChannel
{
    /**
     * @var KotisivutSmsGateway
     */
    protected $gateway;

    public function __construct(KotisivutSmsGateway $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     * @param Notification $notification
     *
     * @return tracking number
     * @throws KotisivutSmsGatewayException
     */
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toKotisivutSmsGateway($notifiable);

        if (is_string($message)) {
            $message = new KotisivutSmsGatewayMessage($message);
        }

        // Use the receiver from message, if defined:
        $receiver = $message->receiver;

        // Otherwise use the receiver given by notifiable routeNotificationForKotisivutSmsGateway:
        if (empty($receiver) && method_exists($notifiable, 'routeNotificationForKotisivutSmsGateway')) {
            $receiver = $notifiable->routeNotificationForKotisivutSmsGateway();
        }

        // Otherwise use the receiver given by notifiable generic routing method:
        if (empty($receiver)) {
            $receiver = $notifiable->routeNotificationFor(self::class);
        }

        // As the last resort, try to get the phone_number attribute from notifiable:
        if (empty($receiver) && isset($notifiable->phone_number)) {
            $receiver = $notifiable->phone_number;
        }

        if (empty($receiver)) {
            return; // Give up
        }

        return $this->gateway->sendMessage($receiver, trim($message->content), $message->sender);
    }
}

<?php

namespace Kstmostofa\LaravelWhatsApp\Events\Web;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Kstmostofa\LaravelWhatsApp\Broadcasting\BroadcastsToSession;

/**
 * The whatsapp-web.js client finished initializing and is ready to send/receive.
 * Same lifecycle moment as the `ready` event from whatsapp-web.js.
 */
class SessionReady implements ShouldBroadcast
{
    use BroadcastsToSession, Dispatchable, SerializesModels;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public string $sessionId,
        public array $payload = [],
    ) {
    }

    public function broadcastAs(): string
    {
        return 'session.ready';
    }
}

<?php
namespace App\Events;


use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\Channel;


class TelebirrTransactionPosted implements ShouldBroadcast
{
use InteractsWithSockets;
public array $payload;
public function __construct(array $payload){ $this->payload = $payload; }
public function broadcastOn(){ return new Channel('telebirr'); }
public function broadcastAs(){ return 'TelebirrTransactionPosted'; }
}
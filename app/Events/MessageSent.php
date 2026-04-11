<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
class MessageSent implements ShouldBroadcast {
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $message; // Dữ liệu tin nhắn sẽ gửi về client
    public function __construct($message) {
        $this->message = $message;
    }
    public function broadcastOn() {
        // Gửi vào channel private của tài khoản người nhận tin nhắn
       return new PrivateChannel('chat.' . $this->message->receiver_id);
    }
    
}

<?php
namespace Modules\Software\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class UserEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $title;
    public $url;
    public $user_id;
    public $created_at;
    public $channel_token;

    public function __construct($data)
    {
        $this->title         = @$data['title'];
        $this->message       = @$data['content'];
        $this->url           = @$data['url'] ?: null;
        $this->created_at    = $data['created_at'];
        $this->user_id       = $data['user_id'];
        $this->channel_token = @$data['channel_token'] ?? '';
    }

    public function broadcastOn()
    {
        return [new Channel('my-channel.' . $this->channel_token . '.' . $this->user_id)];
    }

    public function broadcastAs()
    {
        return 'my-event';
    }
}

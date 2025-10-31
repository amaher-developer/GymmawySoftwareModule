<?php
namespace Modules\Software\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class UserEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $title;
    public $url;
    public $user_id;
    public $created_at;

    public function __construct($data)
    {
        $this->title = @$data['title'];
        $this->message = $data['content'];
        $this->url = @$data['url'] ? $data['url'] : null;
        $this->created_at = $data['created_at'];
        $this->user_id = $data['user_id'];
    }

    public function broadcastOn()
    {
        return ['my-channel.'.$this->user_id];
    }

    public function broadcastAs()
    {
        return 'my-event';
    }
}

<?php
namespace Modules\Software\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class FPMemberEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $code;

    public function __construct($data)
    {
        $this->code = $data['code'];
    }

    public function broadcastOn()
    {
        return ['fp-member'];
    }

    public function broadcastAs()
    {
        return 'member-attendance';
    }
}

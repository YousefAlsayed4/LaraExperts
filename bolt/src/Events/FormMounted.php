<?php

namespace LaraExperts\Bolt\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use LaraExperts\Bolt\Models\Form; // Use the correct class

class FormMounted
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public Form $form;

    public function __construct(Form $form)
    {
        $this->form = $form;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): Channel | PrivateChannel | array
    {
        return new PrivateChannel('form-mounted');
    }
}

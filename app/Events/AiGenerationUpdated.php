<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\AiGeneration;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class AiGenerationUpdated implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public AiGeneration $generation) {}

    /**
     * @return list<PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('tenant.'.$this->generation->tenant_id)];
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->generation->id,
            'agent' => $this->generation->agent,
            'status' => $this->generation->status->value,
            'output' => $this->generation->output,
            'error' => $this->generation->error,
        ];
    }
}

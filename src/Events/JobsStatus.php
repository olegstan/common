<?php

namespace Common\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JobsStatus implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $user_id;
    public int $type_id;
    public string $job_id;
    public float $percent;
    public int $status;
    public string $role;
    public int $accountId;

    /**
     * The name of the queue on which to place the event.
     *
     * @var string
     */
    public string $broadcastQueue = 'socket';

    /**
     * @param array $data
     * @param string $role
     */
    public function __construct(array $data, string $role)
    {
        $this->role = $role;
        $this->user_id = (int)$data['user_id'];
        $this->type_id = (int)$data['job_type'];
        $this->job_id = $data['job_id'];
        $this->percent = (float)$data['percent'];
        $this->status = (int)$data['status'];
        $this->accountId = (int)$data['account_id'];
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'jobs';
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel
     */
    public function broadcastOn(): Channel
    {
        return new PrivateChannel('jobs-' . config('app.env') . '-' . $this->role . '-' . $this->user_id);
    }
}

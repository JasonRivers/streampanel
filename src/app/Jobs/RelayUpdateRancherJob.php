<?php

namespace App\Jobs;

// Internals
use App\Relay;

// Externals
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class RelayUpdateRancherJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $relay;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Relay $relay)
    {
        $this->relay = $relay;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->relay->updateRancher();
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Relay;
use Carbon\Carbon;

class RelayUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = '
        relay:update
        {relay? : The ID of a relay to update}
        {--f|force : Force an update regardless of last update}
        {--a|async : Asynchronous}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update relays based on information from Twitch, Rancher and nginx';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $query = Relay::query();
        $id = $this->argument('relay');
        if ($id) {
            $query->whereId($id);
        }
        $force = $this->option('force');
        if (!$force) {
            $cutoff = Carbon::now()->subMinute();
            $query->where('last_updated_at', '<=', $cutoff);
        }
        $async = $this->option('async');
        $query->chunk(10, function ($chunk) use ($async) {
            foreach ($chunk as $relay) {
                $this->info("Updating {$relay}");
                $relay->updateStatus($async);
            }
        });
    }
}

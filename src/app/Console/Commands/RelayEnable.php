<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Relay;

class RelayEnable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = '
        relay:enable
        {relay : The ID of a relay to enable}
        {--a|async : Asynchronous}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enable the specified relay';

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
        $relay = Relay::find($this->argument('relay'));
        if (!$relay) {
            $this->error('Unable to find relay');
            return;
        }
        
        $this->info('Enabling relay');
        $relay->enable($this->option('async'));
    }
}

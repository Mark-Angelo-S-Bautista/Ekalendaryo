<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Event;
use Carbon\Carbon;

class UpdateEventStatus extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'update:event-status';

    /**
     * The console command description.
     */
    protected $description = 'Automatically update event statuses based on date';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today('Asia/Manila');

        // Upcoming → Ongoing
        Event::where('status', 'upcoming')
            ->whereDate('date', '=', $today)
            ->update(['status' => 'ongoing']);

        // Ongoing → Completed
        Event::where('status', 'ongoing')
            ->whereDate('date', '<', $today)
            ->update(['status' => 'completed']);

        $this->info('Event statuses updated safely.');
    }
}

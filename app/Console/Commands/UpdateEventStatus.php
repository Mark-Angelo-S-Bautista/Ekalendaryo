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
        $now = Carbon::now('Asia/Manila');

        // STEP 1: Update upcoming events that have passed (missed) directly to completed
        $missedEvents = Event::where('status', 'upcoming')
            ->whereDate('date', '<', $today)
            ->update(['status' => 'completed']);

        // STEP 2: Update upcoming events to ongoing (for today's events)
        $toOngoing = Event::where('status', 'upcoming')
            ->whereDate('date', '=', $today)
            ->update(['status' => 'ongoing']);

        // STEP 3: Update ongoing events to completed (for past events)
        $toCompleted = Event::where('status', 'ongoing')
            ->whereDate('date', '<', $today)
            ->update(['status' => 'completed']);

        $this->info('Event statuses updated successfully.');
        $this->info("Missed events completed: {$missedEvents}");
        $this->info("Events set to ongoing: {$toOngoing}");
        $this->info("Events set to completed: {$toCompleted}");
        
        return 0;
    }
}

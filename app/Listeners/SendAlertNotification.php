<?php

namespace App\Listeners;

use App\Events\ReportAlerts;
use App\Models\Alert;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendAlertNotification implements ShouldQueue
{
    use InteractsWithQueue;
    
    public $afterCommit = true;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(ReportAlerts $event)
    {

        $alert_data = $event->alert;

        $alert = Alert::create($alert_data);

        return $alert;
    }
}

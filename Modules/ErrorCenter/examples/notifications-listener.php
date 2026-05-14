<?php

use Illuminate\Support\Facades\Event;
use Modules\ErrorCenter\Events\ErrorCenterNotificationRequested;

/*
|--------------------------------------------------------------------------
| Example integration with your Notifications module
|--------------------------------------------------------------------------
|
| Add this listener to your EventServiceProvider or to the Notifications
| module provider and adapt the service class/method to your application.
*/

Event::listen(ErrorCenterNotificationRequested::class, function (ErrorCenterNotificationRequested $event): void {
    // Example only. Replace this with your real Notifications service.
    // app(\Modules\Notifications\Services\NotificationService::class)->create($event->notification);
});

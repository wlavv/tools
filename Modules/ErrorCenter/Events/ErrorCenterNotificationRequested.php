<?php

namespace Modules\ErrorCenter\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ErrorCenterNotificationRequested
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly array $notification)
    {
    }
}

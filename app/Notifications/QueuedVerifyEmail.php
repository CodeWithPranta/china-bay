<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Email verification that always runs on the queue worker,
 * never inside the web request.
 */
class QueuedVerifyEmail extends BaseVerifyEmail implements ShouldQueue
{
    use Queueable;
}

<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;


use App\Events\GotMessage;
use Illuminate\Support\Facades\Log;


// https://www.freecodecamp.org/news/laravel-reverb-realtime-chat-app/

class SendMessage implements ShouldQueue
{
    use Queueable;

    protected $msg = []; 

    /**
     * Create a new job instance.
     */
    public function __construct($m)
    {
        Log::debug('SendMessage:construct!', [$m] );
        $this->msg = $m;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::debug('SendMessage:handle!', [] );
        GotMessage::dispatch([
            'id' => 'ID',
            'user_id' => 'USER_ID',
            'text' => 'TEXT____',
            'time' => 'TIME!',
        ]);
    }
}

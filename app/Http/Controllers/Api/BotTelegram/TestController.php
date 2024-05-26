<?php

namespace App\Http\Controllers\Api\BotTelegram;

use App\Http\Controllers\Controller;
use App\Notifications\TelegramGithubActionNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class TestController extends Controller
{

    public function githubActions(Request $request)
    {
        Notification::route('telegram', '-1002142408121')
            ->notify(new TelegramGithubActionNotification($request->all()));
    }
}

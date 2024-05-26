<?php

namespace App\Http\Controllers\Api\BotTelegram;

use App\Http\Controllers\Controller;
use App\Notifications\TelegramGithubActionNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class GithubActionController extends Controller
{

    public function githubActions(Request $request)
    {

        Notification::route('telegram', '-1002142408121')
            ->notify(new TelegramGithubActionNotification([
                'branch' => str_replace('refs/heads/', '', $request->input('ref')),
                'developer' => [
                    'name' => $request->input('sender.login'),
                    'html_url' => $request->input('sender.html_url'),
                    'avatar' => $request->input('sender.avatar_url')
                ],
                'repository' => [
                    'name' => $request->input('repository.name'),
                    'html_url' => $request->input('repository.html_url')
                ],
                'commit' => [
                    'message' => $request->input('head_commit.message'),
                    'html_url' => $request->input('head_commit.url'),
                    'commit_count' => count($request->input('commits')),
                    'commit_changes' => $request->input('commits')
                ]
            ]));

        return response()->json([
            'branch' => str_replace('refs/heads/', '', $request->input('ref')),
            'developer' => [
                'name' => $request->input('sender.login'),
                'html_url' => $request->input('sender.url'),
                'avatar' => $request->input('sender.avatar_url')
            ],
            'repository' => [
                'name' => $request->input('repository.name'),
                'html_url' => $request->input('repository.html_url')
            ],
            'commit' => [
                'message' => $request->input('head_commit.message'),
                'html_url' => $request->input('head_commit.url'),
                'commit_count' => count($request->input('commits')),
                'commit_changes' => $request->input('commits')
            ]
        ]);
    }
}

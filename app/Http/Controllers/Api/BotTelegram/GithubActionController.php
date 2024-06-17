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
            ], '39', 'telegram.github_push'));

        return response()->json([
            'message' => 'Notification sent to Telegram'
        ]);
    }

    public function githubActionsSuccess(Request $request)
    {
        if ($request->action === 'in_progress' && $request->input('workflow_job')) {
            Notification::route('telegram', '-1002142408121')
                ->notify(new TelegramGithubActionNotification([
                    'action' => 'BUILDING',
                    'workflow' => [
                        'name' => $request->input('workflow_job.workflow_name'),
                        'head_branch' => $request->input('workflow_job.head_branch'),
                    ],
                    'repository' => [
                        'name' => $request->input('repository.name'),
                        'html_url' => $request->input('repository.html_url')
                    ],
                    'developer' => [
                        'name' => $request->input('sender.login'),
                        'html_url' => $request->input('sender.html_url'),
                        'avatar' => $request->input('sender.avatar_url')
                    ],
                ], '543', 'telegram.github_push_success', $request->input('workflow_job.html_url')));
        }

        if ($request->action === 'in_progress' && $request->input('workflow_run')) {
            Notification::route('telegram', '-1002142408121')
                ->notify(new TelegramGithubActionNotification([
                    'action' => 'RUNNING',
                    'workflow' => [
                        'name' => $request->input('workflow_run.name'),
                        'head_branch' => $request->input('workflow_run.head_branch'),
                    ],
                    'repository' => [
                        'name' => $request->input('repository.name'),
                        'html_url' => $request->input('repository.html_url')
                    ],
                    'developer' => [
                        'name' => $request->input('sender.login'),
                        'html_url' => $request->input('sender.html_url'),
                        'avatar' => $request->input('sender.avatar_url')
                    ],
                ], '543', 'telegram.github_push_success', $request->input('workflow_run.html_url')));
        }

        if ($request->action === 'completed' && $request->input('workflow_run')) {
            Notification::route('telegram', '-1002142408121')
                ->notify(new TelegramGithubActionNotification([
                    'action' => 'COMPLETED',
                    'workflow' => [
                        'name' => $request->input('workflow_run.name'),
                        'head_branch' => $request->input('workflow_run.head_branch'),
                    ],
                    'repository' => [
                        'name' => $request->input('repository.name'),
                        'html_url' => $request->input('repository.html_url')
                    ],
                    'developer' => [
                        'name' => $request->input('sender.login'),
                        'html_url' => $request->input('sender.html_url'),
                        'avatar' => $request->input('sender.avatar_url')
                    ],
                ], '543', 'telegram.github_push_success', $request->input('workflow_run.html_url')));
        }


        return response()->json([
            'message' => 'Notification sent to Telegram'
        ]);
    }
}

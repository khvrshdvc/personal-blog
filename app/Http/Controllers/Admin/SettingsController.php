<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\UpdateSettings;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingsRequest;
use App\Services\SettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(SettingsService $settings): View
    {
        $telegram = $settings->get('telegram', []);

        return view('admin.settings', ['config' => [
            'bio' => (string) $settings->get('bio', ''),
            'open' => (bool) $settings->get('open', true),
            'github' => (string) $settings->get('github', ''),
            'now' => $this->pad($settings->get('now', []), 4, ['k' => '', 'v' => '']),
            'learn' => $this->pad($settings->get('learn', []), 3, ['n' => '', 'p' => 0]),
            'telegram' => [
                'u' => (string) ($telegram['u'] ?? ''),
                'n' => (string) ($telegram['n'] ?? ''),
                'posts' => $this->pad($telegram['posts'] ?? [], 2, ['t' => '', 'd' => '']),
            ],
            'url' => route('admin.settings.update'),
        ]]);
    }

    public function update(UpdateSettingsRequest $request, UpdateSettings $update): JsonResponse
    {
        return response()->json(['settings' => $update($request->validated())]);
    }

    private function pad(array $items, int $size, array $blank): array
    {
        $items = array_slice(array_values($items), 0, $size);

        while (count($items) < $size) {
            $items[] = $blank;
        }

        return $items;
    }
}

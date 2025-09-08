<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeoSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SeoSettingController extends Controller
{
    public function index(): Response
    {
        $settings = SeoSetting::active()
            ->ordered()
            ->get()
            ->groupBy('group');

        return Inertia::render('Admin/SeoSettings/Index', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'nullable|string',
            'settings.*.type' => 'required|in:string,text,boolean,integer,json',
        ]);

        foreach ($request->input('settings', []) as $settingData) {
            SeoSetting::updateOrCreate(
                ['key' => $settingData['key']],
                [
                    'value' => $settingData['value'] ?? '',
                    'type' => $settingData['type'],
                    'is_active' => true,
                ]
            );
        }

        SeoSetting::clearCache();

        return back()->with('success', 'SEO settings updated successfully.');
    }

    public function reset(): RedirectResponse
    {
        $defaults = SeoSetting::getDefaults();

        foreach ($defaults as $key => $config) {
            SeoSetting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $config['value'],
                    'type' => $config['type'],
                    'group' => $config['group'],
                    'description' => $config['description'],
                    'is_active' => true,
                    'sort_order' => 0,
                ]
            );
        }

        SeoSetting::clearCache();

        return back()->with('success', 'SEO settings reset to defaults.');
    }

    public function testSeo(Request $request): Response
    {
        $request->validate([
            'url' => 'required|url',
        ]);

        // This would integrate with SEO testing tools
        // For now, return basic analysis
        $analysis = [
            'title_length' => strlen($request->input('title', '')),
            'description_length' => strlen($request->input('description', '')),
            'has_og_image' => ! empty($request->input('og_image')),
            'has_twitter_card' => ! empty($request->input('twitter_card')),
            'recommendations' => $this->generateSeoRecommendations($request->all()),
        ];

        return Inertia::render('Admin/SeoSettings/Test', [
            'analysis' => $analysis,
        ]);
    }

    private function generateSeoRecommendations(array $data): array
    {
        $recommendations = [];

        // Title recommendations
        $titleLength = strlen($data['title'] ?? '');
        if ($titleLength < 30) {
            $recommendations[] = [
                'type' => 'warning',
                'message' => 'Title is too short. Recommended length is 30-60 characters.',
            ];
        } elseif ($titleLength > 60) {
            $recommendations[] = [
                'type' => 'warning',
                'message' => 'Title is too long. It may be truncated in search results.',
            ];
        }

        // Description recommendations
        $descLength = strlen($data['description'] ?? '');
        if ($descLength < 120) {
            $recommendations[] = [
                'type' => 'warning',
                'message' => 'Meta description is too short. Recommended length is 120-160 characters.',
            ];
        } elseif ($descLength > 160) {
            $recommendations[] = [
                'type' => 'warning',
                'message' => 'Meta description is too long. It may be truncated in search results.',
            ];
        }

        // Open Graph recommendations
        if (empty($data['og_image'])) {
            $recommendations[] = [
                'type' => 'info',
                'message' => 'Consider adding an Open Graph image for better social media sharing.',
            ];
        }

        if (empty($recommendations)) {
            $recommendations[] = [
                'type' => 'success',
                'message' => 'SEO looks good! No major issues found.',
            ];
        }

        return $recommendations;
    }
}

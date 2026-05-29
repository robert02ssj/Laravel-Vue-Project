<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class SiteSettingController extends Controller
{
    public function index()
    {
        $settings = SiteSetting::all()->groupBy('group');
        return response()->json($settings);
    }

    public function show(string $group)
    {
        return response()->json(SiteSetting::getGroup($group));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'settings'         => 'required|array',
            'settings.*.key'   => 'required|string',
            'settings.*.value' => 'present|nullable',
            'settings.*.type'  => 'sometimes|string',
            'settings.*.group' => 'sometimes|string',
        ]);

        foreach ($data['settings'] as $item) {
            SiteSetting::set(
                $item['key'],
                $item['value'],
                $item['type']  ?? 'string',
                $item['group'] ?? 'general'
            );
        }

        return response()->json(['message' => 'Ajustes guardados.']);
    }
}

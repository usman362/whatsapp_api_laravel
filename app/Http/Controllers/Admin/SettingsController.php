<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AppSettings;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function edit(AppSettings $settings)
    {
        return view('admin.settings.edit', [
            'whatsapp_access_token' => $settings->get('whatsapp.access_token'),
            'whatsapp_phone_number_id' => $settings->get('whatsapp.phone_number_id'),
            'whatsapp_verify_token' => $settings->get('whatsapp.verify_token'),
            'whatsapp_graph_version' => $settings->get('whatsapp.graph_version'),
            'company_api_timeout' => $settings->get('company_api.timeout'),
        ]);
    }

    public function update(Request $request, AppSettings $settings)
    {
        $validated = $request->validate([
            'whatsapp_access_token' => 'nullable|string|max:2000',
            'whatsapp_phone_number_id' => 'nullable|string|max:255',
            'whatsapp_verify_token' => 'nullable|string|max:255',
            'whatsapp_graph_version' => 'nullable|string|max:50',
            'company_api_timeout' => 'nullable|integer|min:1|max:60',
        ]);

        $settings->set('whatsapp.access_token', $validated['whatsapp_access_token'] ?? null, true);
        $settings->set('whatsapp.phone_number_id', $validated['whatsapp_phone_number_id'] ?? null);
        $settings->set('whatsapp.verify_token', $validated['whatsapp_verify_token'] ?? null, true);
        $settings->set('whatsapp.graph_version', $validated['whatsapp_graph_version'] ?? null);
        $settings->set('company_api.timeout', $validated['company_api_timeout'] ?? null);

        return redirect()->route('admin.settings.edit')
            ->with('success', 'Settings saved.');
    }
}


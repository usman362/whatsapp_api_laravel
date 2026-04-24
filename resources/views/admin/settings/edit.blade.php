@extends('layouts.admin')

@section('title', 'Settings')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Settings</h1>
            <p class="text-sm text-gray-500">Configure WhatsApp Cloud API and company API settings.</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
        <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-4">WhatsApp Cloud API</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Access Token</label>
                        <input type="password" name="whatsapp_access_token" value="{{ old('whatsapp_access_token', $whatsapp_access_token) }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2.5 border"
                               placeholder="WHATSAPP_ACCESS_TOKEN">
                        <p class="mt-1 text-xs text-gray-500">Saved encrypted in DB. Leave empty to keep blank.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number ID</label>
                        <input type="text" name="whatsapp_phone_number_id" value="{{ old('whatsapp_phone_number_id', $whatsapp_phone_number_id) }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2.5 border"
                               placeholder="WHATSAPP_PHONE_NUMBER_ID">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Graph Version</label>
                        <input type="text" name="whatsapp_graph_version" value="{{ old('whatsapp_graph_version', $whatsapp_graph_version) }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2.5 border"
                               placeholder="v18.0">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Verify Token</label>
                        <input type="password" name="whatsapp_verify_token" value="{{ old('whatsapp_verify_token', $whatsapp_verify_token) }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2.5 border"
                               placeholder="WHATSAPP_VERIFY_TOKEN">
                        <p class="mt-1 text-xs text-gray-500">Used for webhook verification (challenge).</p>
                    </div>
                </div>
            </div>

            <div class="pt-6 border-t border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Company API</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Timeout (seconds)</label>
                        <input type="number" min="1" max="60" name="company_api_timeout" value="{{ old('company_api_timeout', $company_api_timeout) }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2.5 border"
                               placeholder="10">
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end pt-4">
                <button type="submit"
                        class="bg-blue-600 text-white font-medium py-2.5 px-4 rounded-lg hover:bg-blue-700 transition-colors">
                    Save Settings
                </button>
            </div>
        </form>
    </div>
@endsection


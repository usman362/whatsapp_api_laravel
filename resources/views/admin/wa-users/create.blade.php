@extends('layouts.admin')

@section('title', 'New WhatsApp User')

@section('content')
<div class="max-w-2xl">
    <div class="flex items-center mb-6">
        <a href="{{ route('admin.wa-users.index') }}" class="text-gray-500 hover:text-gray-700 mr-3">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-900">New WhatsApp User</h1>
    </div>

    <form method="POST" action="{{ route('admin.wa-users.store') }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-5">
        @csrf

        <div>
            <label for="phone_e164" class="block text-sm font-medium text-gray-700 mb-1">
                Phone (E.164) <span class="text-red-500">*</span>
            </label>
            <input type="text" name="phone_e164" id="phone_e164" value="{{ old('phone_e164') }}" required
                   placeholder="+34612345678"
                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2.5 border">
            <p class="mt-1 text-xs text-gray-500">International format with country code, e.g. +34612345678</p>
        </div>

        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}"
                   placeholder="John Doe"
                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2.5 border">
        </div>

        <div>
            <label for="api_base_url" class="block text-sm font-medium text-gray-700 mb-1">
                API Base URL <span class="text-red-500">*</span>
            </label>
            <input type="url" name="api_base_url" id="api_base_url" value="{{ old('api_base_url') }}" required
                   placeholder="https://company1.timhr.es/api"
                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2.5 border">
            <p class="mt-1 text-xs text-gray-500">Base URL of the user's company backend</p>
        </div>

        <div>
            <label for="api_token" class="block text-sm font-medium text-gray-700 mb-1">API Token</label>
            <input type="text" name="api_token" id="api_token" value="{{ old('api_token') }}"
                   placeholder="Bearer token (if applicable)"
                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2.5 border">
        </div>

        <div class="flex items-center">
            <input type="checkbox" name="active" id="active" value="1" {{ old('active', true) ? 'checked' : '' }}
                   class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
            <label for="active" class="ml-2 text-sm text-gray-700">Active user</label>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit"
                    class="bg-blue-600 text-white font-medium py-2.5 px-6 rounded-lg hover:bg-blue-700 transition-colors text-sm">
                Create User
            </button>
            <a href="{{ route('admin.wa-users.index') }}" class="text-sm text-gray-500 hover:text-gray-700">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection

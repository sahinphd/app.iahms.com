<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Show the theme customization dashboard.
     */
    public function showTheme()
    {
        $logoText = Setting::get('site_logo_text', 'IAHMS LMS');
        $logoSubtext = Setting::get('site_logo_subtext', 'SECURE LEARNING');
        $primaryColor = Setting::get('theme_primary_color', '#8b5cf6');
        $bgColor = Setting::get('theme_bg_color', '#0f172a');
        $sidebarColor = Setting::get('theme_sidebar_color', '#020617');

        return view('admin.theme', compact('logoText', 'logoSubtext', 'primaryColor', 'bgColor', 'sidebarColor'));
    }

    /**
     * Update the theme settings.
     */
    public function updateTheme(Request $request)
    {
        $request->validate([
            'site_logo_text' => 'required|string|max:50',
            'site_logo_subtext' => 'nullable|string|max:100',
            'theme_primary_color' => ['required', 'string', 'regex:/^#[a-fA-F0-9]{6}$/'],
            'theme_bg_color' => ['required', 'string', 'regex:/^#[a-fA-F0-9]{6}$/'],
            'theme_sidebar_color' => ['required', 'string', 'regex:/^#[a-fA-F0-9]{6}$/'],
        ]);

        Setting::set('site_logo_text', $request->site_logo_text);
        Setting::set('site_logo_subtext', $request->site_logo_subtext ?? '');
        Setting::set('theme_primary_color', $request->theme_primary_color);
        Setting::set('theme_bg_color', $request->theme_bg_color);
        Setting::set('theme_sidebar_color', $request->theme_sidebar_color);

        return redirect()->back()->with('success', 'Theme and branding settings updated successfully!');
    }

    /**
     * Show storage configuration dashboard.
     */
    public function showStorageSettings()
    {
        $activeDriver = Setting::get('active_storage_driver', 'local');

        // GCP settings
        $gcpProjectId = Setting::get('gcp_project_id');
        $gcpBucket = Setting::get('gcp_bucket');
        $gcpKeyFile = Setting::get('gcp_key_file');

        // R2 settings
        $r2Endpoint = Setting::get('r2_endpoint');
        $r2Bucket = Setting::get('r2_bucket');
        $r2AccessKeyId = Setting::get('r2_access_key_id');
        $r2SecretAccessKey = Setting::get('r2_secret_access_key');
        $r2Region = Setting::get('r2_region', 'auto');

        // Mux settings
        $muxTokenId = Setting::get('mux_token_id');
        $muxTokenSecret = Setting::get('mux_token_secret');
        $muxSigningKeyId = Setting::get('mux_signing_key_id');
        $muxPrivateKey = Setting::get('mux_private_key');

        return view('admin.settings.storage', compact(
            'activeDriver',
            'gcpProjectId', 'gcpBucket', 'gcpKeyFile',
            'r2Endpoint', 'r2Bucket', 'r2AccessKeyId', 'r2SecretAccessKey', 'r2Region',
            'muxTokenId', 'muxTokenSecret', 'muxSigningKeyId', 'muxPrivateKey'
        ));
    }

    /**
     * Update storage settings.
     */
    public function updateStorageSettings(Request $request)
    {
        $request->validate([
            'active_storage_driver' => 'required|in:local,gcp,cloudflare,mux',
            
            // GCP
            'gcp_project_id' => 'nullable|required_if:active_storage_driver,gcp|string',
            'gcp_bucket' => 'nullable|required_if:active_storage_driver,gcp|string',
            'gcp_key_file' => 'nullable|required_if:active_storage_driver,gcp|string',

            // R2
            'r2_endpoint' => 'nullable|required_if:active_storage_driver,cloudflare|string',
            'r2_bucket' => 'nullable|required_if:active_storage_driver,cloudflare|string',
            'r2_access_key_id' => 'nullable|required_if:active_storage_driver,cloudflare|string',
            'r2_secret_access_key' => 'nullable|required_if:active_storage_driver,cloudflare|string',
            'r2_region' => 'nullable|string',

            // Mux
            'mux_token_id' => 'nullable|required_if:active_storage_driver,mux|string',
            'mux_token_secret' => 'nullable|required_if:active_storage_driver,mux|string',
            'mux_signing_key_id' => 'nullable|string',
            'mux_private_key' => 'nullable|string',
        ]);

        Setting::set('active_storage_driver', $request->active_storage_driver);

        // GCP
        Setting::set('gcp_project_id', $request->gcp_project_id ?? '');
        Setting::set('gcp_bucket', $request->gcp_bucket ?? '');
        Setting::set('gcp_key_file', $request->gcp_key_file ?? '');

        // R2
        Setting::set('r2_endpoint', $request->r2_endpoint ?? '');
        Setting::set('r2_bucket', $request->r2_bucket ?? '');
        Setting::set('r2_access_key_id', $request->r2_access_key_id ?? '');
        Setting::set('r2_secret_access_key', $request->r2_secret_access_key ?? '');
        Setting::set('r2_region', $request->r2_region ?? 'auto');

        // Mux
        Setting::set('mux_token_id', $request->mux_token_id ?? '');
        Setting::set('mux_token_secret', $request->mux_token_secret ?? '');
        Setting::set('mux_signing_key_id', $request->mux_signing_key_id ?? '');
        Setting::set('mux_private_key', $request->mux_private_key ?? '');

        return redirect()->back()->with('success', 'Storage settings saved successfully.');
    }
}

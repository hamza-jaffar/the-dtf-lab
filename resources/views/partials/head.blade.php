<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

@php
    $companyService = app(\App\Services\CompanySettingService::class);
    $companyName = $companyService->get(\App\Enums\CompanySettingKey::NAME) ?: config('app.name', 'Laravel');
    $companyLogo = $companyService->get(\App\Enums\CompanySettingKey::LOGO);
@endphp
<title>
    {{ filled($title ?? null) ? $title . ' - ' . $companyName : $companyName }}
</title>


@if($companyLogo)
    <link rel="icon" href="{{ $companyLogo }}" sizes="any">
    <link rel="icon" href="{{ $companyLogo }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ $companyLogo }}">
@else
    <link rel="icon" href="/logo.jpg" sizes="any">
    <link rel="icon" href="/logo.jpg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
@endif

@fonts

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
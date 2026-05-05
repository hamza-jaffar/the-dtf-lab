<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

@php
    $companyService = app(\App\Services\CompanySettingService::class);
    $companyName = $companyService->get(\App\Enums\CompanySettingKey::NAME) ?: config('app.name', 'Laravel');
@endphp
<title>
    {{ filled($title ?? null) ? $title.' - '.$companyName : $companyName }}
</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

@fonts

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance

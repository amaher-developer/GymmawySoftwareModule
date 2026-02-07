{{-- Report Header Partial - Professional business branding for PDF exports --}}
{{-- Uses $mainSettings (View::shared) and $lang --}}
@php
    $headerLang = $lang ?? 'ar';
    $logoRawFilename = $mainSettings->getRawOriginal('logo_' . $headerLang);
    if (!$logoRawFilename) {
        $logoRawFilename = $mainSettings->getRawOriginal('logo_ar') ?? $mainSettings->getRawOriginal('logo_en');
    }
    $logoPath = $logoRawFilename ? public_path('uploads/settings/' . $logoRawFilename) : null;
    if (!$logoPath || !file_exists($logoPath)) {
        $logoPath = $logoRawFilename ? base_path('uploads/settings/' . $logoRawFilename) : null;
    }
    $logoExists = false;
    $logoSrc = '';
    if ($logoPath && file_exists($logoPath)) {
        $ext = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
        if ($ext === 'webp' && function_exists('imagecreatefromwebp')) {
            // mPDF doesn't support WebP - convert to temp PNG file
            $img = @imagecreatefromwebp($logoPath);
            if ($img) {
                $tmpPng = sys_get_temp_dir() . '/report_logo_' . md5($logoPath) . '.png';
                imagepng($img, $tmpPng);
                imagedestroy($img);
                $logoSrc = str_replace('\\', '/', $tmpPng);
                $logoExists = true;
            }
        } elseif (in_array($ext, ['png', 'jpg', 'jpeg', 'gif'])) {
            $logoSrc = str_replace('\\', '/', $logoPath);
            $logoExists = true;
        }
    }
    $textAlignOpposite = $headerLang == 'ar' ? 'left' : 'right';
@endphp

<table style="width: 100%; border-collapse: collapse; margin-bottom: 0;">
    <tr>
        <td style="background-color: #1a3a5c; padding: 12px 15px; width: {{ $logoExists ? '75%' : '100%' }}; vertical-align: middle;">
            <div style="color: #ffffff; font-size: 16px; font-weight: bold; margin-bottom: 3px;">
                {{ $mainSettings->name_ar ?? '' }}
            </div>
            <div style="color: #c0c8d4; font-size: 11px;">
                {{ $mainSettings->name_en ?? '' }}
            </div>
        </td>
        @if($logoExists)
        <td style="background-color: #1a3a5c; padding: 10px 15px; width: 25%; text-align: center; vertical-align: middle;">
            <img src="{{ $logoSrc }}" style="max-height: 50px; max-width: 110px;" alt="Logo">
        </td>
        @endif
    </tr>
</table>

<table style="width: 100%; border-collapse: collapse; margin-bottom: 15px; border-top: 3px solid #2e86de;">
    <tr style="background-color: #f0f4f8;">
        <td style="padding: 5px 15px; font-size: 9px; color: #333333; border: none; width: 40%;">
            <span style="color: #888888;">{{ $headerLang == 'ar' ? 'العنوان' : 'Address' }}:</span>
            {{ $mainSettings->address ?? '' }}
        </td>
        <td style="padding: 5px 10px; font-size: 9px; color: #333333; border: none; width: 22%; text-align: center;">
            <span style="color: #888888;">{{ $headerLang == 'ar' ? 'الهاتف' : 'Phone' }}:</span>
            {{ $mainSettings->phone ?? '' }}
        </td>
        <td style="padding: 5px 10px; font-size: 9px; color: #333333; border: none; width: 23%; text-align: center;">
            <span style="color: #888888;">{{ $headerLang == 'ar' ? 'البريد الإلكتروني' : 'Email' }}:</span>
            {{ $mainSettings->support_email ?? '' }}
        </td>
        <td style="padding: 5px 15px; font-size: 9px; color: #666666; border: none; width: 15%; text-align: {{ $textAlignOpposite }};">
            {{ date('Y-m-d H:i') }}
        </td>
    </tr>
</table>

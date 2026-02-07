@extends('software::layouts.master')

@section('content')

<div class="card card-custom">
    <div class="card-header">
        <div class="card-title">
            <h3 class="card-label">{{ trans('sw.app_qr_code') }}</h3>
        </div>
    </div>
    <div class="card-body">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                {{-- Smart Link URL --}}
                <div class="mb-8 text-center">
                    <label class="form-label fw-bold fs-6 text-gray-700 mb-3">{{ trans('sw.smart_link_url') }}</label>
                    <div class="input-group">
                        <input type="text" id="smartLinkUrl" class="form-control form-control-solid text-center fs-6"
                               value="{{ $smartLinkUrl }}" readonly>
                        <button class="btn btn-light-primary" type="button" onclick="copyLink()">
                            <i class="fa fa-copy"></i>
                        </button>
                    </div>
                    <div class="text-muted fs-7 mt-2">{{ trans('sw.smart_link_description') }}</div>
                </div>

                {{-- QR Code Display --}}
                <div class="text-center mb-8">
                    <div class="d-inline-block p-6 rounded" style="background: #ffffff; border: 2px solid #e4e6ef;">
                        <div id="qrcode" style="display: inline-block;"></div>
                    </div>
                </div>

                {{-- Download Buttons --}}
                <div class="text-center mb-8">
                    <button class="btn btn-primary btn-lg me-3" onclick="downloadQR('png')">
                        <i class="fa fa-download me-2"></i> {{ trans('sw.download_png') }}
                    </button>
                    <button class="btn btn-light-primary btn-lg" onclick="downloadQR('svg')">
                        <i class="fa fa-download me-2"></i> {{ trans('sw.download_svg') }}
                    </button>
                </div>

                {{-- App Links Info --}}
                <div class="separator separator-dashed my-8"></div>

                <div class="mb-5">
                    <h5 class="fw-bold text-gray-800 mb-4">{{ trans('sw.redirect_rules') }}</h5>

                    <div class="d-flex align-items-center border border-dashed border-gray-300 rounded p-4 mb-3">
                        <i class="fab fa-apple fs-2x text-dark me-4"></i>
                        <div class="d-flex flex-column flex-grow-1">
                            <span class="fw-bold text-gray-800">iPhone / iPad</span>
                            <span class="text-muted fs-7">{{ $mainSettings->ios_app ?: trans('sw.not_configured') }}</span>
                        </div>
                        @if($mainSettings->ios_app)
                            <span class="badge badge-light-success">{{ trans('sw.active') }}</span>
                        @else
                            <span class="badge badge-light-danger">{{ trans('sw.not_set') }}</span>
                        @endif
                    </div>

                    <div class="d-flex align-items-center border border-dashed border-gray-300 rounded p-4 mb-3">
                        <i class="fab fa-google-play fs-2x text-success me-4"></i>
                        <div class="d-flex flex-column flex-grow-1">
                            <span class="fw-bold text-gray-800">Android</span>
                            <span class="text-muted fs-7">{{ $mainSettings->android_app ?: trans('sw.not_configured') }}</span>
                        </div>
                        @if($mainSettings->android_app)
                            <span class="badge badge-light-success">{{ trans('sw.active') }}</span>
                        @else
                            <span class="badge badge-light-danger">{{ trans('sw.not_set') }}</span>
                        @endif
                    </div>

                    <div class="d-flex align-items-center border border-dashed border-gray-300 rounded p-4">
                        <i class="fa fa-globe fs-2x text-primary me-4"></i>
                        <div class="d-flex flex-column flex-grow-1">
                            <span class="fw-bold text-gray-800">{{ trans('sw.other_devices') }}</span>
                            <span class="text-muted fs-7">{{ url('/') }}</span>
                        </div>
                        <span class="badge badge-light-primary">{{ trans('sw.website') }}</span>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="{{ asset('resources/assets/new_front/global/scripts/qr/qrcode.js') }}"></script>
<script>
    // Generate QR Code
    var qrcode = new QRCode(document.getElementById("qrcode"), {
        text: "{{ $smartLinkUrl }}",
        width: 280,
        height: 280,
        colorDark: "#1a3a5c",
        colorLight: "#ffffff",
        correctLevel: QRCode.CorrectLevel.H
    });

    function copyLink() {
        var input = document.getElementById('smartLinkUrl');
        input.select();
        document.execCommand('copy');
        toastr.success("{{ trans('sw.copied') }}");
    }

    function downloadQR(format) {
        var qrContainer = document.getElementById('qrcode');

        if (format === 'png') {
            var canvas = qrContainer.querySelector('canvas');
            if (canvas) {
                // Create a new canvas with padding and logo space
                var padding = 40;
                var newCanvas = document.createElement('canvas');
                newCanvas.width = canvas.width + (padding * 2);
                newCanvas.height = canvas.height + (padding * 2);
                var ctx = newCanvas.getContext('2d');

                // White background
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(0, 0, newCanvas.width, newCanvas.height);

                // Draw QR code centered
                ctx.drawImage(canvas, padding, padding);

                var link = document.createElement('a');
                link.download = 'app-qr-code.png';
                link.href = newCanvas.toDataURL('image/png');
                link.click();
            }
        } else if (format === 'svg') {
            var img = qrContainer.querySelector('img');
            var canvas = qrContainer.querySelector('canvas');
            var src = img ? img.src : (canvas ? canvas.toDataURL('image/png') : '');

            // Create SVG wrapping the QR image
            var size = 280;
            var padding = 40;
            var totalSize = size + (padding * 2);
            var svgContent = '<?xml version="1.0" encoding="UTF-8"?>\n' +
                '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" ' +
                'width="' + totalSize + '" height="' + totalSize + '" viewBox="0 0 ' + totalSize + ' ' + totalSize + '">' +
                '<rect width="' + totalSize + '" height="' + totalSize + '" fill="#ffffff"/>' +
                '<image x="' + padding + '" y="' + padding + '" width="' + size + '" height="' + size + '" href="' + src + '"/>' +
                '</svg>';

            var blob = new Blob([svgContent], {type: 'image/svg+xml'});
            var link = document.createElement('a');
            link.download = 'app-qr-code.svg';
            link.href = URL.createObjectURL(blob);
            link.click();
            URL.revokeObjectURL(link.href);
        }
    }
</script>
@endsection

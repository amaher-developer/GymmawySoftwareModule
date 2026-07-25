{{-- ═══════════════════════════════════════════════════════════════════════
     notifications.blade.php
     Included once from master.blade.php — no other file should include it.
     Dependencies already loaded by master before this file:
       • jQuery (plugins.bundle.js)
       • Bootstrap 5 (plugins.bundle.js) → use bootstrap.Modal, NOT $.modal()
       • Select2 (plugins.bundle.js)      → do NOT re-load from CDN
       • SweetAlert2 v11 (plugins.bundle.js) → Swal.fire() works
       • Pusher 7 loaded HERE
═══════════════════════════════════════════════════════════════════════ --}}

{{-- ── Bell shake animation ─────────────────────────────────────────────── --}}
<style>
@keyframes bell-shake {
    0%,100% { transform: rotate(0deg); }
    10%      { transform: rotate(-18deg); }
    20%      { transform: rotate(18deg); }
    30%      { transform: rotate(-14deg); }
    40%      { transform: rotate(14deg); }
    50%      { transform: rotate(-10deg); }
    60%      { transform: rotate(10deg); }
    70%      { transform: rotate(-6deg); }
    80%      { transform: rotate(6deg); }
    90%      { transform: rotate(-2deg); }
}
.bell-ringing {
    display: inline-block;
    animation: bell-shake 0.9s ease both;
    transform-origin: top center;
}
</style>

{{-- ── Send-notification modal ─────────────────────────────────────────── --}}
<div class="modal fade" id="modelNotification" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">{{ trans('sw.send_notification_to_users') }}</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="sendNotificationForm" action="{{ route('sw.sendToUsers') }}" method="GET">
                    <div class="mb-4">
                        <label class="form-label fw-bold">{{ trans('sw.message') }}</label>
                        <textarea id="content_notification_form" name="content"
                                  class="form-control form-control-solid" rows="4" required
                                  placeholder="{{ trans('sw.message') }}"
                                  style="resize:vertical;"></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold">{{ trans('sw.select_users') }}</label>
                        <select id="select_managers" name="users[]"
                                class="form-control form-control-solid notif-select2"
                                required multiple="multiple"
                                data-placeholder="{{ trans('sw.select_users') }}">
                        </select>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            {{ trans('sw.cancel') }}
                        </button>
                        <button class="btn btn-primary" type="submit">
                            <i class="ki-outline ki-send me-2"></i>{{ trans('sw.send_notification') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ── Read-notification modal ──────────────────────────────────────────── --}}
<div class="modal fade" id="modelReadNotification" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title"><i class="fa fa-bell me-2"></i>{{ trans('sw.notification') }}</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="readNotificationMessage"></p>
            </div>
        </div>
    </div>
</div>

{{-- ── Pusher ───────────────────────────────────────────────────────────── --}}
<script src="https://js.pusher.com/7.0/pusher.min.js"></script>

<script>
(function () {
    'use strict';

    // ── helpers ────────────────────────────────────────────────────────────

    /** Safe Bootstrap 5 modal show */
    function showModal(id) {
        var el = document.getElementById(id);
        if (!el) return;
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(el).show();
        } else if (typeof $ !== 'undefined') {
            $(el).modal('show');
        }
    }

    /** Safe Bootstrap 5 modal hide */
    function hideModal(id) {
        var el = document.getElementById(id);
        if (!el) return;
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            var inst = bootstrap.Modal.getInstance(el);
            if (inst) inst.hide();
        } else if (typeof $ !== 'undefined') {
            $(el).modal('hide');
        }
    }

    // ── Audio via Web Audio API (no file needed, no URL issues) ──────────────
    var _audioCtx = null;

    // Create/resume AudioContext on first user click — required by browser autoplay policy
    document.addEventListener('click', function () {
        if (_audioCtx) {
            if (_audioCtx.state === 'suspended') _audioCtx.resume();
            return;
        }
        var AC = window.AudioContext || window.webkitAudioContext;
        if (AC) _audioCtx = new AC();
    });

    if (typeof window.notifytone === 'undefined') {
        window.notifytone = function () {
            try {
                var AC = window.AudioContext || window.webkitAudioContext;
                if (!AC) return;
                if (!_audioCtx) _audioCtx = new AC();
                if (_audioCtx.state === 'suspended') _audioCtx.resume();

                // Two-tone notification beep
                [660, 880].forEach(function (freq, i) {
                    var o = _audioCtx.createOscillator();
                    var g = _audioCtx.createGain();
                    o.connect(g);
                    g.connect(_audioCtx.destination);
                    o.type = 'sine';
                    o.frequency.value = freq;
                    var t = _audioCtx.currentTime + i * 0.18;
                    g.gain.setValueAtTime(0.35, t);
                    g.gain.exponentialRampToValueAtTime(0.001, t + 0.25);
                    o.start(t);
                    o.stop(t + 0.25);
                });
            } catch (e) {}
        };
    }

    /** Animate the bell icon */
    function ringBell() {
        var bell = document.getElementById('notification_bell_icon');
        if (!bell) return;
        bell.classList.remove('bell-ringing');
        void bell.offsetWidth; // force reflow so animation restarts
        bell.classList.add('bell-ringing');
        setTimeout(function () { bell.classList.remove('bell-ringing'); }, 900);
    }

    // ── Select2 for the send-notification modal ────────────────────────────
    // Select2 is already bundled in plugins.bundle.js — no CDN re-load needed.
    // We initialise only our specific select with a unique class to avoid
    // touching any other select2 instance on the page.
    function initNotifSelect2() {
        var el = document.getElementById('select_managers');
        if (!el) return;
        if (typeof $ !== 'undefined' && $.fn.select2) {
            if (!$(el).hasClass('select2-hidden-accessible')) {
                $(el).select2({
                    placeholder: '{{ trans('sw.select_users') }}',
                    allowClear: true,
                    dropdownParent: $('#modelNotification'),
                    width: '100%',
                });
            }
        }
    }
    // Init once DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initNotifSelect2);
    } else {
        initNotifSelect2();
    }

    // ── #side_notification button → open send modal ────────────────────────
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('#side_notification');
        if (!btn) return;
        e.preventDefault();

        document.getElementById('content_notification_form').value = '';

        $.ajax({
            url: '{{ route('sw.listUserJson') }}',
            type: 'get',
            success: function (data) {
                var opts = '<option value=""></option>';
                for (var i = 0; i < data.length; i++) {
                    opts += '<option value="' + data[i]['id'] + '">' + data[i]['name'] + '</option>';
                }
                var $sel = $('#select_managers');
                $sel.html(opts);
                if ($sel.hasClass('select2-hidden-accessible')) {
                    $sel.trigger('change.select2');
                }
                showModal('modelNotification');
            },
            error: function () {
                showModal('modelNotification');
            }
        });
    });

    // ── Send form via AJAX (prevent page reload) ───────────────────────────
    document.addEventListener('submit', function (e) {
        var form = e.target.closest('#sendNotificationForm');
        if (!form) return;
        e.preventDefault();

        var content = document.getElementById('content_notification_form').value.trim();
        var users   = $('#select_managers').val();

        if (!content) {
            alert('{{ trans('sw.message') }}?');
            return;
        }
        if (!users || users.length === 0) {
            alert('{{ trans('sw.select_users') }}?');
            return;
        }

        var submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;

        var params = 'content=' + encodeURIComponent(content);
        for (var i = 0; i < users.length; i++) {
            params += '&users[]=' + encodeURIComponent(users[i]);
        }

        $.ajax({
            url: '{{ route('sw.sendToUsers') }}',
            type: 'get',
            data: params,
            success: function () {
                hideModal('modelNotification');
                document.getElementById('content_notification_form').value = '';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: '{{ trans('sw.send_notification') }}',
                        toast: true,
                        position: document.documentElement.dir === 'rtl' ? 'top-start' : 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                    });
                }
            },
            error: function (xhr) {
                console.error('[sendNotif] error', xhr.status);
                alert('Error ' + xhr.status);
            },
            complete: function () {
                submitBtn.disabled = false;
            }
        });
    });

    // ── markAsRead (called from notification items onclick) ────────────────
    window.markAsRead = function (id, message, url) {
        $.ajax({
            url: '{{ route('sw.markAsRead') }}',
            data: { id: id },
            type: 'get',
            success: function () {
                var body = message || '';
                if (url && url !== 'null') {
                    body += '<br><br><a href="' + url + '">{{ trans('sw.press_here') }}</a>';
                }
                document.getElementById('readNotificationMessage').innerHTML = body;
                showModal('modelReadNotification');

                // Hide the notification dot if no more unread
                var dot = document.getElementById('notification_dot');
                // We don't know the exact count here, so just let the next page load handle it.
                // Optionally remove the newly-added live item if it has a data-id:
                var liveItem = document.querySelector('[data-notif-id="' + id + '"]');
                if (liveItem) liveItem.remove();
            },
            error: function (xhr) {
                console.error('[markAsRead] error', xhr.status);
            }
        });
    };

    // ── Pusher ─────────────────────────────────────────────────────────────
    Pusher.logToConsole = true;

    var pusher = new Pusher('098b6ce982918904e9e7', { cluster: 'eu' });

    @php
        $pusherChannel = 'my-channel.'
            . ($mainSettings['token'] ?? '')
            . '.'
            . (auth()->guard('sw')->id() ?? '');
    @endphp
    var _channelName = '{{ $pusherChannel }}';
    console.log('[Pusher] channel:', _channelName);

    pusher.connection.bind('connected',    function () { console.log('[Pusher] connected'); });
    pusher.connection.bind('disconnected', function () { console.log('[Pusher] disconnected'); });
    pusher.connection.bind('error',        function (e) { console.error('[Pusher] error', e); });

    var channel = pusher.subscribe(_channelName);

    channel.bind('pusher:subscription_succeeded', function () {
        console.log('[Pusher] subscribed to', _channelName);
    });
    channel.bind('pusher:subscription_error', function (e) {
        console.error('[Pusher] subscription_error', e);
    });

    channel.bind('my-event', function (data) {
        console.log('[Pusher] my-event:', data);

        // 1. Sound
        window.notifytone();

        // 2. Bell shake
        ringBell();

        // 3. Show red dot
        var dot = document.getElementById('notification_dot');
        if (dot) dot.classList.remove('d-none');

        // 4. Prepend item in dropdown list
        var container = document.getElementById('notifications_list_container');
        if (container) {
            var item = document.createElement('div');
            item.className = 'd-flex flex-stack py-4 border-bottom';
            item.setAttribute('data-notif-id', data.user_id || '');
            item.innerHTML =
                '<div class="d-flex align-items-center">' +
                    '<div class="symbol symbol-35px me-4">' +
                        '<span class="symbol-label bg-light-warning">' +
                            '<i class="ki-outline ki-notification fs-2 text-warning"></i>' +
                        '</span>' +
                    '</div>' +
                    '<div class="mb-0 me-2">' +
                        '<a href="javascript:;" class="fs-6 text-gray-800 text-hover-primary fw-bold"' +
                           ' onclick="markAsRead(' +
                               JSON.stringify(data.user_id || '') + ',' +
                               JSON.stringify(data.message || '') + ',' +
                               JSON.stringify(data.url || '') +
                           ')">' +
                            (data.title || '') +
                        '</a>' +
                        '<div class="text-gray-500 fs-7">' + (data.created_at || '') + '</div>' +
                    '</div>' +
                '</div>';
            container.insertBefore(item, container.firstChild);
        }

        // 5. Toast (SweetAlert2 v11 — already in plugins.bundle)
        if (typeof Swal !== 'undefined') {
            var msg = data.message || '';
            if (data.url && data.url !== 'null' && data.url !== '') {
                msg += '<br><a href="' + data.url + '" style="color:#fff;font-weight:bold;text-decoration:underline;">{{ trans('sw.press_here') }}</a>';
            }
            var isRtl = document.documentElement.dir === 'rtl' || document.body.dir === 'rtl';
            Swal.fire({
                title: '🔔 ' + (data.title || '{{ trans('sw.new_notification') }}'),
                html: msg,
                toast: true,
                position: isRtl ? 'top-start' : 'top-end',
                showConfirmButton: false,
                timer: 8000,
                timerProgressBar: true,
                didOpen: function (toast) {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });
        }
    });

}()); // end IIFE — nothing leaks to global scope except notifytone, markAsRead, pusher, channel
</script>

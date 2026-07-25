<?php

namespace Modules\Software\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Modules\Generic\Http\Controllers\Api\FirebaseApiController;
use Modules\Generic\Models\Setting;
use Modules\Software\Models\GymPTMember;
use Modules\Software\Models\GymPushNotification;
use Illuminate\Support\Facades\Log;

/**
 * Send push notifications to PT members whose class is scheduled for tomorrow.
 *
 * Schedule: daily  (register in SoftwareServiceProvider::schedule())
 * Run manually:  php artisan pt:notify-classes-tomorrow
 */
class NotifyPTClassesTomorrow extends Command
{
    protected $signature = 'pt:notify-classes-tomorrow';

    protected $description = 'Notify PT members one day before their scheduled class';

    public function handle(): int
    {
        $tomorrow    = Carbon::tomorrow();
        $tomorrowStr = $tomorrow->toDateString();
        $dayOfWeek   = $tomorrow->dayOfWeek; // 0=Sunday … 6=Saturday

        $settings = Setting::first();
        $gymName  = $settings->name ?? config('app.name');

        // Load all memberships active on tomorrow, with the class and its push-tokens
        $ptMembers = GymPTMember::with([
                'class.activeClassTrainers.trainer',
                'class.pt_subscription',
                'legacyClass.pt_subscription',
                'member',
            ])
            ->whereDate('joining_date', '<=', $tomorrowStr)
            ->whereDate('expire_date',  '>=', $tomorrowStr)
            ->get();

        $sent  = 0;
        $skip  = 0;

        /** @var FirebaseApiController $firebase */
        $firebase = new FirebaseApiController();

        foreach ($ptMembers as $ptMember) {
            $memberId = $ptMember->member_id;
            if (!$memberId) { $skip++; continue; }

            // Resolve class (new or legacy schema)
            $ptClass = $ptMember->class ?? $ptMember->legacyClass ?? null;
            if (!$ptClass) { $skip++; continue; }

            // Check work_days for tomorrow's day-of-week
            $workDays = $ptClass->schedule['work_days'] ?? [];
            $daySlot  = $workDays[$dayOfWeek] ?? null;
            if (!$daySlot || empty($daySlot['status'])) { $skip++; continue; }

            $subscriptionName = $ptClass->pt_subscription->name ?? $gymName;
            $startTime        = Carbon::parse($daySlot['start'] ?? '00:00')->format('g:i A');

            $title = $gymName;
            // Use the system default locale
            $locale = config('app.locale', 'ar');
            
            if ($locale === 'ar') {
                $body = "تذكير: لديك تدريب {$subscriptionName} غداً في {$startTime}.";
            } else {
                $body = "Reminder: You have a {$subscriptionName} class tomorrow at {$startTime}.";
            }
    

            $data = [
                'title'  => $title,
                'body'   => $body,
                'sound'  => 'default',
                'badge'  => '1',
                'e'      => '1',
                'type'   => 'pt_class_reminder',
                'image'  => $settings->logo ?? '',
            ];

            try {
                $result = $firebase->push([$memberId], $data);

                // Persist notification record
                GymPushNotification::create([
                    'title'             => $title,
                    'body'              => $data,
                    'member_id'         => $memberId,
                    'notification_id'   => $result->message_id ?? null,
                    'branch_setting_id' => $ptMember->branch_setting_id ?? null,
                ]);

                $sent++;
                $memberName = optional($ptMember->member)->name ?? '';
                $this->line("  Sent to member #{$memberId} ({$memberName})");
            } catch (\Throwable $e) {
                Log::error('NotifyPTClassesTomorrow: push failed for member #' . $memberId . ' — ' . $e->getMessage());
                $skip++;
            }
        }

        $this->info("Done. Sent: {$sent}, Skipped: {$skip}.");

        return self::SUCCESS;
    }
}

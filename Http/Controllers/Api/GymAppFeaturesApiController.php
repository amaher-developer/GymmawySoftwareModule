<?php

namespace Modules\Software\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Generic\Models\Setting;
use Modules\Software\Models\GymMember;
use Modules\Software\Models\GymPTTrainer;
use Modules\Software\Models\GymSuggestion;
use Modules\Software\Models\LoyaltyTransaction;

class GymAppFeaturesApiController extends GymGenericApiController
{
    // ── Loyalty Points ─────────────────────────────────────────────────────────

    public function loyaltyPoints()
    {
        $member = GymMember::where('id', Auth::guard('api')->user()->id)->first();

        $balance  = (int)($member->loyalty_points_balance ?? 0);
        $transactions = LoyaltyTransaction::where('member_id', $member->id)
            ->orderBy('id', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($t) {
                return [
                    'id'          => $t->id,
                    'points'      => $t->points,
                    'type'        => $t->type,
                    'source_type' => $t->source_type,
                    'reason'      => $t->reason,
                    'date'        => Carbon::parse($t->created_at)->translatedFormat('d F Y'),
                ];
            });

        $this->return['result']['balance']      = $balance;
        $this->return['result']['transactions'] = $transactions;

        return $this->successResponse();
    }

    // ── Trainers ───────────────────────────────────────────────────────────────

    public function trainers()
    {
        $trainers = GymPTTrainer::orderBy('name')
            ->get()
            ->map(function ($t) {
                return [
                    'id'             => $t->id,
                    'name'           => $t->name,
                    'image'          => $t->image,
                    'phone'          => $t->phone,
                    'price'          => $t->price ?? '',
                    'specialization' => $t->specialization ?? '',
                    'bio'            => $t->bio ?? '',
                ];
            });

        $this->return['result']['trainers'] = $trainers;

        return $this->successResponse();
    }

    public function trainer($id)
    {
        $trainer = GymPTTrainer::find($id);

        if (!$trainer) {
            return $this->falseResponse(trans('sw.not_found'));
        }

        $this->return['result']['trainer'] = [
            'id'             => $trainer->id,
            'name'           => $trainer->name,
            'image'          => $trainer->image,
            'phone'          => $trainer->phone,
            'price'          => $trainer->price ?? '',
            'specialization' => $trainer->specialization ?? '',
            'bio'            => $trainer->bio ?? '',
            'work_hours'     => $trainer->work_hours ?? '',
        ];

        return $this->successResponse();
    }

    // ── Suggestions & Complaints ───────────────────────────────────────────────

    public function storeSuggestion(Request $request)
    {
        if (!$this->validateApiRequest(['message'])) return $this->response;

        $member = Auth::guard('api')->user();

        GymSuggestion::create([
            'member_id'        => $member?->id,
            'type'             => in_array($request->type, ['suggestion', 'complaint']) ? $request->type : 'suggestion',
            'name'             => $request->name ?? $member?->name,
            'phone'            => $request->phone ?? $member?->phone,
            'message'          => $request->message,
            'branch_setting_id'=> 1,
        ]);

        $this->message = trans('sw.contact_add_successfully');

        return $this->successResponse();
    }

    // ── About / Terms / Policy ────────────────────────────────────────────────

    public function about()
    {
        $setting = Setting::select('about_ar', 'about_en', 'images')->first();

        $this->return['result']['content_ar'] = $setting->about_ar ?? '';
        $this->return['result']['content_en'] = $setting->about_en ?? '';

        return $this->successResponse();
    }

    public function terms()
    {
        $setting = Setting::select('terms_ar', 'terms_en')->first();

        $this->return['result']['content_ar'] = $setting->terms_ar ?? '';
        $this->return['result']['content_en'] = $setting->terms_en ?? '';

        return $this->successResponse();
    }

    public function policy()
    {
        $setting = Setting::select('terms_ar', 'terms_en')->first();

        // policy and terms share the same field unless a dedicated policy field is added
        $this->return['result']['content_ar'] = $setting->terms_ar ?? '';
        $this->return['result']['content_en'] = $setting->terms_en ?? '';

        return $this->successResponse();
    }
}

<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\GuildUnion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ComplaintController extends Controller
{
    public function create(Request $request, ?string $union = null): View
    {
        $selectedUnionId = $request->query('union_id');

        if (filled($union)) {
            $selectedUnionId = GuildUnion::query()
                ->active()
                ->where(fn ($query) => $query->whereKey($union)->orWhere('slug', $union))
                ->value('id') ?: $selectedUnionId;
        }

        return view('frontend.complaints.create', [
            'unions' => $this->enabledUnions(),
            'selectedUnionId' => $selectedUnionId,
        ]);
    }

    public function store(Request $request): View|RedirectResponse
    {
        $validated = $request->validate([
            'union_id' => [
                'required',
                Rule::exists('unions', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'full_name' => ['required', 'string', 'max:255'],
            'national_code' => ['nullable', 'string', 'max:20'],
            'mobile' => ['required', 'string', 'max:20'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
            'attachment' => ['nullable', 'file', 'max:10240', 'mimetypes:image/jpeg,image/png,image/webp,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        ], [], [
            'union_id' => 'اتحادیه',
            'full_name' => 'نام و نام خانوادگی',
            'national_code' => 'کد ملی',
            'mobile' => 'شماره موبایل',
            'subject' => 'موضوع',
            'body' => 'شرح شکایت',
            'attachment' => 'پیوست',
        ]);

        $attachment = $request->hasFile('attachment')
            ? $request->file('attachment')->store('complaints/attachments', 'public')
            : null;

        $complaint = Complaint::create([
            ...$validated,
            'tracking_code' => $this->generateTrackingCode(),
            'attachment' => $attachment,
            'status' => 'registered',
        ]);

        $complaint->load('union');

        return view('frontend.complaints.result', [
            'complaint' => $complaint,
            'isNew' => true,
        ]);
    }

    public function track(): View
    {
        return view('frontend.complaints.track');
    }

    public function trackResult(Request $request): View|RedirectResponse
    {
        $validated = $request->validate([
            'tracking_code' => ['required', 'string', 'max:30'],
            'mobile' => ['required', 'string', 'max:20'],
        ], [], [
            'tracking_code' => 'کد رهگیری',
            'mobile' => 'شماره موبایل',
        ]);

        $complaint = Complaint::query()
            ->with(['union', 'answerer'])
            ->where('tracking_code', Str::upper($validated['tracking_code']))
            ->where('mobile', $validated['mobile'])
            ->first();

        if (! $complaint) {
            return back()->withInput()->withErrors(['tracking_code' => 'شکایتی با این کد رهگیری و شماره موبایل پیدا نشد.']);
        }

        return view('frontend.complaints.result', [
            'complaint' => $complaint,
            'isNew' => false,
        ]);
    }


    public function lookup(Request $request): View|RedirectResponse
    {
        return $this->trackResult($request);
    }

    private function enabledUnions()
    {
        return GuildUnion::query()
            ->active()
            ->orderBy('title')
            ->get();
    }

    private function generateTrackingCode(): string
    {
        do {
            $code = 'CMP'.now()->format('ymd').Str::upper(Str::random(6));
        } while (Complaint::query()->where('tracking_code', $code)->exists());

        return $code;
    }
}

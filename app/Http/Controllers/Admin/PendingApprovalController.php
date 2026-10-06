<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ContentApprovalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PendingApprovalController extends Controller
{
    public function index(Request $request, ContentApprovalService $approvalService): View
    {
        $allVisibleItems = $approvalService->pendingItems(null, $request->user());
        $search = trim((string) $request->query('search', ''));
        $type = (string) $request->query('type', '');
        $typeOptions = $allVisibleItems->pluck('label', 'type')->unique();

        // Filter only the already permission-scoped queue; never query
        // another content module to fulfill a search request.
        $items = $allVisibleItems
            ->when($type !== '', fn ($items) => $items->where('type', $type))
            ->when($search !== '', fn ($items) => $items->filter(
                fn (array $item): bool => mb_stripos((string) ($item['title'] ?? ''), $search) !== false
                    || mb_stripos((string) ($item['summary'] ?? ''), $search) !== false
            ))
            ->values();

        return view('admin.pending_approvals.index', [
            'items' => $items,
            'totalVisibleItems' => $allVisibleItems->count(),
            'typeOptions' => $typeOptions,
            'search' => $search,
            'type' => $type,
        ]);
    }

    public function approve(Request $request, ContentApprovalService $approvalService, string $type, int $id): RedirectResponse
    {
        $approvalService->ensureCanModerate($request->user(), $type, 'approve');
        $approvalService->approve($approvalService->find($type, $id), $request->user());

        return back()->with('success', 'محتوا با موفقیت تایید شد.');
    }


    public function publish(Request $request, ContentApprovalService $approvalService, string $type, int $id): RedirectResponse
    {
        $approvalService->ensureCanModerate($request->user(), $type, 'publish');
        $approvalService->publish($approvalService->find($type, $id), $request->user());

        return back()->with('success', 'محتوا با موفقیت تایید و منتشر شد.');
    }

    public function archive(Request $request, ContentApprovalService $approvalService, string $type, int $id): RedirectResponse
    {
        $approvalService->ensureCanModerate($request->user(), $type, 'archive');
        $approvalService->archive($approvalService->find($type, $id), $request->user());

        return back()->with('success', 'محتوا با موفقیت آرشیو شد.');
    }

    public function reject(Request $request, ContentApprovalService $approvalService, string $type, int $id): RedirectResponse
    {
        $approvalService->ensureCanModerate($request->user(), $type, 'reject');
        $validated = $request->validate([
            'rejected_reason' => ['required', 'string', 'max:1000'],
        ], [], [
            'rejected_reason' => 'دلیل رد شدن',
        ]);

        $approvalService->reject($approvalService->find($type, $id), $request->user(), $validated['rejected_reason']);

        return back()->with('success', 'محتوا با ثبت دلیل، رد شد.');
    }
}

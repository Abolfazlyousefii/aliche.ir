<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ContentApprovalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PendingApprovalController extends Controller
{
    public function index(ContentApprovalService $approvalService): View
    {
        return view('admin.pending_approvals.index', [
            'items' => $approvalService->pendingItems(),
            'statusLabels' => ContentApprovalService::statusLabels(),
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

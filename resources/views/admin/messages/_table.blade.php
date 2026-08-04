<div class="table-responsive">
    <table class="admin-table">
        <thead>
            <tr>
                <th>عنوان پیام</th>
                <th>فرستنده</th>
                <th>گیرنده</th>
                <th>اولویت</th>
                <th>وضعیت</th>
                <th>تاریخ ارسال</th>
                <th>عملیات</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($messages as $message)
                <tr @class(['table-warning' => $message->recipient_id === auth()->id() && ! $message->read_at])>
                    <td>
                        <strong>{{ $message->subject }}</strong>
                        <small class="d-block text-muted">{{ $message->typeLabel() }}</small>
                    </td>
                    <td>{{ $message->sender?->name ?? 'سیستم' }}</td>
                    <td>{{ $message->recipient?->name ?? 'حذف‌شده' }}</td>
                    <td><span class="badge bg-{{ $message->priority === 'urgent' ? 'danger' : ($message->priority === 'important' ? 'warning text-dark' : 'secondary') }}">{{ $message->priorityLabel() }}</span></td>
                    <td>
                        @if ($message->read_at)
                            <span class="badge bg-success">خوانده‌شده</span>
                        @else
                            <span class="badge bg-danger">خوانده‌نشده</span>
                        @endif
                    </td>
                    <td>{{ $message->sent_at ? jdate($message->sent_at)->format('Y/m/d H:i') : jdate($message->created_at)->format('Y/m/d H:i') }}</td>
                    <td>
                        <div class="admin-actions">
                            <a class="admin-secondary-btn" href="{{ route('admin.messages.show', $message) }}">مشاهده</a>
                            @if (request()->user()->hasPermission('messages.delete'))
                                <form method="POST" action="{{ route('admin.messages.destroy', $message) }}" data-admin-delete-form data-delete-message="پیام «{{ $message->subject }}» حذف شود؟">
                                    @csrf
                                    @method('DELETE')
                                    <button class="admin-danger-btn" type="submit">حذف</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted">پیامی برای نمایش وجود ندارد.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@include('admin.partials.pagination', ['paginator' => $messages])

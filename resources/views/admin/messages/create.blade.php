@extends('admin.layouts.app')

@section('title', 'ارسال پیام داخلی')

@section('content')
<div class="admin-page-toolbar">
    <div>
        <p class="admin-eyebrow">پیام‌های داخلی</p>
        <h2>ارسال پیام جدید</h2>
    </div>
    <div class="admin-actions">
        <a class="admin-secondary-btn" href="{{ route('admin.messages.inbox') }}">صندوق ورودی</a>
        <a class="admin-secondary-btn" href="{{ route('admin.messages.sent') }}">ارسال‌شده‌ها</a>
    </div>
</div>

<form class="admin-panel-card" method="POST" action="{{ route('admin.messages.store') }}">
    @csrf
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label" for="send_type">نوع ارسال</label>
            <select class="form-control" id="send_type" name="send_type" required>
                <option value="direct" @selected(old('send_type', 'direct') === 'direct')>ارسال به یک کاربر</option>
                <option value="selected" @selected(old('send_type') === 'selected')>ارسال به چند کاربر انتخاب‌شده</option>
                <option value="role" @selected(old('send_type') === 'role')>ارسال به همه کاربران یک نقش</option>
                <option value="union" @selected(old('send_type') === 'union')>ارسال به کاربران یک اتحادیه</option>
                <option value="broadcast" @selected(old('send_type') === 'broadcast')>ارسال به همه کاربران پنل</option>
            </select>
            @error('send_type')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-4 js-message-target" data-target="direct">
            <label class="form-label" for="recipient_id">گیرنده</label>
            <select class="form-control" id="recipient_id" name="recipient_id">
                <option value="">انتخاب کاربر</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" @selected((string) old('recipient_id') === (string) $user->id)>{{ $user->name }} @if($user->mobile) - {{ $user->mobile }} @elseif($user->email) - {{ $user->email }} @endif</option>
                @endforeach
            </select>
            @error('recipient_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-4 js-message-target" data-target="role">
            <label class="form-label" for="role_id">نقش</label>
            <select class="form-control" id="role_id" name="role_id">
                <option value="">انتخاب نقش</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->id }}" @selected((string) old('role_id') === (string) $role->id)>{{ $role->label ?: $role->name }}</option>
                @endforeach
            </select>
            @error('role_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-4 js-message-target" data-target="union">
            <label class="form-label" for="union_id">اتحادیه</label>
            <select class="form-control" id="union_id" name="union_id">
                <option value="">انتخاب اتحادیه</option>
                @foreach ($unions as $union)
                    <option value="{{ $union->id }}" @selected((string) old('union_id') === (string) $union->id)>{{ $union->title ?: $union->name }}</option>
                @endforeach
            </select>
            @error('union_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>

        <div class="col-12 js-message-target" data-target="selected">
            <label class="form-label">گیرندگان انتخاب‌شده</label>
            <div class="border rounded p-3" style="max-height:320px;overflow:auto">
                <div class="row g-2">
                    @forelse ($users as $user)
                        <div class="col-md-4">
                            <label class="border rounded p-2 d-block h-100">
                                <input type="checkbox" name="recipient_ids[]" value="{{ $user->id }}" @checked(in_array($user->id, old('recipient_ids', [])))>
                                <strong>{{ $user->name }}</strong>
                                <small class="d-block text-muted">{{ $user->mobile ?: $user->email }}</small>
                            </label>
                        </div>
                    @empty
                        <div class="col-12 text-muted">کاربر فعالی برای انتخاب وجود ندارد.</div>
                    @endforelse
                </div>
            </div>
            @error('recipient_ids')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-8">
            <label class="form-label" for="subject">عنوان پیام</label>
            <input class="form-control" id="subject" name="subject" value="{{ old('subject') }}" maxlength="255" required>
            @error('subject')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-4">
            <label class="form-label" for="priority">اولویت</label>
            <select class="form-control" id="priority" name="priority" required>
                @foreach (['low' => 'کم', 'normal' => 'عادی', 'important' => 'مهم', 'urgent' => 'فوری'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('priority', 'normal') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('priority')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>

        <div class="col-12">
            <label class="form-label" for="body">متن پیام</label>
            <textarea class="form-control js-rich-editor" id="body" name="body" rows="8" required>{{ old('body') }}</textarea>
            @error('body')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>

        <div class="col-12">
            <input type="hidden" name="allow_reply" value="0">
            <label class="form-check-label">
                <input class="form-check-input" type="checkbox" name="allow_reply" value="1" @checked(old('allow_reply', '1'))>
                اجازه پاسخ به این پیام داده شود
            </label>
        </div>
    </div>

    <div class="mt-3 d-flex gap-2 flex-wrap">
        <button class="admin-primary-btn" type="submit">ارسال پیام</button>
        <a class="admin-secondary-btn" href="{{ route('admin.messages.index') }}">انصراف</a>
    </div>
</form>
@endsection

@push('scripts')
<script>
(() => {
    const sendType = document.getElementById('send_type');
    const sections = Array.from(document.querySelectorAll('.js-message-target'));

    function updateVisibility() {
        const value = sendType.value;
        sections.forEach(section => {
            section.style.display = section.dataset.target === value ? '' : 'none';
        });
    }

    sendType.addEventListener('change', updateVisibility);
    updateVisibility();
})();
</script>
@endpush

@extends('admin.layouts.app')

@section('title', 'ارسال پیامک اطلاع‌رسانی')

@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">پیامک اطلاع‌رسانی</p><h2>ارسال پیامک</h2></div>
    <a class="admin-secondary-btn" href="{{ route('admin.sms.index') }}">بازگشت</a>
</div>

<form class="admin-panel-card admin-form" action="{{ route('admin.sms.store') }}" method="POST" id="smsForm">
    @csrf
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label" for="send_type">نوع ارسال</label>
            <select class="form-control" id="send_type" name="send_type" required>
                <option value="">انتخاب نوع ارسال</option>
                @foreach ($sendTypeLabels as $sendType => $label)
                    @continue($sendType === 'all' && ! $isSuperAdmin)
                    <option value="{{ $sendType }}" @selected(old('send_type') === $sendType)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4" id="unionWrapper">
            <label class="form-label" for="union_id">اتحادیه</label>
            <select class="form-control" id="union_id" name="union_id" @disabled(! $isSuperAdmin)>
                <option value="">انتخاب اتحادیه</option>
                @foreach ($unions as $union)
                    <option value="{{ $union->id }}" @selected((string) old('union_id', $isSuperAdmin ? null : $userUnionId) === (string) $union->id)>{{ $union->display_title }}</option>
                @endforeach
            </select>
            @unless ($isSuperAdmin)
                <input type="hidden" name="union_id" value="{{ $userUnionId }}">
                <small class="text-muted">کارشناس اتحادیه فقط به اعضای اتحادیه خودش پیامک می‌فرستد.</small>
            @endunless
        </div>
        <div class="col-md-4" id="singleMemberWrapper">
            <label class="form-label" for="single_member_id">عضو برای ارسال تکی</label>
            <select class="form-control" id="single_member_id" name="single_member_id">
                <option value="">انتخاب عضو</option>
                @foreach ($members as $member)
                    <option value="{{ $member->id }}" data-union="{{ $member->union_id }}" @selected((string) old('single_member_id') === (string) $member->id)>{{ $member->full_name }} - {{ $member->mobile }} @if($isSuperAdmin)({{ $member->union?->display_title }})@endif</option>
                @endforeach
            </select>
        </div>

        <div class="col-12" id="membersWrapper">
            <label class="form-label">انتخاب اعضا</label>
            <div class="border rounded p-3" style="max-height:320px;overflow:auto">
                <div class="row g-2">
                    @forelse ($members as $member)
                        <div class="col-md-4 sms-member-item" data-union="{{ $member->union_id }}">
                            <label class="border rounded p-2 d-block h-100">
                                <input type="checkbox" name="member_ids[]" value="{{ $member->id }}" data-union="{{ $member->union_id }}" @checked(in_array($member->id, old('member_ids', [])))>
                                <strong>{{ $member->full_name }}</strong>
                                <small class="d-block text-muted">{{ $member->mobile }} @if($isSuperAdmin)| {{ $member->union?->display_title }}@endif</small>
                            </label>
                        </div>
                    @empty
                        <div class="col-12 text-muted">عضوی با شماره موبایل قابل ارسال پیدا نشد.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-12">
            <label class="form-label" for="message">متن پیامک</label>
            <textarea class="form-control" id="message" name="message" rows="6" maxlength="1000" required>{{ old('message') }}</textarea>
            <div class="d-flex gap-3 flex-wrap mt-2 text-muted">
                <span>تعداد کاراکتر: <strong id="characterCount">0</strong></span>
                <span>تعداد تقریبی پیامک: <strong id="segmentCount">1</strong></span>
                <span>تعداد گیرندگان: <strong id="recipientCount">0</strong></span>
            </div>
        </div>
    </div>

    <div class="mt-3 d-flex gap-2 flex-wrap">
        <button class="admin-primary-btn" type="submit">ثبت ارسال پیامک</button>
        <a class="admin-secondary-btn" href="{{ route('admin.sms.logs') }}">تاریخچه پیامک‌ها</a>
    </div>
</form>
@endsection

@push('scripts')
<script>
(() => {
    const sendType = document.getElementById('send_type');
    const union = document.getElementById('union_id');
    const singleMember = document.getElementById('single_member_id');
    const membersWrapper = document.getElementById('membersWrapper');
    const singleMemberWrapper = document.getElementById('singleMemberWrapper');
    const unionWrapper = document.getElementById('unionWrapper');
    const message = document.getElementById('message');
    const characterCount = document.getElementById('characterCount');
    const segmentCount = document.getElementById('segmentCount');
    const recipientCount = document.getElementById('recipientCount');
    const memberItems = Array.from(document.querySelectorAll('.sms-member-item'));
    const memberChecks = Array.from(document.querySelectorAll('input[name="member_ids[]"]'));
    const memberOptions = Array.from(singleMember.querySelectorAll('option[data-union]'));
    const totalMembers = {{ $members->count() }};

    function selectedUnion() {
        return union ? union.value : '';
    }

    function updateVisibility() {
        const type = sendType.value;
        const needsUnion = type === 'union_all';
        const needsSelected = type === 'selected';
        const needsSingle = type === 'single';

        unionWrapper.style.display = (needsUnion || needsSelected || needsSingle) ? '' : 'none';
        membersWrapper.style.display = needsSelected ? '' : 'none';
        singleMemberWrapper.style.display = needsSingle ? '' : 'none';

        const unionValue = selectedUnion();
        memberItems.forEach(item => {
            item.style.display = (!unionValue || item.dataset.union === unionValue) ? '' : 'none';
        });
        memberOptions.forEach(option => {
            option.hidden = !!unionValue && option.dataset.union !== unionValue;
        });

        updateRecipientCount();
    }

    function updateRecipientCount() {
        const type = sendType.value;
        const unionValue = selectedUnion();
        let count = 0;

        if (type === 'all') {
            count = totalMembers;
        } else if (type === 'union_all') {
            count = memberItems.filter(item => !unionValue || item.dataset.union === unionValue).length;
        } else if (type === 'selected') {
            count = memberChecks.filter(input => input.checked && (!unionValue || input.dataset.union === unionValue)).length;
        } else if (type === 'single') {
            count = singleMember.value ? 1 : 0;
        }

        recipientCount.textContent = count;
    }

    function updateMessageCount() {
        const length = message.value.length;
        characterCount.textContent = length;
        segmentCount.textContent = Math.max(1, Math.ceil(length / 70));
    }

    sendType.addEventListener('change', updateVisibility);
    if (union) union.addEventListener('change', updateVisibility);
    singleMember.addEventListener('change', updateRecipientCount);
    memberChecks.forEach(input => input.addEventListener('change', updateRecipientCount));
    message.addEventListener('input', updateMessageCount);

    updateVisibility();
    updateMessageCount();
})();
</script>
@endpush

<section id="chamber-board" class="admin-panel-card mt-4" style="scroll-margin-top:110px">
    <div class="admin-page-toolbar">
        <div><p class="admin-eyebrow">صفحهٔ درباره اتاق</p><h2>هیئت‌رئیسهٔ اتاق اصناف</h2><p class="text-muted">رئیس در بالای صفحه نمایش داده می‌شود. اعضای فعال بر اساس ترتیب نمایش مرتب می‌شوند.</p></div>
        @if(request()->user()->hasPermission('chamber_members.create'))<a class="admin-primary-btn" href="{{ route('admin.chamber_members.create') }}">افزودن عضو</a>@endif
    </div>
    <div class="table-responsive"><table class="table admin-table align-middle">
        <thead><tr><th>عکس</th><th>نام و سمت</th><th>رئیس اتاق</th><th>ترتیب</th><th>وضعیت</th><th>عملیات</th></tr></thead>
        <tbody>@forelse($members as $member)<tr>
            <td>@if($member->photo)<img src="{{ $member->photo_url }}" alt="{{ $member->full_name }}" width="64" height="64" style="object-fit:cover;border-radius:12px">@else<span class="text-muted">بدون عکس</span>@endif</td>
            <td><strong>{{ $member->full_name }}</strong><div class="text-muted">{{ $member->position }}</div></td>
            <td>{{ $member->is_president ? 'بله' : '—' }}</td><td>{{ $member->sort_order }}</td><td>{{ $member->is_active ? 'فعال' : 'غیرفعال' }}</td>
            <td class="admin-actions">
                @if(request()->user()->hasPermission('chamber_members.edit'))<a href="{{ route('admin.chamber_members.edit', $member) }}">ویرایش</a>@endif
                @if(request()->user()->hasPermission('chamber_members.delete'))<form action="{{ route('admin.chamber_members.destroy', $member) }}" method="POST">@csrf @method('DELETE')<button type="submit">حذف</button></form>@endif
            </td>
        </tr>@empty<tr><td colspan="6" class="text-center text-muted">عضو جدید اضافه کنید و گزینهٔ رئیس اتاق را برای رئیس انتخاب کنید.</td></tr>@endforelse</tbody>
    </table></div>
</section>

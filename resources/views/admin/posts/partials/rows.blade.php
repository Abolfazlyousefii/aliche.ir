@forelse ($posts as $post)
    <tr>
        <td>
            <strong>{{ $post->title }}</strong><br>
            <code>{{ $post->slug }}</code>
        </td>
        <td>{{ $post->category?->title ?: '—' }}</td>
        <td>{{ $post->union?->display_title ?: 'عمومی' }}</td>
        <td>{{ $post->type_label }}</td>
        <td>{{ $post->homepage_position_label }}</td>
        <td>
            <span class="admin-status-badge status-{{ $post->status }}">
                {{ $post->status_label }}
            </span>
        </td>
        <td>{{ fa_number(number_format($post->views_count)) }}</td>
        <td>{{ jalali_datetime($post->published_at) ?: '—' }}</td>
        <td>
            <div class="admin-actions">
                <a href="{{ route('admin.posts.show', $post) }}">مشاهده</a>
                <a href="{{ route('admin.posts.edit', $post) }}">ویرایش</a>
                <form action="{{ route('admin.posts.destroy', $post) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit">حذف</button>
                </form>
            </div>
        </td>
    </tr>
@empty
    @if($posts->currentPage() === 1)
        <tr data-post-empty-row>
            <td colspan="9">
                <div class="admin-post-empty-filter">
                    <strong>خبری پیدا نشد</strong>
                    <span>عبارت جستجو یا فیلترهای انتخاب‌شده را تغییر دهید.</span>
                </div>
            </td>
        </tr>
    @endif
@endforelse

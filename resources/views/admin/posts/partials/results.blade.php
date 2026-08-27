@if($filterError)
    <div class="alert alert-warning admin-post-results-error" role="alert">
        {{ $filterError }}
    </div>
@endif

<div class="admin-panel-card">
    <div class="admin-post-results-toolbar">
        <div class="admin-post-results-count">
            @if($posts->total() > 0)
                <strong>{{ fa_number($posts->total()) }}</strong> خبر مطابق فیلترهای فعلی پیدا شد.
            @else
                نتیجه‌ای مطابق فیلترهای فعلی پیدا نشد.
            @endif
        </div>
    </div>

    <div class="table-responsive">
        <table class="table admin-table align-middle">
            <thead>
                <tr>
                    <th>عنوان</th>
                    <th>دسته‌بندی</th>
                    <th>اتحادیه</th>
                    <th>نوع</th>
                    <th>جایگاه صفحه اصلی</th>
                    <th>وضعیت</th>
                    <th>بازدید</th>
                    <th>انتشار</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
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
                    <tr>
                        <td colspan="9">
                            <div class="admin-post-empty-filter">
                                <strong>خبری پیدا نشد</strong>
                                <span>فیلترها را تغییر دهید یا همه فیلترها را پاک کنید.</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @include('admin.partials.pagination', ['paginator' => $posts])
</div>

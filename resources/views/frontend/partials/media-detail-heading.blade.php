<section class="media-detail-heading site-container" aria-labelledby="media-detail-heading">
    <nav class="media-detail-heading__breadcrumb" aria-label="مسیر راهنما">
        <a href="{{ route('home') }}">خانه</a>
        <span aria-hidden="true">/</span>
        <a href="{{ $indexUrl }}">{{ $indexLabel }}</a>
    </nav>
    <h1 id="media-detail-heading">{{ $title }}</h1>
</section>

@push('styles')
<style>
/* Only gallery/video detail headings, not the shared site header. */
.media-detail-heading {
    padding-top: clamp(22px, 3vw, 38px);
    padding-bottom: clamp(16px, 2vw, 25px);
    background: #fff;
    color: #143a50;
}
.media-detail-heading__breadcrumb {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 9px;
    margin-bottom: 11px;
    color: #6b8291;
    font-size: 12px;
    line-height: 1.7;
}
.media-detail-heading__breadcrumb a {
    color: #4d7792;
    text-decoration: none;
}
.media-detail-heading__breadcrumb a:hover,
.media-detail-heading__breadcrumb a:focus-visible {
    color: #08659a;
    text-decoration: underline;
}
.media-detail-heading h1 {
    margin: 0;
    font-size: clamp(21px, 2.2vw, 30px);
    font-weight: 750;
    line-height: 1.65;
    overflow-wrap: anywhere;
    color: #143a50;
}
.media-detail-tags {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 22px;
    padding-top: 16px;
    border-top: 1px solid #e6edf1;
}
.media-detail-tags__label {
    margin-inline-end: 4px;
    font-size: 12px;
    font-weight: 600;
    color: #42677f;
}
.media-detail-tags .post-tag {
    display: inline-block;
    padding: 5px 11px;
    border: 1px solid #dae8f0;
    border-radius: 6px;
    background: #f4f9fc;
    color: #245875;
    font-size: 12px;
    line-height: 1.7;
}
@media (max-width: 575.98px) {
    .media-detail-heading { padding-top: 18px; }
    .media-detail-heading h1 { font-size: 21px; }
}
</style>
@endpush

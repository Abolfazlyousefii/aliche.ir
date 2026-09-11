@php
    $documentRoot = rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''), DIRECTORY_SEPARATOR);
    $servedAssetPath = static function (string $relativePath) use ($documentRoot): string {
        $documentRootPath = $documentRoot !== ''
            ? $documentRoot.DIRECTORY_SEPARATOR.ltrim($relativePath, DIRECTORY_SEPARATOR)
            : '';

        return $documentRootPath !== '' && is_file($documentRootPath)
            ? $documentRootPath
            : public_path($relativePath);
    };

    $mainStylesPath = $servedAssetPath('assets/css/styles.css');
    $layoutLockPath = $servedAssetPath('assets/css/home-layout-lock.css');
    $mainStylesVersion = is_file($mainStylesPath) ? filemtime($mainStylesPath) : '1';
    $layoutLockVersion = is_file($layoutLockPath) ? filemtime($layoutLockPath) : '1';
@endphp
<link href="https://cdn.jsdelivr.net" rel="preconnect"/>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet"/>
<link href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" rel="stylesheet"/>
<link href="{{ asset('assets/css/styles.css') }}?v={{ $mainStylesVersion }}" rel="stylesheet"/>
<link href="{{ asset('assets/css/home-layout-lock.css') }}?v={{ $layoutLockVersion }}" rel="stylesheet"/>

@if(request()->routeIs('guilds.show'))
    @php
        $guildProfileStylesPath = $servedAssetPath('assets/css/guild-profile.css');
        $guildProfileStylesVersion = is_file($guildProfileStylesPath) ? filemtime($guildProfileStylesPath) : '1';
        $guildMemberImagesPath = $servedAssetPath('assets/css/guild-member-images.css');
        $guildMemberImagesVersion = is_file($guildMemberImagesPath) ? filemtime($guildMemberImagesPath) : '1';
    @endphp
    <link href="{{ asset('assets/css/guild-profile.css') }}?v={{ $guildProfileStylesVersion }}" rel="stylesheet"/>
    <link href="{{ asset('assets/css/guild-member-images.css') }}?v={{ $guildMemberImagesVersion }}" rel="stylesheet"/>
@endif

@if(request()->routeIs('posts.index'))
    @php
        $newsArchivePaginationStylesPath = $servedAssetPath('assets/css/news-archive-pagination.css');
        $newsArchivePaginationStylesVersion = is_file($newsArchivePaginationStylesPath)
            ? filemtime($newsArchivePaginationStylesPath)
            : '1';
    @endphp
    <link href="/assets/css/news-archive-pagination.css?v={{ $newsArchivePaginationStylesVersion }}" rel="stylesheet"/>
@endif

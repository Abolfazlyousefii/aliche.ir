@extends('admin.layouts.app')

@section('title', 'ویرایش خبر')

@section('content')
<div class="admin-page-toolbar">
    <div><p class="admin-eyebrow">مدیریت اخبار</p><h2>ویرایش خبر: {{ $post->title }}</h2></div>
    <a class="admin-secondary-btn" href="{{ route('admin.posts.show', $post) }}">مشاهده خبر</a>
</div>

<form action="{{ route('admin.posts.update', $post) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    @include('admin.posts._form')
</form>
@endsection

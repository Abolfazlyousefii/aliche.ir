<label class="form-label" for="union_id">اتحادیه</label>
<div class="video-union-picker" data-video-union-picker>
    {{-- The real select stays authoritative for form submission and Laravel validation.
         Without JavaScript it remains a normal accessible native select. --}}
    <select class="form-control" id="union_id" name="union_id" data-video-union-native>
        <option value="">ویدیوی عمومی (بدون اتحادیه)</option>
        @foreach($unions as $union)
            <option value="{{ $union->id }}" @selected((string) old('union_id', $video?->union_id) === (string) $union->id)>{{ $union->display_title }}</option>
        @endforeach
    </select>
</div>
<small class="text-muted d-block mt-1">برای یافتن اتحادیه، بخشی از نام آن را بنویسید.</small>

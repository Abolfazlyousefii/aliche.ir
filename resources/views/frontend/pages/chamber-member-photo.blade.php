<span class="chamber-member-photo">
@if($member->photo)<img src="{{ $member->photo_url }}" alt="{{ $member->full_name }}" loading="lazy" decoding="async" width="360" height="360">
@else<span class="chamber-photo-initial" aria-hidden="true">{{ mb_substr($member->first_name, 0, 1) }}</span>@endif
</span>

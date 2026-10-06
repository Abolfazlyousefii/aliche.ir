<li class="admin-menu-tree-item" data-menu-item-id="{{ $item->id }}" @if($canEditMenu ?? false) draggable="true" @endif>
    <div class="admin-menu-tree-row">
        @if($canEditMenu ?? false)<span class="admin-drag-handle" title="جابجایی">↕</span>@endif
        <div class="admin-menu-item-info">
            <strong>{{ $item->icon }} {{ $item->title }}</strong>
            <small><code>{{ $item->type }}</code> — {{ $item->resolved_url }}</small>
        </div>
        <span class="admin-status-badge {{ $item->is_active ? 'is-active' : 'is-inactive' }}">{{ $item->is_active ? 'فعال' : 'غیرفعال' }}</span>
        <div class="admin-actions">
            @if($canEditMenu ?? false)<a href="{{ route('admin.menus.items.edit', [$menu, $item]) }}">ویرایش</a>@endif
            @if($canEditMenu ?? false)
            <form action="{{ route('admin.menus.items.toggle', [$menu, $item]) }}" method="POST">
                @csrf
                @method('PATCH')
                <button type="submit">{{ $item->is_active ? 'غیرفعال' : 'فعال' }}</button>
            </form>
            @endif
            @if(request()->user()->hasPermission('menus.delete'))
            <form action="{{ route('admin.menus.items.destroy', [$menu, $item]) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit">حذف</button>
            </form>
            @endif
        </div>
    </div>

    <ol class="admin-menu-tree" data-menu-list>
        @foreach ($item->adminChildren as $child)
            @include('admin.menus.items._tree-item', ['item' => $child, 'menu' => $menu])
        @endforeach
    </ol>
</li>

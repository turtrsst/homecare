{{-- Daftar menu admin. Item dengan properti `can` hanya tampil bila pengguna memiliki permission-nya. --}}
<nav class="flex-1 space-y-1 overflow-y-auto p-4" aria-label="Menu operasional">
    @foreach ($sections as $item)
        @if (empty($item['can']) || $user->can($item['can']))
            @php $active = request()->routeIs($item['match']); @endphp
            <a href="{{ route($item['route']) }}" @if($active) aria-current="page" @endif
               class="flex items-center gap-3 rounded-2xl px-3.5 py-3 text-sm font-semibold transition {{ $active ? 'bg-gradient-to-r from-soeradji-600 to-soeradji-500 text-white shadow-lg shadow-soeradji-600/25' : 'text-clinic-600 hover:bg-clinic-100 hover:text-clinic-900' }}">
                <x-icon :name="$item['icon']" class="h-5 w-5 shrink-0" />
                {{ $item['label'] }}
            </a>
        @endif
    @endforeach
</nav>

<div class="border-t border-clinic-100 p-4">
    <div class="mb-3 flex items-center gap-3 px-2">
        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-soeradji-50 font-extrabold text-soeradji-700">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
        <div class="min-w-0">
            <p class="truncate text-sm font-bold text-clinic-900">{{ $user->name }}</p>
            <p class="truncate text-xs text-clinic-500">{{ $user->role->label() }}</p>
        </div>
    </div>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="flex w-full items-center gap-3 rounded-2xl px-3.5 py-2.5 text-sm font-semibold text-rose-600 hover:bg-rose-50"><x-icon name="logout" class="h-4 w-4" /> Keluar</button>
    </form>
</div>

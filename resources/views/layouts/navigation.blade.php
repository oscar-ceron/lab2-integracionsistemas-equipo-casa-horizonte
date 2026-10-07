@php $admin = Auth::user()->is_admin; $home = $admin ? route('admin') : route('dashboard'); @endphp
<nav x-data="{ open: false }" class="sticky top-0 z-40 border-b border-gray-200 bg-gray-100">
    <div class="mx-auto flex h-16 max-w-6xl items-stretch justify-between px-5 sm:px-8">
        <div class="flex items-stretch gap-10">
            <a href="{{ $home }}" class="flex items-center" aria-label="Casa Horizonte">
                <x-application-logo class="h-11 w-auto" />
            </a>
            <div class="hidden items-stretch gap-7 sm:flex">
                <x-nav-link :href="$home" :active="request()->routeIs($admin ? 'admin' : 'dashboard')">{{ $admin ? 'Administración' : 'Panel' }}</x-nav-link>
                <x-nav-link :href="route('rooms.index')" :active="request()->routeIs('rooms.*')">Habitaciones</x-nav-link>
            </div>
        </div>

        <div class="hidden items-center gap-3 sm:flex">
            @unless ($admin)<a href="{{ route('dashboard', ['tab' => 'reservar']) }}" class="btn btn-primary">Reservar</a>@endunless
            <x-dropdown align="right" width="48">
                <x-slot name="trigger">
                    <button class="btn btn-quiet gap-2.5 !px-2 !py-1.5" aria-label="Menú de usuario">
                        <span class="flex size-8 items-center justify-center rounded-full bg-indigo-800 text-xs font-semibold text-gray-50">{{ mb_strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}</span>
                        <span class="max-w-32 truncate">{{ Auth::user()->name }}</span>
                    </button>
                </x-slot>
                <x-slot name="content">
                    <x-dropdown-link :href="route('profile.edit')">Mi perfil</x-dropdown-link>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">Cerrar sesión</x-dropdown-link>
                    </form>
                </x-slot>
            </x-dropdown>
        </div>

        <div class="flex items-center sm:hidden">
            <button @click="open = ! open" class="btn btn-quiet !p-2.5" aria-label="Abrir menú"><x-icon name="menu" class="size-5" /></button>
        </div>
    </div>

    <div x-show="open" x-cloak
        x-transition:enter="transition duration-200 ease-snap" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
        class="space-y-1 border-t border-gray-200 px-5 py-4 sm:hidden">
        <x-responsive-nav-link :href="$home" :active="request()->routeIs($admin ? 'admin' : 'dashboard')">{{ $admin ? 'Administración' : 'Panel' }}</x-responsive-nav-link>
        <x-responsive-nav-link :href="route('rooms.index')" :active="request()->routeIs('rooms.*')">Habitaciones</x-responsive-nav-link>
        @unless ($admin)<x-responsive-nav-link :href="route('dashboard', ['tab' => 'reservar'])">Reservar</x-responsive-nav-link>@endunless
        <x-responsive-nav-link :href="route('profile.edit')">Mi perfil</x-responsive-nav-link>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">Cerrar sesión ({{ Auth::user()->name }})</x-responsive-nav-link>
        </form>
    </div>
</nav>

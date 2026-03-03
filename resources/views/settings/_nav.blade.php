<aside class="w-56 shrink-0">
    <nav class="bg-white rounded-xl border border-gray-200 p-2 space-y-1 sticky top-4">
        <a href="{{ route('settings.index') }}" class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg {{ request()->routeIs('settings.index') ? 'bg-primary-50 text-primary-700 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            Empresa
        </a>
        <a href="{{ route('settings.users') }}" class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg {{ request()->routeIs('settings.users*') ? 'bg-primary-50 text-primary-700 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            Usuários
        </a>
        <a href="{{ route('settings.channels') }}" class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg {{ request()->routeIs('settings.channels*') ? 'bg-primary-50 text-primary-700 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            Canais
        </a>
        <a href="{{ route('settings.sla') }}" class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg {{ request()->routeIs('settings.sla*') ? 'bg-primary-50 text-primary-700 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Políticas SLA
        </a>
        <a href="{{ route('settings.quick-replies') }}" class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg {{ request()->routeIs('settings.quick-replies*') ? 'bg-primary-50 text-primary-700 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            Respostas Rápidas
        </a>
        <a href="{{ route('settings.automations') }}" class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg {{ request()->routeIs('settings.automations*') ? 'bg-primary-50 text-primary-700 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            Automações
        </a>
    </nav>
</aside>
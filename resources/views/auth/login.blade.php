<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Elite Atendimento</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-gray-50 font-sans antialiased">
    <div class="min-h-full flex">
        {{-- Left side - branding --}}
        <div class="hidden lg:flex lg:w-1/2 bg-primary-800 relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-primary-700 to-primary-900"></div>
            <div class="relative z-10 flex flex-col justify-center px-16">
                <div class="flex items-center gap-3 mb-8">
                    <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </div>
                    <span class="text-2xl font-bold text-white">EliteChat</span>
                </div>
                <h2 class="text-4xl font-bold text-white mb-4">
                    Plataforma Omnichannel<br>para Concessionárias
                </h2>
                <p class="text-primary-200 text-lg leading-relaxed">
                    Gerencie todos os seus canais de atendimento em um só lugar. 
                    WhatsApp, Facebook, Instagram, E-mail e muito mais.
                </p>
                <div class="mt-12 grid grid-cols-2 gap-4">
                    <div class="bg-white/10 rounded-lg p-4">
                        <div class="text-2xl font-bold text-white">5+</div>
                        <div class="text-primary-200 text-sm">Canais integrados</div>
                    </div>
                    <div class="bg-white/10 rounded-lg p-4">
                        <div class="text-2xl font-bold text-white">CRM</div>
                        <div class="text-primary-200 text-sm">Pipeline de vendas</div>
                    </div>
                    <div class="bg-white/10 rounded-lg p-4">
                        <div class="text-2xl font-bold text-white">SLA</div>
                        <div class="text-primary-200 text-sm">Controle de qualidade</div>
                    </div>
                    <div class="bg-white/10 rounded-lg p-4">
                        <div class="text-2xl font-bold text-white">AI</div>
                        <div class="text-primary-200 text-sm">Automações inteligentes</div>
                    </div>
                </div>
            </div>
            {{-- Decorative circles --}}
            <div class="absolute -bottom-32 -right-32 w-72 h-72 bg-white/5 rounded-full"></div>
            <div class="absolute -top-16 -right-16 w-48 h-48 bg-white/5 rounded-full"></div>
        </div>

        {{-- Right side - login form --}}
        <div class="flex-1 flex items-center justify-center px-6 py-12">
            <div class="w-full max-w-md">
                <div class="lg:hidden flex items-center gap-2 mb-8 justify-center">
                    <div class="w-10 h-10 bg-primary-600 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-gray-900">EliteChat</span>
                </div>

                <h2 class="text-2xl font-bold text-gray-900 mb-1">Bem-vindo de volta</h2>
                <p class="text-gray-500 mb-8">Faça login para acessar sua conta</p>

                @if($errors->any())
                <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="text-sm text-red-800">{{ $errors->first() }}</p>
                    </div>
                </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">E-mail</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                               placeholder="seu@email.com">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">Senha</label>
                        <div x-data="{ show: false }" class="relative">
                            <input :type="show ? 'text' : 'password'" name="password" id="password" required
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm pr-10"
                                   placeholder="••••••••">
                            <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="remember" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                            <span class="text-sm text-gray-600">Lembrar-me</span>
                        </label>
                    </div>

                    <button type="submit" class="w-full bg-primary-600 text-white py-2.5 px-4 rounded-lg font-medium hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors">
                        Entrar
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
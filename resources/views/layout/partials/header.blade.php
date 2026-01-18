<nav
    x-data="{ open: false, modulesOpen: false }"
    class="fixed w-full z-50 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 shadow-sm">

    <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-between items-center h-16">

            <!-- Logo -->
            <a href="{{ route('admin.home') }}" class="flex items-center gap-2 group">
                <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-purple-600 rounded-lg flex items-center justify-center transform group-hover:scale-110 transition">
                    <span class="text-white font-bold text-xl">I</span>
                </div>
                <span class="text-xl font-bold text-gray-900 dark:text-white">IruyCode</span>
            </a>

            <!-- Desktop Navigation -->
            <div class="hidden md:flex items-center space-x-1">
                <a href="{{ route('admin.home') }}" 
                   class="px-4 py-2 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition font-medium">
                    🏠 Home
                </a>

                <!-- Notifications Bell -->
                <a href="{{ route('admin.home') }}#notifications" 
                   class="relative px-4 py-2 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition font-medium">
                    🔔 Notificações
                    @if(isset($unreadNotificationsCount) && $unreadNotificationsCount > 0)
                        <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs font-bold rounded-full flex items-center justify-center animate-pulse">
                            {{ $unreadNotificationsCount }}
                        </span>
                    @endif
                </a>

                <!-- Enable Push Notifications Button -->
                <button onclick="window.requestBankManagerNotifications()" 
                        class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium transition flex items-center gap-2">
                    📱 Ativar Push
                </button>
                
                <!-- Modules Dropdown -->
                <div class="relative" @click.outside="modulesOpen = false">
                    <button @click="modulesOpen = !modulesOpen"
                            class="px-4 py-2 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition font-medium flex items-center gap-2">
                        📦 Módulos
                        <svg class="w-4 h-4 transition-transform" :class="modulesOpen ? 'rotate-180' : ''"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    
                    <!-- Dropdown Menu -->
                    <div x-show="modulesOpen" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-64 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 py-2"
                         x-cloak>
                        
                        <a href="{{ route('bank-manager.index') }}" 
                           class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            <span class="text-2xl">💰</span>
                            <div>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">Bank Manager</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Finanças pessoais</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Mobile Burger -->
            <button
                @click="open = !open"
                class="md:hidden p-2 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div
        x-show="open"
        x-transition
        @click.outside="open = false"
        class="md:hidden bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800">
        
        <div class="px-4 py-3 space-y-2">
            <a href="{{ route('admin.home') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                <span>🏠</span>
                <span class="text-gray-900 dark:text-white font-medium">Home</span>
            </a>

            <!-- Enable Push Notifications Button (Mobile) -->
            <button onclick="window.requestBankManagerNotifications()" 
                    class="w-full flex items-center gap-3 px-4 py-3 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium transition">
                <span>📱</span>
                <span>Ativar Notificações Push</span>
            </button>
            
            <div class="border-t border-gray-200 dark:border-gray-700 my-2"></div>
            <p class="px-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Módulos</p>
            
            <a href="{{ route('bank-manager.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                <span>💰</span>
                <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">Bank Manager</p>
                </div>
            </a>
        </div>
    </div>
</nav>

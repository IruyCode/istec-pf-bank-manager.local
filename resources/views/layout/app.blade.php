<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'IruyCode')</title>
    <link rel="icon" type="image/png" href="{{ asset('iruycode_img/favicon.png') }}">

    {{-- Preloader: esconde instantaneamente se já foi mostrado nesta sessão --}}
    <script>
        if (sessionStorage.getItem('preloaderShown')) {
            document.documentElement.classList.add('skip-preloader');
        }
    </script>
    <style>
        .skip-preloader #site-preloader { display: none !important; }
    </style>

    <!-- Tema Bootstrap 5 escuro -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/datatables.bootstrap5.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>


    <!-- DataTables Core CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    {{-- Vite assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Alpine.js + plugins --}}
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" onload="
                        document.addEventListener('alpine:init', () => {
                            // Store global para controlar modais
                            Alpine.store('modal', {
                                current: null,
                                open(name) { this.current = name },
                                close() { this.current = null },
                                is(name) { return this.current === name }
                            });
                        });
                    "></script>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 transition-colors duration-300">

    {{-- ─── Preloader (once per session) ─────────────────────────────────── --}}
    <div id="site-preloader"
         style="position:fixed;inset:0;z-index:99999;background:#000;display:flex;align-items:center;justify-content:center;transition:opacity .5s ease">
        <video
            id="preloader-video"
            src="{{ asset('iruycode_img/' . rawurlencode('Logotipo empresa de programação sistemas e tecnologia.mp4')) }}"
            autoplay muted playsinline
            style="max-width:100%;max-height:100%;object-fit:contain">
        </video>
    </div>
    <script>
        (function () {
            if (!sessionStorage.getItem('preloaderShown')) {
                sessionStorage.setItem('preloaderShown', '1');
                setTimeout(function () {
                    var el = document.getElementById('site-preloader');
                    el.style.opacity = '0';
                    setTimeout(function () { el.style.display = 'none'; }, 500);
                }, 4000);
            }
        })();
    </script>
    {{-- ─────────────────────────────────────────────────────────────────── --}}

    <!-- Header global -->
    @include('layout.partials.header')

    <!-- Conteúdo principal -->
    <main class="pt-20 min-h-screen bg-gray-100 dark:bg-gray-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Mensagens globais (sucesso, erro etc.) --}}
            @include('layout.partials.alerts')

            {{-- Conteúdo dinâmico --}}
            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    @include('layout.partials.footer')

    {{-- Scripts adicionais empilhados por módulos --}}
    @stack('scripts')

    <!-- Firebase Push Notifications -->
    <script type="module">
        // Import the functions you need from the SDKs you need
        import {
            initializeApp
        } from "https://www.gstatic.com/firebasejs/12.7.0/firebase-app.js";
        import {
            getMessaging,
            getToken,
            onMessage
        } from "https://www.gstatic.com/firebasejs/12.7.0/firebase-messaging.js";

        // Your web app's Firebase configuration
        const firebaseConfig = {
            apiKey: "AIzaSyC0JxElG0utTkRbnfSO9DporVpPjvIbeXc",
            authDomain: "iruycode-final.firebaseapp.com",
            projectId: "iruycode-final",
            storageBucket: "iruycode-final.firebasestorage.app",
            messagingSenderId: "188640663792",
            appId: "1:188640663792:web:3e30555d305d035bde8a35",
            measurementId: "G-XVMK8VNQMR"
        };

        // Initialize Firebase
        const app = initializeApp(firebaseConfig);
        const messaging = getMessaging(app);

        // Request notification permission
        async function requestNotificationPermission() {
            console.log('🔔 Solicitando permissão para notificações...');
            
            // Show loading
            const button = event?.target;
            const originalText = button?.innerHTML;
            if (button) {
                button.disabled = true;
                button.innerHTML = '⏳ Carregando...';
            }

            try {
                // Check if browser supports notifications
                if (!('Notification' in window)) {
                    alert('❌ Seu navegador não suporta notificações!');
                    return;
                }

                // Check if service workers are supported
                if (!('serviceWorker' in navigator)) {
                    alert('❌ Seu navegador não suporta Service Workers!');
                    return;
                }

                // Unregister old service workers first
                const registrations = await navigator.serviceWorker.getRegistrations();
                for (let registration of registrations) {
                    await registration.unregister();
                    console.log('🗑️ Service Worker antigo removido:', registration.scope);
                }

                // Register service worker first and wait for it
                console.log('📝 Registrando Service Worker...');
                const registration = await navigator.serviceWorker.register('/firebase-messaging-sw.js');
                console.log('✅ Service Worker registrado:', registration);

                // Wait for service worker to be active
                await navigator.serviceWorker.ready;
                console.log('✅ Service Worker ativo!');

                // Request permission
                const permission = await Notification.requestPermission();

                if (permission === 'granted') {
                    console.log('✅ Permissão concedida!');
                    console.log('🔄 Obtendo token FCM...');

                    // Get FCM token with service worker registration
                    const currentToken = await getToken(messaging, {
                        vapidKey: 'BIkKt7obfRXyFlmfGrBJcZSC5CjgCV-5xlY7lWSbID0145bMZWbkWLy-Wgd1YU54TIUEYEu4tYaOdJNvbO9Wp88',
                        serviceWorkerRegistration: registration
                    });

                    console.log('🔍 Resultado getToken:', currentToken);

                    if (currentToken) {
                        console.log('🔑 Token FCM:', currentToken);
                        console.log('📤 Enviando token para servidor...');

                        // Send token to Laravel backend
                        const response = await fetch('{{ route('bank-manager.notifications.register-token') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                token: currentToken,
                                device_name: navigator.userAgent
                            })
                        });

                        const data = await response.json();
                        console.log('✅ Token registrado no servidor:', data);
                        
                        // Success feedback
                        alert('✅ Notificações ativadas com sucesso!\n\nVocê receberá notificações sobre:\n• Despesas recentes\n• Contas a vencer\n• Investimentos\n• E muito mais!');
                        
                        if (button) {
                            button.innerHTML = '✅ Ativado!';
                            button.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                            button.classList.add('bg-green-600');
                        }
                    } else {
                        console.log('⚠️ Nenhum token disponível.');
                        alert('⚠️ Não foi possível obter o token de notificação.\nTente novamente.');
                        if (button) button.innerHTML = originalText;
                    }
                } else if (permission === 'denied') {
                    alert('❌ Permissão negada!\n\nPara ativar notificações:\n1. Clique no ícone de cadeado na barra de endereço\n2. Permita notificações\n3. Recarregue a página e tente novamente');
                    if (button) button.innerHTML = originalText;
                } else {
                    alert('⚠️ Você precisa permitir notificações para continuar.');
                    if (button) button.innerHTML = originalText;
                }
            } catch (error) {
                console.error('❌ Erro:', error);
                alert('❌ Erro ao ativar notificações:\n' + error.message + '\n\nVerifique o console para mais detalhes.');
                if (button) button.innerHTML = originalText;
            } finally {
                if (button) button.disabled = false;
            }
        }

        // Handle foreground messages
        onMessage(messaging, (payload) => {
            console.log('📩 Mensagem recebida:', payload);

            const notificationTitle = payload.notification.title;
            const notificationOptions = {
                body: payload.notification.body,
                icon: payload.notification.icon || '/icon.png',
                badge: '/badge.png',
                tag: 'bank-manager-notification'
            };

            if (Notification.permission === 'granted') {
                new Notification(notificationTitle, notificationOptions);
            }
        });

        // Export to global scope
        window.requestBankManagerNotifications = requestNotificationPermission;
    </script>
</body>

</html>

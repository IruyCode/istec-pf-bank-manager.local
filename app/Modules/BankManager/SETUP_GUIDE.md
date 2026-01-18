# 🚀 GUIA DE INSTALAÇÃO - Sistema de Notificações Bank Manager

## 📋 Pré-requisitos
- Laravel 11+
- PHP 8.2+
- Conta Firebase (para Push Notifications)

---

## 🔧 Instalação

### 1️⃣ Executar Migrations
```bash
php artisan migrate
```

Isso criará as tabelas:
- `bank_manager_notifications`
- `fcm_tokens`
- Adicionará campos em `app_bank_manager_transactions` e `app_bank_manager_financial_goals`

---

### 2️⃣ Configurar Firebase Cloud Messaging (FCM)

#### A) Obter credenciais do Firebase

**1. Service Account JSON (Backend)**
1. Acesse [Firebase Console](https://console.firebase.google.com/)
2. Selecione seu projeto **iruycode-final**
3. Vá em **⚙️ Configurações do Projeto** → **Contas de serviço**
4. Clique em **Gerar nova chave privada**
5. Salve o JSON baixado como `storage/app/firebase-credentials.json`

**2. VAPID Key (Frontend)**
1. No Firebase Console, vá em **⚙️ Configurações do Projeto** → **Cloud Messaging**
2. Na seção **Web Push certificates**, copie a **Key pair (VAPID)**

#### B) Adicionar no `.env`
```env
FIREBASE_CREDENTIALS_PATH=storage/app/firebase-credentials.json
```

#### C) Substituir VAPID Key no código
No arquivo do layout (passo 1 acima), substitua:
```javascript
vapidKey: 'SUBSTITUA_PELA_SUA_VAPID_KEY'
```
Pela sua VAPID Key obtida no Firebase Console.

---

### 3️⃣ Configurar Laravel Scheduler

#### A) Verificar configuração
Arquivo já está configurado em `routes/console.php`:
```php
Schedule::command('bankmanager:check-expenses')
    ->dailyAt('09:00')
    ->timezone('Europe/Lisbon')
    ->withoutOverlapping();
```

#### B) Configurar Cron Job no servidor
Adicione ao crontab:
```bash
* * * * * cd /caminho/do/projeto && php artisan schedule:run >> /dev/null 2>&1
```

Para editar o crontab:
```bash
crontab -e
```

---

### 4️⃣ Testar o Sistema

#### A) Executar comando manualmente
```bash
php artisan bankmanager:check-expenses
```

#### B) Verificar logs
```bash
tail -f storage/logs/laravel.log
```

#### C) Verificar notificações criadas
```sql
SELECT * FROM bank_manager_notifications ORDER BY created_at DESC LIMIT 10;
```

---

## 🔔 Integração Frontend (Push Notifications)

### 1️⃣ Adicionar Firebase SDK

No `resources/views/layouts/app.blade.php`, adicione antes de `</body>`:

```html
<!-- Firebase Push Notifications -->
<script type="module">
  // Import the functions you need from the SDKs you need
  import { initializeApp } from "https://www.gstatic.com/firebasejs/12.7.0/firebase-app.js";
  import { getMessaging, getToken, onMessage } from "https://www.gstatic.com/firebasejs/12.7.0/firebase-messaging.js";

  // Your web app's Firebase configuration
  const firebaseConfig = {
    apiKey: "AIzaSyC8JxELCGutTkRbnfSO9DpozYpPjvIbeXc",
    authDomain: "iruycode-final.firebaseapp.com",
    projectId: "iruycode-final",
    storageBucket: "iruycode-final.firebasestorage.app",
    messagingSenderId: "118646663792",
    appId: "1:118646463792:web:3e30555d3d05d035bde8a35",
    measurementId: "G-XVMk8VNQMR"
  };

  // Initialize Firebase
  const app = initializeApp(firebaseConfig);
  const messaging = getMessaging(app);

  // Request notification permission
  function requestNotificationPermission() {
    console.log('🔔 Solicitando permissão para notificações...');
    
    Notification.requestPermission().then((permission) => {
      if (permission === 'granted') {
        console.log('✅ Permissão concedida!');
        
        // Get FCM token (⚠️ OBTER VAPID KEY NO FIREBASE CONSOLE)
        getToken(messaging, { 
          vapidKey: 'SUBSTITUA_PELA_SUA_VAPID_KEY'
        })
        .then((currentToken) => {
          if (currentToken) {
            console.log('🔑 Token FCM:', currentToken);
            
            // Send token to Laravel backend
            fetch('/bank-manager/notifications/register-token', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
              },
              body: JSON.stringify({
                token: currentToken,
                device_name: navigator.userAgent
              })
            })
            .then(response => response.json())
            .then(data => {
              console.log('✅ Token registrado no servidor:', data);
            })
            .catch((error) => {
              console.error('❌ Erro ao registrar token:', error);
            });
          } else {
            console.log('⚠️ Nenhum token disponível.');
          }
        })
        .catch((err) => {
          console.error('❌ Erro ao obter token:', err);
        });
      } else {
        console.log('❌ Permissão negada');
      }
    });
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

<!-- Botão opcional para ativar notificações -->
<button onclick="window.requestBankManagerNotifications()" 
        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
    🔔 Ativar Notificações
</button>
```

### 2️⃣ Criar arquivo `firebase-messaging-sw.js`

Na pasta `public/`, crie `firebase-messaging-sw.js`:

```javascript
// Give the service worker access to Firebase Messaging
importScripts('https://www.gstatic.com/firebasejs/12.7.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/12.7.0/firebase-messaging-compat.js');

// Initialize the Firebase app in the service worker
firebase.initializeApp({
  apiKey: "AIzaSyC8JxELCGutTkRbnfSO9DpozYpPjvIbeXc",
  authDomain: "iruycode-final.firebaseapp.com",
  projectId: "iruycode-final",
  storageBucket: "iruycode-final.firebasestorage.app",
  messagingSenderId: "118646663792",
  appId: "1:118646463792:web:3e30555d3d05d035bde8a35"
});

const messaging = firebase.messaging();

// Handle background messages
messaging.onBackgroundMessage((payload) => {
  console.log('[firebase-messaging-sw.js] Mensagem em background:', payload);
  
  const notificationTitle = payload.notification.title || 'Bank Manager';
  const notificationOptions = {
    body: payload.notification.body || 'Nova notificação',
    icon: payload.notification.icon || '/icon.png',
    badge: '/badge.png',
    tag: 'bank-manager-notification',
    data: {
      click_action: payload.data?.click_action || payload.fcmOptions?.link || '/bank-manager'
    }
  };

  self.registration.showNotification(notificationTitle, notificationOptions);
});

// Handle notification click
self.addEventListener('notificationclick', (event) => {
  console.log('[firebase-messaging-sw.js] Notification click:', event);
  
  event.notification.close();
  
  const urlToOpen = event.notification.data.click_action || '/bank-manager';
  
  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true })
      .then((windowClients) => {
        // Check if there is already a window open
        for (let i = 0; i < windowClients.length; i++) {
          const client = windowClients[i];
          if (client.url === urlToOpen && 'focus' in client) {
            return client.focus();
          }
        }
        // If not, open new window
        if (clients.openWindow) {
          return clients.openWindow(urlToOpen);
        }
      })
  );
});
```

---

## 🎯 Dados Necessários no Banco

Para o sistema funcionar, certifique-se de que existem:

### Transactions
- `user_id`
- `operation_type_id = 2` (para despesas)
- `transaction_date`
- `is_recurring = true` (para despesas fixas)
- `due_day` (dia do mês para despesas fixas)
- `debt_installment_id` (para vincular a parcelas)

### Investments
- `user_id`
- `is_active = true`

### Debtors
- `user_id`
- `debtor_name`
- `due_date`
- `is_paid = false`
- `amount`

### Debts & Installments
- Tabela `app_bank_manager_debts` com `user_id`
- Tabela `app_bank_manager_debt_installments` com:
  - `debt_id`
  - `installment_number`
  - `amount`
  - `due_date`
  - `paid_at` (null para não pagas)

### Financial Goals
- `user_id`
- `name`
- `target_amount`
- `current_amount`
- `is_completed = false`

---

## 🧪 Teste de Integração

### Criar notificação de teste:
```php
use App\Modules\BankManager\Models\BankManagerNotification;
use App\Modules\BankManager\Services\PushNotificationService;

$notification = BankManagerNotification::create([
    'user_id' => 1,
    'type' => 'expense_recent',
    'title' => '💰 Teste de Notificação',
    'message' => 'Esta é uma notificação de teste!',
    'context' => 'test_' . now()->timestamp,
    'link' => '/bank-manager',
]);

$pushService = app(PushNotificationService::class);
$pushService->sendToUser(1, [
    'title' => $notification->title,
    'message' => $notification->message,
    'link' => $notification->link,
]);
```

---

## 📊 Monitoramento

### Ver status do scheduler:
```bash
php artisan schedule:list
```

### Forçar execução:
```bash
php artisan schedule:run
```

### Ver próximas execuções:
```bash
php artisan schedule:work
```

---

## ⚠️ Troubleshooting

### Comando não roda automaticamente
- Verifique se o cron job está configurado
- Verifique permissões: `chmod +x artisan`
- Verifique logs: `tail -f /var/log/cron.log`

### Push notifications não chegam
- Verifique FCM_SERVER_KEY no `.env`
- Verifique se tokens estão registrados: `SELECT * FROM fcm_tokens`
- Verifique logs do Laravel
- Teste manualmente o endpoint de registro de token

### Notificações duplicadas
- Cada notificação tem `context` único
- O sistema verifica `existsActive()` antes de criar
- Se duplicar, verifique a lógica de `context` no service

---

## 📝 Próximos Passos

1. ✅ Configurar Firebase
2. ✅ Executar migrations
3. ✅ Configurar cron job
4. ✅ Testar comando manualmente
5. ✅ Integrar push notifications no frontend
6. ✅ Adicionar link de notificações no menu
7. ✅ Personalizar mensagens por contexto

---

## 🎨 Customização

### Alterar horário de execução:
Em `routes/console.php`, altere:
```php
->dailyAt('09:00')  // Para outro horário
```

### Alterar timezone:
```php
->timezone('America/Sao_Paulo')  // Ou outro timezone
```

### Adicionar novos tipos de notificação:
1. Adicione o enum na migration `bank_manager_notifications`
2. Crie um novo Service em `app/Modules/BankManager/Services/Notifications/`
3. Registre no `CheckExpensesCommand.php`

---

## 📞 Suporte

Para dúvidas ou problemas, consulte:
- `NOTIFICATIONS_README.md` - Documentação completa
- Logs: `storage/logs/laravel.log`
- Firebase Console: https://console.firebase.google.com/

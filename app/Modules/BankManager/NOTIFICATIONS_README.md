# 📋 SISTEMA DE NOTIFICAÇÕES DO BANK MANAGER

## 🎯 VISÃO GERAL

O Bank Manager possui um sistema automatizado de notificações que monitora **7 áreas financeiras** diferentes e envia alertas via push notifications (Firebase) e notificações internas no banco de dados.

**Execução**: O comando `bankmanager:check-expenses` roda diariamente às **09:00** (timezone Lisboa) via Laravel Scheduler.

---

## 📦 ESTRUTURA DO SISTEMA

### Models
- **BankManagerNotification**: Armazena notificações no banco de dados
- **FcmToken**: Gerencia tokens Firebase Cloud Messaging dos dispositivos

### Services
- **PushNotificationService**: Envia push notifications via Firebase FCM
- **ExpenseService**: Verifica despesas recentes e fixas
- **InvestmentService**: Lembra de atualizar investimentos
- **DebtorService**: Notifica sobre cobranças de devedores
- **DebtService**: Alerta sobre parcelas de dívidas
- **GoalService**: Lembra de contribuir para metas
- **SpendingAlertService**: Alerta sobre gastos excessivos

### Command
- **CheckExpensesCommand**: Executa todas as verificações diariamente

### Controller
- **NotificationController**: API REST para gerenciar notificações

---

## 📋 REGRAS DE NEGÓCIO POR MÓDULO

### 1️⃣ DESPESAS RECENTES
**Objetivo**: Alertar quando o usuário não registra despesas há 2+ dias

**Regras**:
- ✅ Busca a última transação do tipo "expense" (operation_type_id = 2)
- ✅ Se passou 2 ou mais dias desde a última despesa → cria notificação
- ✅ Evita duplicação: verifica se já existe notificação ativa com `context='missing_expenses'`
- ✅ Envia push: "💰 Verifique suas despesas!"

**Dados da notificação**:
```json
{
  "type": "expense_recent",
  "title": "💰 Verifique suas despesas!",
  "message": "Já passaram X dias desde sua última despesa registrada...",
  "context": "missing_expenses",
  "data": {
    "last_expense_date": "2026-01-07",
    "days_since": 3
  },
  "link": "/bankmanager/transactions/create"
}
```

---

### 2️⃣ DESPESAS FIXAS PRÓXIMAS
**Objetivo**: Lembrar de despesas fixas próximas ao vencimento

**Regras**:
- ✅ Para cada despesa fixa registrada, calcula dias até vencimento
- ✅ Notifica em 3 momentos: **D-10**, **D-5** e **D-1** (10, 5 e 1 dia antes)
- ✅ Ignora meses em que o dia de vencimento não existe (ex: dia 31 em fevereiro)
- ✅ Evita duplicação: usa `context='fixed_expense_{id}_day_{dias}'`
- ✅ Mostra valor formatado em euros

**Dados da notificação**:
```json
{
  "type": "expense_fixed",
  "title": "Despesa Fixa Próxima",
  "message": "⏳ Em 10 dias: Netflix - €12.99",
  "context": "fixed_expense_42_day_10",
  "data": {
    "expense_id": 42,
    "description": "Netflix",
    "amount": 12.99,
    "due_day": 15,
    "due_date": "2026-01-15",
    "days_until": 10
  },
  "link": "/bankmanager/transactions"
}
```

---

### 3️⃣ INVESTIMENTOS
**Objetivo**: Lembrar diariamente de atualizar saldos de investimentos

**Regras**:
- ✅ Verifica se existem investimentos ativos
- ✅ Envia 1 lembrete por dia (não repete no mesmo dia)
- ✅ Usa context com data: `investments_update_reminder_YYYYMMDD`
- ✅ Mostra quantidade de investimentos ativos

**Dados da notificação**:
```json
{
  "type": "investment",
  "title": "💼 Atualize seus investimentos",
  "message": "Você tem 3 investimento(s) ativo(s). Não se esqueça de atualizar os saldos hoje!",
  "context": "investments_update_reminder_20260109",
  "data": {
    "active_count": 3,
    "date": "2026-01-09"
  },
  "link": "/bankmanager/investments"
}
```

---

### 4️⃣ DEVEDORES
**Objetivo**: Lembrar de cobrar pessoas que devem dinheiro

**Regras**:
- ✅ Notifica em 3 momentos: **D-5**, **D-1** e **D0** (5 dias antes, 1 dia antes e no dia)
- ✅ Apenas devedores não pagos (`is_paid = false`)
- ✅ Mensagens personalizadas por momento:
  - **D-5**: "⏳ Em 5 dias: {nome}"
  - **D-1**: "🔔 Lembrete: pagamento vence amanhã!"
  - **D0**: "📅 Pagamento HOJE: {nome}"
- ✅ Mostra valor em euros se disponível
- ✅ Context único por devedor e momento: `debtor_{id}_{label}`

**Dados da notificação**:
```json
{
  "type": "debtor",
  "title": "📅 Cobrança: João Silva",
  "message": "📅 Pagamento HOJE: João Silva - €150.00",
  "context": "debtor_12_today",
  "data": {
    "debtor_id": 12,
    "debtor_name": "João Silva",
    "amount": 150.00,
    "due_date": "2026-01-09",
    "days_until": 0,
    "alert_moment": "today"
  },
  "link": "/bankmanager/debtors/12"
}
```

---

### 5️⃣ DÍVIDAS (PARCELAS)
**Objetivo**: Lembrar de pagar parcelas de dívidas

#### A) LEMBRETES FUTUROS
**Regras**:
- ✅ Notifica em 4 momentos: **D-7**, **D-2**, **D-1** e **D0**
- ✅ Apenas parcelas não pagas (`paid_at = NULL`)
- ✅ Mensagens personalizadas:
  - **D-7**: "⏳ Parcela em 7 dias"
  - **D-2**: "⏰ Parcela em 2 dias"
  - **D-1**: "🔔 Amanhã vence"
  - **D0**: "📅 Pagamento HOJE"
- ✅ Evita duplicar no mesmo dia: verifica context + triggered_at

#### B) PARCELAS ATRASADAS
**Regras**:
- ✅ Notifica em 3 momentos: **D+5**, **D+10** e **diariamente após D+11**
- ✅ Mensagem: "⚠️ Parcela atrasada há X dia(s)"
- ✅ Context: `debt_{id}_inst{numero}_late_{dias_atraso}`

**Dados da notificação**:
```json
{
  "type": "debt",
  "title": "📅 Parcela 3",
  "message": "📅 Pagamento HOJE: Empréstimo Banco X - €250.00",
  "context": "debt_8_inst3_day_0",
  "data": {
    "debt_id": 8,
    "debt_name": "Empréstimo Banco X",
    "installment_id": 45,
    "installment_number": 3,
    "amount": 250.00,
    "due_date": "2026-01-09",
    "days_until": 0,
    "status": "upcoming"
  },
  "link": "/bankmanager/debts/8"
}
```

---

### 6️⃣ METAS FINANCEIRAS
**Objetivo**: Lembrar de contribuir para metas mensalmente

**Regras**:
- ✅ Executa apenas nos **dias 5, 10 e 20** de cada mês
- ✅ Apenas para metas não concluídas (`is_completed = false`)
- ✅ Verifica se já houve contribuição do tipo "add/deposit/increase" no mês
- ✅ Ignora metas que já atingiram o objetivo
- ✅ Context único por meta e data: `goal_{id}_no_contrib_YYYYMMDD`

**Dados da notificação**:
```json
{
  "type": "goal",
  "title": "🎯 Lembrete: Meta Financeira",
  "message": "Não se esqueça de contribuir para 'Viagem Europa'. Você está a 65.5% da meta!",
  "context": "goal_5_no_contrib_20260110",
  "data": {
    "goal_id": 5,
    "goal_name": "Viagem Europa",
    "target_amount": 3000.00,
    "current_amount": 1965.00,
    "remaining": 1035.00,
    "percent_complete": 65.5,
    "reminder_day": 10
  },
  "link": "/bankmanager/goals/5"
}
```

---

### 7️⃣ ALERTAS DE GASTOS
**Objetivo**: Alertar quando gastos mensais excedem limites comparados ao mês anterior

#### A) CATEGORIAS CONSIDERADAS
- ✅ Apenas categorias de despesa (`operation_type_id = 2`)
- ❌ **EXCLUI**: despesas fixas e parcelas (não contabiliza no alerta)

#### B) COMPARAÇÃO
- ✅ Compara gastos do mês atual com o mês anterior
- ✅ Calcula percentual: `(atual / anterior) × 100`

#### C) FAIXAS DE ALERTA
- **70%**: "⚠️ Atenção aos seus gastos!" (considere reduzir gastos não essenciais)
- **90%**: "🚨 Quase atingindo seu limite!" (planeje-se para não ultrapassar)
- **100%**: "❗ Você atingiu o nível médio!" (igualou ou ultrapassou o mês anterior)

#### D) REGRAS ESPECIAIS
- ✅ Envia apenas 1 notificação por faixa por mês
- ✅ Se ultrapassar 100%, envia apenas notificação de 100% (não envia múltiplas)
- ✅ Context: `spending_alert_YYYYMM_{faixa}`

**Dados da notificação**:
```json
{
  "type": "spending",
  "title": "🚨 Quase atingindo seu limite!",
  "message": "Você já gastou 90% do que gastou no mês passado. Planeje-se para não ultrapassar. (€1350.00 / €1500.00)",
  "context": "spending_alert_202601_90",
  "data": {
    "current_month_spending": 1350.00,
    "last_month_spending": 1500.00,
    "percentage": 90.00,
    "threshold": 90,
    "month": "2026-01"
  },
  "link": "/bankmanager/reports"
}
```

---

## 🔧 COMPONENTES TÉCNICOS

### Database Tables

#### `bank_manager_notifications`
```sql
- id
- user_id (FK users)
- type (enum: expense_recent, expense_fixed, investment, debtor, debt, goal, spending)
- title
- message
- context (unique - evita duplicação)
- data (JSON)
- link
- is_read (boolean)
- is_dismissed (boolean)
- triggered_at
- created_at, updated_at
```

#### `fcm_tokens`
```sql
- id
- user_id (FK users)
- token (unique)
- device_name
- last_used_at
- created_at, updated_at
```

---

## ⏰ SCHEDULER

**Configuração** em `routes/console.php`:

```php
Schedule::command('bankmanager:check-expenses')
    ->dailyAt('09:00')
    ->timezone('Europe/Lisbon')
    ->withoutOverlapping();
```

**Comportamento**:
- Roda **1x por dia às 09:00** (horário de Lisboa)
- `withoutOverlapping()`: não executa se ainda estiver rodando
- Executa todas as 7 verificações em sequência

---

## 🚀 PUSH NOTIFICATIONS (Firebase FCM)

**Service**: `PushNotificationService`

**Características**:
- ✅ Usa Firebase Cloud Messaging (FCM)
- ✅ Envia para todos os tokens registrados na tabela `fcm_tokens`
- ✅ Inclui link de redirecionamento (`webpush.fcm_options.link`)
- ✅ Loga sucessos e falhas
- ✅ Remove automaticamente tokens inválidos/expirados

**Configuração**:
1. Adicionar no `.env`: `FCM_SERVER_KEY=your-firebase-server-key`
2. Configurado em `config/services.php`

---

## 🎨 API ENDPOINTS

### Notificações
```
GET    /bank-manager/notifications              # Listar notificações
GET    /bank-manager/notifications/unread-count # Contagem de não lidas
POST   /bank-manager/notifications/{id}/read    # Marcar como lida
POST   /bank-manager/notifications/read-all     # Marcar todas como lidas
POST   /bank-manager/notifications/{id}/dismiss # Dispensar notificação
```

### FCM Tokens
```
POST   /bank-manager/notifications/register-token  # Registrar token
POST   /bank-manager/notifications/remove-token    # Remover token
GET    /bank-manager/notifications/tokens          # Listar tokens do usuário
```

---

## 📱 INTEGRAÇÃO FRONTEND

### Registrar Token FCM
```javascript
// Registrar token quando usuário permitir notificações
fetch('/bank-manager/notifications/register-token', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken
    },
    body: JSON.stringify({
        token: fcmToken,
        device_name: 'Chrome Desktop'
    })
});
```

### Obter Contagem de Não Lidas
```javascript
// Atualizar badge de notificações
fetch('/bank-manager/notifications/unread-count')
    .then(response => response.json())
    .then(data => {
        document.getElementById('notification-badge').textContent = data.count;
    });
```

---

## 🧪 TESTE MANUAL

### 1. Executar comando manualmente:
```bash
php artisan bankmanager:check-expenses
```

### 2. Verificar logs:
```bash
tail -f storage/logs/laravel.log
```

### 3. Verificar notificações criadas:
```sql
SELECT * FROM bank_manager_notifications 
WHERE user_id = 1 
ORDER BY created_at DESC;
```

---

## 📊 RESUMO PARA OUTRO COPILOT

O sistema funciona assim:

1. **Laravel Scheduler** dispara o comando `bankmanager:check-expenses` às 09:00 diariamente
2. O comando **CheckExpensesCommand** executa 7 services em sequência
3. Cada service verifica sua área e cria notificações quando necessário
4. As notificações são salvas no banco com **context único** para evitar duplicações
5. **Push notifications** são enviadas via Firebase para todos os dispositivos registrados
6. Usuário pode marcar notificações como "verificadas" ou "ignoradas" via métodos do model

**Tipos de notificação**: 
- `expense_recent` (despesas não registradas)
- `expense_fixed` (despesas fixas próximas)
- `investment` (atualizar investimentos)
- `debtor` (cobrar devedores)
- `debt` (pagar parcelas)
- `goal` (contribuir para metas)
- `spending` (alerta de gastos)

**Prevenção de duplicatas**: Cada tipo usa um context único (ex: `fixed_expense_123_day_5`) e verifica se já existe antes de criar.

---

## 📝 LICENÇA

Este módulo faz parte do IruyCode Project e segue a mesma licença do projeto principal.

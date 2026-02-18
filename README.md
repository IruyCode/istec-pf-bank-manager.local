# 🏦 Bank Manager - Manual do Usuário

> Aplicação web moderna para gerenciamento de finanças pessoais e controle de débitos.

## 📋 Índice

1. [Visão Geral](#visão-geral)
2. [Requisitos do Sistema](#requisitos-do-sistema)
3. [Instalação](#instalação)
4. [Configuração Inicial](#configuração-inicial)
5. [Como Usar](#como-usar)
6. [Funcionalidades](#funcionalidades)
7. [Suporte](#suporte)

---

## 🎯 Visão Geral

**Bank Manager** é uma plataforma de gerenciamento financeiro construída com Laravel 11, que permite:

- 👤 Gerenciar perfis de usuário (Admin e Cliente)
- 💳 Controlar débitos e transações
- 📊 Visualizar dashboards personalizados
- 🔒 Autenticação segura com Fortify
- 🔐 Autenticação de dois fatores (2FA)
- 🔑 Geração de tokens de acesso pessoal

### Arquitetura

O projeto segue padrão de **Monolith Modular**:

```
App Core (Controllers, Models, Listeners, Policies)
    ↓
Módulos (BankManager, Notifications)
    ↓
Serviços e Handlers
```

---

## 💻 Requisitos do Sistema

### Mínimos
- **PHP**: 8.3 ou superior
- **Node.js**: 18+ (para assets)
- **Composer**: 2.5+
- **Database**: MySQL 8.0+ ou PostgreSQL 13+

### Adicionais Recomendados
- **Redis**: Para cache otimizado
- **Java 17+**: Para gerar diagramas (PlantUML)
- **Docker**: Para ambientes isolados

#### Verificar versões instaladas:
```bash
php --version
composer --version
node --version
mysql --version  # ou psql --version
```

---

## 🚀 Instalação

### 1. Clonar o Repositório
```bash
git clone <seu-repositorio> banco-manager
cd banco-manager
```

### 2. Instalar Dependências PHP
```bash
composer install
```

### 3. Instalar Dependências JavaScript
```bash
npm install
```

### 4. Copiar Arquivo de Configuração
```bash
cp .env.example .env
```

### 5. Gerar Chave de Aplicação
```bash
php artisan key:generate
```

### 6. Criar Banco de Dados
```bash
# MySQL
mysql -u root -p -e "CREATE DATABASE banco_manager_db;"
```

### 7. Executar Migrações
```bash
php artisan migrate
```

### 8. Seedar Dados (Opcional)
```bash
php artisan db:seed
```

---

## ⚙️ Configuração Inicial

### Arquivo `.env`

Edite o `.env` com suas configurações:

```env
APP_NAME=BankManager
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=banco_manager_db
DB_USERNAME=root
DB_PASSWORD=

MAIL_DRIVER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=465
MAIL_USERNAME=seu_usuario
MAIL_PASSWORD=sua_senha

FORTIFY_FEATURES=registration,reset_passwords,two-factor-authentication,update-profile-information,update-passwords

FIREBASE_PROJECT_ID=seu_projeto
FIREBASE_PRIVATE_KEY_ID=sua_chave
FIREBASE_PRIVATE_KEY="sua_chave_privada"
FIREBASE_CLIENT_EMAIL=seu_email
```

### Cache e Sessão
```bash
php artisan storage:link
```

---

## 📖 Como Usar

### Iniciar o Servidor

#### **Opção 1: Servidor Built-in do Laravel**
```bash
php artisan serve
```
- URL: `http://localhost:8000`

#### **Opção 2: Vite Dev Server** (para assets em tempo real)
```bash
npm run dev
```
- Acesse em outra aba do terminal: `php artisan serve`

#### **Opção 3: Docker** (Recomendado)
```bash
docker-compose up -d
```

### Acessar a Aplicação

1. Abra no navegador: **http://localhost:8000**
2. Clique em **Register** para criar uma conta
3. Complete o formulário com seus dados
4. Faça login com seu email e senha

### Duas Vias de Acesso

#### 👤 **Painel do Cliente**
- Gerenciar seus débitos
- Visualizar histórico de transações
- Receber notificações
- Atualizar perfil

#### 🔐 **Painel Administrativo**
- Gerenciar todos os usuários
- Controlar débitos de clientes
- Enviar notificações em massa
- Relatórios financeiros

> **Nota**: Apenas usuários com role `admin` acessam o painel administrativo

---

## ✨ Funcionalidades Principais

### 1. Autenticação e Segurança

#### Login Tradicional
- Email e senha
- Recuperação de senha
- Logout seguro

#### Autenticação de Dois Fatores (2FA)
1. Configure 2FA nas configurações de perfil
2. Escaneie o código QR com seu app autenticador (Google Authenticator, Authy)
3. Guarde os códigos de backup em local seguro
4. Ao fazer login, digite o código de 6 dígitos

#### Tokens de Acesso Pessoal
- Gere tokens para integração com APIs externas
- Gerencie tokens na seção "Api Tokens" do perfil
- Copie e salve o token em local seguro (aparece uma única vez)

### 2. Gerenciamento de Débitos

#### Criar Novo Débito
1. Acesse **Débitos** → **Novo Débito**
2. Preencha os campos:
   - **Descrição**: Nome do débito
   - **Valor**: Montante devido
   - **Data de Vencimento**: Quando vence
   - **Status**: Pendente/Pago/Vencido
3. Clique **Salvar**

#### Editar ou Deletar
- Clique no débito na lista
- Use os botões **Editar** ou **Deletar**
- Confirme a ação

### 3. Notificações

#### Tipos de Notificações
- ✅ Débito pago com sucesso
- ⚠️ Débito próximo ao vencimento
- ❌ Débito vencido
- 📢 Notificações do administrador

#### Gerenciar Notificações
1. Clique no ícone de sino (🔔) no topo
2. Marque como lida
3. Clique para ver detalhes
4. Opção de deletar notificações antigas

### 4. Perfil de Usuário

#### Atualizar Informações
1. Acesse **Perfil** → **Editar Informações**
2. Modifique:
   - Nome completo
   - Email
   - Foto de perfil
3. Salve as mudanças

#### Alterar Senha
1. Vá para **Perfil** → **Segurança**
2. Insira sua senha atual
3. Digite a nova senha (mín. 8 caracteres)
4. Confirme a nova senha
5. Salve

#### Deletar Conta (Irreversível)
1. **Perfil** → **Perigo**
2. Clique **Deletar Minha Conta**
3. Confirme digitando sua senha
4. ⚠️ Espere 30 dias de reconsideração ou confirme agora

---

## 🎨 Interface

### Componentes Principais

| Seção | Descrição |
|-------|-----------|
| **Navbar** | Navegação, notificações e menu do usuário |
| **Sidebar** | Menu lateral com módulos (em painel admin) |
| **Dashboard** | Resumo de dados e atalhos rápidos |
| **Cards** | Exibição de informações resumidas |
| **Tabelas** | Lista de débitos, usuários, etc. |
| **Forms** | Formulários com validação |
| **Modals** | Confirmações e ações rápidas |

### Temas

- 🌙 Modo escuro automático (segue preferência do SO)
- ☀️ Modo claro
- 🎨 Cores personalizáveis em `tailwind.config.js`

---

## 🔧 Comandos Úteis

### Desenvolvimento

```bash
# Limpar cache
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Gerar classe modelo com migração
php artisan make:model Payment -m

# Criar controlador
php artisan make:controller PaymentController --resource

# Executar testes
php artisan test

# Ver rotas registradas
php artisan route:list
```

### Produção

```bash
# Compilar assets
npm run build

# Otimizar para produção
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 📊 Diagramas da Arquitetura

Execute para gerar diagrama em PNG:

```bash
plantuml -Tpng architecture.puml
```

O diagrama mostra:
- Núcleo da aplicação (Controllers, Models, Listeners, Policies)
- Módulos (BankManager, Notifications)
- Fluxo de serviços e dependências

---

## 🐛 Solução de Problemas

### Problema: "Class not found"
```bash
composer dump-autoload
```

### Problema: Permissões de pasta
```bash
chmod -R 775 storage bootstrap/cache
```

### Problema: Assets não carregam
```bash
npm run build
php artisan storage:link
```

### Problema: Banco de dados vazio
```bash
php artisan migrate:fresh --seed
```

### Problema: Erro 500 genérico
```bash
php artisan config:clear
php artisan cache:clear
tail -f storage/logs/laravel.log
```

---

## 📞 Suporte

### Documentação
- [Laravel Docs](https://laravel.com/docs)
- [Fortify Docs](https://laravel.com/docs/fortify)
- [Tailwind CSS](https://tailwindcss.com)

### Contato
- 📧 Email: suporte@bankmanager.com
- 💬 Issues: Abra uma issue neste repositório
- 📱 Chat: Comunidade no Discord

### Reportar Bugs
1. Descreva o problema com detalhes
2. Inclua passos para reproduzir
3. Versões do PHP, Laravel e Node.js
4. Mensagem de erro completa (logs)

---

## 📄 Licença

Este projeto está licenciado sob a **MIT License** - veja [LICENSE](LICENSE) para detalhes.

---

## 🤝 Contribuindo

Quer melhorar o Bank Manager?

1. Faça um **fork** do projeto
2. Crie uma **branch** para sua feature (`git checkout -b feature/NovaFuncionalidade`)
3. **Commit** suas mudanças (`git commit -m 'Adiciona NovaFuncionalidade'`)
4. **Push** para a branch (`git push origin feature/NovaFuncionalidade`)
5. Abra um **Pull Request**

---

**Desenvolvido com ❤️ usando Laravel 11 e Tailwind CSS**

_Última atualização: Fevereiro de 2026_

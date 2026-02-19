<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar E-mail - IruyCode Bank Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="login-body">
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="login-card card p-4 shadow-lg">
            <h3 class="text-center mb-4 fw-bold">Verifique seu e-mail</h3>

            @if (session('status') === 'verification-link-sent')
                <div class="alert alert-success text-center">
                    Um novo link de verificação foi enviado para seu e-mail.
                </div>
            @endif

            <p class="text-muted text-center mb-4">
                Antes de continuar, confirme o endereço de e-mail pelo link que enviamos.
            </p>

            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                    Reenviar e-mail de verificação
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}" class="mt-3">
                @csrf
                <button type="submit" class="btn btn-outline-secondary w-100 py-2 fw-bold">
                    Sair
                </button>
            </form>
        </div>
    </div>

    <style>
        .login-body {
            background: linear-gradient(-45deg, #0d6efd, #6610f2, #20c997, #0dcaf0);
            background-size: 400% 400%;
            animation: gradientMove 12s ease infinite;
        }

        @keyframes gradientMove {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        .login-card {
            min-width: 350px;
            border-radius: 16px;
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.9);
            animation: fadeIn 0.8s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .btn-primary,
        .btn-outline-secondary {
            border-radius: 12px;
        }
    </style>
</body>

</html>

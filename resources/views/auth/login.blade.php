<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - IruyCode E-commerce</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>



<body class="login-body">

    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="login-card card p-4 shadow-lg">
            <h3 class="text-center mb-4 fw-bold">Bem-vindo</h3>

            @if (session('status'))
                <div class="alert alert-success text-center">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">E-mail</label>
                    <input type="email" class="form-control" name="email" required autofocus>
                    @error('email')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Senha</label>
                    <input type="password" class="form-control" name="password" required>
                    @error('password')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" name="remember" checked>
                    <label class="form-check-label">Lembrar-me</label>
                </div>

                <button class="btn btn-primary w-100 py-2 fw-bold">Entrar</button>

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

        .form-control {
            border-radius: 10px;
        }

        .btn-primary {
            border-radius: 12px;
        }
    </style>

</body>


</html>

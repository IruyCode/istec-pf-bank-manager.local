<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <title>Smart Door Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <div class="container py-5">
        <h1 class="mb-4">🚪 Smart Door Dashboard</h1>

        <div class="row g-4">
            <div class="col-md-3">
                <div id="doorCard" class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Porta</h5>
                        <p id="doorStatus" class="fs-4">--</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div id="intrusionCard" class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Intrusão</h5>
                        <p id="intrusionStatus" class="fs-4">--</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Modo Noturno</h5>
                        <p id="nightModeStatus" class="fs-4">--</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Aberturas</h5>
                        <p id="openCount" class="fs-4">0</p>
                    </div>
                </div>
            </div>
        </div>

        <p class="text-muted mt-4">
            Última atualização: <span id="updatedAt">--</span>
        </p>
    </div>

    <script>
        async function loadStatus() {
            const response = await fetch('/iot/smart-door/status', {
                cache: 'no-store'
            });
            const data = await response.json();

            document.getElementById('doorStatus').innerText = data.door;
            document.getElementById('intrusionStatus').innerText = data.intrusion ? 'SIM' : 'NÃO';
            document.getElementById('nightModeStatus').innerText = data.nightMode ? 'ATIVO' : 'DESATIVADO';
            document.getElementById('openCount').innerText = data.openCount;
            document.getElementById('updatedAt').innerText = data.updated_at ?? '--';

            // Cores
            document.getElementById('doorCard').className =
                'card text-center ' + (data.door === 'OPEN' ? 'border-warning' : 'border-success');

            document.getElementById('intrusionCard').className =
                'card text-center ' + (data.intrusion ? 'border-danger' : 'border-secondary');
        }

        loadStatus();
        setInterval(loadStatus, 2000);
    </script>

</body>

</html>
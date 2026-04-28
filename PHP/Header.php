<!-- header.php -->
<head>
  <link rel="stylesheet" href="../CSS/header.css">
  <link rel="icon" href="../Images\Logos\MiniLogo.jfif" type="png">
  <!-- outros metadados -->
</head>
<body>
<header class="header">
    <div class="header-container">

        <!-- ESQUERDA -->
        <div class="header-left">
            <div>
                <div class="header-title">Feed</div>
                <div class="header-subtitle">Últimas publicações</div>
            </div>
        </div>

        <!-- DIREITA -->
        <div class="header-right">
            <details>
                <summary>⚙️</summary>

                <div class="dropdown">
                    <button onclick="location.href='configuracao.php';">
                        ⚙️ Configuração
                    </button>

                    <button class="danger" onclick="location.href='log_out.php';">
                        🚪 Log Out
                    </button>
                </div>
            </details>
        </div>

    </div>
</header>
</body>
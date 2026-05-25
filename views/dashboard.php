<?php
/**
 * View: Dashboard de Gerenciamento de Mesas
 * Template para exibição do floor management (gerenciamento de mesas)
 * * @var array $mesas Array de mesas com dados de pedidos
 * @var int $totalMesas
 * @var int $mesasOcupadas
 * @var int $mesasDisponiveis
 * @var float $faturamentoTotal
 * @var string $usuarioNome
 * @var string $usuarioRole
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MesaFlow | Floor Management</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Dashboard Styles -->
    <link rel="stylesheet" href="views/assets/css/dashboard.css">
</head>

<body class="dashboard-page">

    <main class="app-shell">

        <!-- ==================== HEADER ==================== -->
        <header class="app-header">
            <div class="brand-area">
                <div class="brand-icon">
                    <i class="fa-solid fa-utensils"></i>
                </div>
                <div class="brand-info">
                    <h1 class="app-title">MesaFlow</h1>
                    <p class="app-subtitle">Floor Management</p>
                </div>
            </div>

            <div class="header-actions">
                <div class="user-menu">
                    <span class="user-name"><?php echo htmlspecialchars($usuarioNome); ?></span>
                    <span class="user-role"><?php echo ucfirst($usuarioRole); ?></span>
                </div>
                <a href="index.php?action=logout" class="btn-logout" title="Sair">
                    <i class="fa-solid fa-sign-out-alt"></i>
                </a>
            </div>
        </header>

        <!-- ==================== CONTENT AREA ==================== -->
        <section class="content-area">

            <!-- Page Header com Totalizadores -->
            <div class="page-header">
                <div class="page-title">
                    <h2>Mesas do Salão</h2>
                    <p>Gerencie disponibilidade, pedidos e pagamentos em tempo real.</p>
                </div>

                <button class="btn-new-order" data-bs-toggle="modal" data-bs-target="#modalNovosPedidos">
                    <i class="fa-solid fa-plus"></i>
                    <span>Novo Pedido</span>
                </button>
            </div>

            <!-- Totalizadores (Status Rápido) -->
            <div class="status-cards">
                <div class="status-card">
                    <div class="status-icon" style="background: rgba(199, 255, 0, 0.1); color: #c7ff00;">
                        <i class="fa-solid fa-table-cells-large"></i>
                    </div>
                    <div class="status-info">
                        <span class="status-label">Total de Mesas</span>
                        <span class="status-value" id="total-mesas"><?php echo $totalMesas; ?></span>
                    </div>
                </div>

                <div class="status-card">
                    <div class="status-icon" style="background: rgba(70, 160, 255, 0.1); color: #46a0ff;">
                        <i class="fa-solid fa-check-circle"></i>
                    </div>
                    <div class="status-info">
                        <span class="status-label">Disponíveis</span>
                        <span class="status-value" id="mesas-disponiveis"><?php echo $mesasDisponiveis; ?></span>
                    </div>
                </div>

                <div class="status-card">
                    <div class="status-icon" style="background: rgba(255, 100, 100, 0.1); color: #ff6464;">
                        <i class="fa-solid fa-utensils"></i>
                    </div>
                    <div class="status-info">
                        <span class="status-label">Ocupadas</span>
                        <span class="status-value" id="mesas-ocupadas"><?php echo $mesasOcupadas; ?></span>
                    </div>
                </div>

                <div class="status-card">
                    <div class="status-icon" style="background: rgba(76, 175, 80, 0.1); color: #4caf50;">
                        <i class="fa-solid fa-money-bill-wave"></i>
                    </div>
                    <div class="status-info">
                        <span class="status-label">Faturamento</span>
                        <span class="status-value" id="faturamento-total">R$ <?php echo number_format($faturamentoTotal, 2, ',', '.'); ?></span>
                    </div>
                </div>
            </div>

            <!-- Grid de Mesas -->
            <div class="tables-grid" id="tables-grid">
                <?php foreach ($mesas as $mesa): ?>
                    <?php
                    $mesaId = $mesa['mesa_id'];
                    $numero = $mesa['numero'];
                    $status = $mesa['status'];
                    $temPedido = !empty($mesa['pedido_id']);
                    $valorTotal = floatval($mesa['valor_total'] ?? 0);
                    $tempoMinutos = intval($mesa['tempo_ocupacao_minutos'] ?? 0);
                    $tempoFormatado = Mesa::formatarTempo($tempoMinutos);
                    $progresso = Mesa::calcularProgressoOcupacao($tempoMinutos);
                    $garcom = htmlspecialchars($mesa['garcom_responsavel'] ?? 'N/A');
                    
                    // Determinar classe CSS de status
                    $classeStatus = $status === 'ocupada' ? 'occupied' : 'available';
                    if ($status === 'ocupada' && $valorTotal > 0) {
                        $classeStatus = 'payment'; // Pode estar pronta para pagamento
                    }
                    ?>
                    
                    <article class="table-card <?php echo $classeStatus; ?>" data-mesa-id="<?php echo $mesaId; ?>">
                        
                        <?php if ($status === 'ocupada' && $valorTotal > 150): ?>
                            <span class="small-dot" title="Conta alta"></span>
                        <?php endif; ?>

                        <div class="table-number"><?php echo $numero; ?></div>

                        <?php if ($status === 'ocupada'): ?>
                            <div class="table-seats"><?php echo $garcom; ?></div>
                            
                            <!-- Anel de Progresso (tempo de ocupação) -->
                            <div class="time-ring" style="--progress: <?php echo $progresso; ?>%;">
                                <?php echo $tempoFormatado; ?>
                            </div>

                            <div class="card-divider"></div>

                            <!-- Info de Conta -->
                            <div class="bill-row">
                                <span class="bill-label">Conta Atual</span>
                                <span class="bill-value">R$ <?php echo number_format($valorTotal, 2, ',', '.'); ?></span>
                            </div>
                        <?php else: ?>
                            <div class="table-seats">Vazia</div>
                            
                            <div class="time-ring" style="--progress: 0%;">
                                0m
                            </div>

                            <div class="card-divider"></div>

                            <!-- Status Disponível -->
                            <div class="status-row">
                                <i class="fa-regular fa-circle-check"></i>
                                <span>Disponível</span>
                            </div>
                        <?php endif; ?>

                        <!-- Botões de Ação (hover) -->
                        <div class="card-actions">
                            <?php if ($status === 'disponivel'): ?>
                                <button class="btn-action btn-abrir" data-mesa-id="<?php echo $mesaId; ?>" title="Abrir mesa">
                                    <i class="fa-solid fa-door-open"></i>
                                </button>
                            <?php else: ?>
                                <button class="btn-action btn-fechar" data-mesa-id="<?php echo $mesaId; ?>" title="Fechar mesa">
                                    <i class="fa-solid fa-door-closed"></i>
                                </button>
                                <button class="btn-action btn-pedido" data-mesa-id="<?php echo $mesaId; ?>" title="Ver pedido">
                                    <i class="fa-solid fa-clipboard-list"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

        </section>

        <!-- ==================== BOTTOM NAVIGATION ==================== -->
        <nav class="bottom-nav">
            <a href="index.php?action=dashboard" class="nav-item active">
                <i class="fa-solid fa-table-cells-large"></i>
                <span>FLOOR</span>
            </a>

            <a href="#" class="nav-item" title="Pedidos">
                <i class="fa-solid fa-cart-shopping"></i>
                <span>ORDER</span>
            </a>

            <a href="#" class="nav-item" title="Inventário">
                <i class="fa-solid fa-box"></i>
                <span>INVENTORY</span>
            </a>

            <a href="#" class="nav-item" title="Checkout">
                <i class="fa-solid fa-credit-card"></i>
                <span>CHECKOUT</span>
            </a>

            <a href="#" class="nav-item" title="Relatórios">
                <i class="fa-solid fa-chart-column"></i>
                <span>DASHBOARD</span>
            </a>

            <?php if ($usuarioRole === 'administrador'): ?>
                <a href="index.php?action=gerenciar_mesas" class="nav-item" title="Gerenciar Mesas">
                    <i class="fa-solid fa-sliders"></i>
                    <span>SETTINGS</span>
                </a>
            <?php endif; ?>
        </nav>

    </main>

    <!-- ==================== MODALS ==================== -->
    <div class="modal fade" id="modalNovosPedidos" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Novo Pedido</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Selecione uma mesa disponível para abrir um novo pedido.</p>
                    <p class="text-muted">Esta funcionalidade será integrada em breve.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Dashboard JS -->
    <script src="views/assets/js/dashboard.js"></script>
</body>

</html>

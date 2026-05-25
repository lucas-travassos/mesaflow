<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MesaFlow | Floor Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="views/assets/css/dashboard.css">
</head>

<?php

/**
 * View: Dashboard de Floor Management
 *
 * Variáveis injetadas pelo MesaController::dashboard():
 * @var array  $mesas
 * @var int    $totalMesas
 * @var int    $mesasOcupadas
 * @var int    $mesasDisponiveis
 * @var float  $faturamentoTotal
 * @var string $usuarioNome
 * @var string $usuarioRole
 */

// 1. Configurações que o header.php vai ler dinamicamente
$tituloPagina = 'MesaFlow | Floor Management';
$classeCorpo  = 'dashboard-page';
$jsEspecifico = 'dashboard.js';

// 2. Injeta o cabeçalho único e reaproveitável
include __DIR__ . '/includes/header.php';
?>

<body class="dashboard-page">

    <main class="app-shell">

        <!-- ==================== CONTENT ==================== -->
        <section class="content-area">

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

            <!-- Totalizadores -->
            <div class="status-cards">
                <div class="status-card">
                    <div class="status-icon" style="background:rgba(199,255,0,.1);color:#c7ff00;">
                        <i class="fa-solid fa-table-cells-large"></i>
                    </div>
                    <div class="status-info">
                        <span class="status-label">Total de Mesas</span>
                        <span class="status-value" id="total-mesas"><?php echo $totalMesas; ?></span>
                    </div>
                </div>
                <div class="status-card">
                    <div class="status-icon" style="background:rgba(70,160,255,.1);color:#46a0ff;">
                        <i class="fa-solid fa-check-circle"></i>
                    </div>
                    <div class="status-info">
                        <span class="status-label">Disponíveis</span>
                        <span class="status-value" id="mesas-disponiveis"><?php echo $mesasDisponiveis; ?></span>
                    </div>
                </div>
                <div class="status-card">
                    <div class="status-icon" style="background:rgba(255,100,100,.1);color:#ff6464;">
                        <i class="fa-solid fa-utensils"></i>
                    </div>
                    <div class="status-info">
                        <span class="status-label">Ocupadas</span>
                        <span class="status-value" id="mesas-ocupadas"><?php echo $mesasOcupadas; ?></span>
                    </div>
                </div>
                <div class="status-card">
                    <div class="status-icon" style="background:rgba(76,175,80,.1);color:#4caf50;">
                        <i class="fa-solid fa-money-bill-wave"></i>
                    </div>
                    <div class="status-info">
                        <span class="status-label">Faturamento</span>
                        <span class="status-value" id="faturamento-total">
                            R$ <?php echo number_format($faturamentoTotal, 2, ',', '.'); ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Grid de Mesas -->
            <div class="tables-grid" id="tables-grid">
                <?php foreach ($mesas as $mesa): ?>
                    <?php
                    $mesaId         = $mesa['mesa_id'];
                    $numero         = $mesa['numero'];
                    $status         = $mesa['status'];
                    $valorTotal     = (float) ($mesa['valor_total'] ?? 0);
                    $tempoMinutos   = (int) ($mesa['tempo_ocupacao_minutos'] ?? 0);
                    $tempoFormatado = Mesa::formatarTempo($tempoMinutos);
                    $progresso      = Mesa::calcularProgressoOcupacao($tempoMinutos); // CORREÇÃO: método agora existe
                    $garcom         = htmlspecialchars($mesa['garcom_nome'] ?? 'N/A'); // CORREÇÃO: era 'garcom_responsavel'
                    $classeStatus   = $status === 'ocupada' ? ($valorTotal > 150 ? 'payment' : 'occupied') : 'available';
                    ?>
                    <article class="table-card <?php echo $classeStatus; ?>" data-mesa-id="<?php echo $mesaId; ?>">

                        <?php if ($status === 'ocupada' && $valorTotal > 150): ?>
                            <span class="small-dot" title="Conta alta"></span>
                        <?php endif; ?>

                        <div class="table-number"><?php echo $numero; ?></div>

                        <?php if ($status === 'ocupada'): ?>
                            <div class="table-seats"><?php echo $garcom; ?></div>
                            <div class="time-ring" style="--progress: <?php echo $progresso; ?>%;">
                                <?php echo $tempoFormatado; ?>
                            </div>
                            <div class="card-divider"></div>
                            <div class="bill-row">
                                <span class="bill-label">Conta Atual</span>
                                <span class="bill-value">R$ <?php echo number_format($valorTotal, 2, ',', '.'); ?></span>
                            </div>
                        <?php else: ?>
                            <div class="table-seats">Vazia</div>
                            <div class="time-ring" style="--progress: 0%;">0m</div>
                            <div class="card-divider"></div>
                            <div class="status-row">
                                <i class="fa-regular fa-circle-check"></i>
                                <span>Disponível</span>
                            </div>
                        <?php endif; ?>

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

        <?php
        include __DIR__ . '/includes/footer.php';
        ?>

    </main>

    <!-- Modal: Novo Pedido -->
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="views/assets/js/dashboard.js"></script>
</body>

</html>
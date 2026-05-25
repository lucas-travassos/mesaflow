<?php
/**
 * View: Gerenciar Mesas (CRUD)
 * Template para gerenciamento completo de mesas (criar, editar, deletar)
 * * @var array $mesas Array de mesas
 * @var int $totalMesas Total de mesas
 * @var string $usuarioNome
 * @var string $usuarioRole
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MesaFlow | Gerenciar Mesas</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- CRUD Styles -->
    <link rel="stylesheet" href="views/assets/css/crud-mesas.css">
</head>

<body class="crud-page">

    <main class="app-shell">

        <!-- ==================== HEADER ==================== -->
        <header class="app-header">
            <div class="brand-area">
                <a href="index.php?action=floor" class="brand-link">
                    <div class="brand-icon">
                        <i class="fa-solid fa-utensils"></i>
                    </div>
                    <div class="brand-info">
                        <h1 class="app-title">MesaFlow</h1>
                        <p class="app-subtitle">Table Management</p>
                    </div>
                </a>
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

            <!-- Page Header -->
            <div class="page-header">
                <div class="page-title">
                    <h2>Gerenciar Mesas</h2>
                    <p>Criar, editar ou excluir mesas do restaurante.</p>
                </div>

                <button class="btn-novo" id="btn-nova-mesa" data-bs-toggle="modal" data-bs-target="#modalMesa">
                    <i class="fa-solid fa-plus"></i>
                    <span>Nova Mesa</span>
                </button>
            </div>

            <!-- Totalizador -->
            <div class="info-card">
                <div class="info-icon">
                    <i class="fa-solid fa-table-cells-large"></i>
                </div>
                <div class="info-content">
                    <span class="info-label">Total de Mesas</span>
                    <span class="info-value" id="total-mesas"><?php echo $totalMesas; ?></span>
                </div>
            </div>

            <!-- Tabela de Mesas -->
            <div class="table-container">
                <table class="mesas-table" id="mesas-table">
                    <thead>
                        <tr>
                            <th>Nº Mesa</th>
                            <th>Status</th>
                            <th>Pedido Aberto</th>
                            <th>Garçom</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="mesas-tbody">
                        <?php foreach ($mesas as $mesa): ?>
                            <tr data-mesa-id="<?php echo $mesa['mesa_id']; ?>">
                                <td>
                                    <strong>#<?php echo $mesa['numero']; ?></strong>
                                </td>
                                <td>
                                    <?php if ($mesa['status'] === 'disponivel'): ?>
                                        <span class="badge badge-status badge-disponivel">
                                            <i class="fa-solid fa-circle-check"></i> Disponível
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-status badge-ocupada">
                                            <i class="fa-solid fa-utensils"></i> Ocupada
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($mesa['pedido_id']): ?>
                                        <span class="badge bg-warning">
                                            <i class="fa-solid fa-circle"></i> Sim (Pedido #<?php echo $mesa['pedido_id']; ?>)
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">
                                            <i class="fa-solid fa-circle"></i> Não
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($mesa['garcom_responsavel'] ?? 'N/A'); ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button 
                                            class="btn-action btn-editar" 
                                            data-mesa-id="<?php echo $mesa['mesa_id']; ?>"
                                            data-numero="<?php echo $mesa['numero']; ?>"
                                            data-status="<?php echo $mesa['status']; ?>"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalMesa"
                                            title="Editar"
                                        >
                                            <i class="fa-solid fa-pencil"></i>
                                        </button>
                                        <button 
                                            class="btn-action btn-deletar" 
                                            data-mesa-id="<?php echo $mesa['mesa_id']; ?>"
                                            title="Deletar"
                                        >
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <?php if (empty($mesas)): ?>
                    <div class="empty-state">
                        <i class="fa-solid fa-inbox"></i>
                        <h3>Nenhuma mesa cadastrada</h3>
                        <p>Crie sua primeira mesa clicando no botão "Nova Mesa"</p>
                    </div>
                <?php endif; ?>
            </div>

        </section>

    </main>

    <!-- ==================== MODAL: CRIAR/EDITAR MESA ==================== -->
    <div class="modal fade" id="modalMesa" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Nova Mesa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form id="formMesa">
                    <input type="hidden" id="mesaId" name="mesa_id">
                    
                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label for="numero" class="form-label">Número da Mesa *</label>
                            <input 
                                type="number" 
                                class="form-control" 
                                id="numero" 
                                name="numero" 
                                placeholder="Ex: 1"
                                min="1"
                                required
                            >
                            <small class="text-muted">Números devem ser únicos</small>
                        </div>

                        <div class="form-group mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="disponivel">Disponível</option>
                                <option value="ocupada">Ocupada</option>
                            </select>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar Mesa</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- CRUD JS -->
    <script src="views/assets/js/crud-mesas.js"></script>
</body>

</html>

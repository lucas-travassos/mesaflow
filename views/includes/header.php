<?php

/**
 * Componente Reutilizável: Header Geral
 * @var string $usuarioNome
 * @var string $usuarioRole
 */
$tituloPagina = $tituloPagina ?? 'MesaFlow';
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title><?php echo htmlspecialchars($tituloPagina); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="views/assets/css/dashboard.css">
    <?php if (isset($cssEspecifico)): ?>
        <link rel="stylesheet" href="views/assets/css/<?php echo $cssEspecifico; ?>">
    <?php endif; ?>
</head>

<body class="<?php echo $classeCorpo ?? 'dashboard-page'; ?>">

    <main class="app-shell">
        <header class="app-header">
            <div class="brand-area">
                <a href="index.php?action=floor" class="text-decoration-none d-flex align-items-center gap-3">
                    <div class="brand-icon"><i class="fa-solid fa-utensils"></i></div>
                    <div class="brand-info">
                        <h1 class="app-title text-white m-0">MesaFlow</h1>
                        <p class="app-subtitle m-0 text-muted" style="font-size: 11px; letter-spacing: 1px;">MANAGEMENT SYSTEM</p>
                    </div>
                </a>
            </div>

            <div class="header-actions d-flex align-items-center gap-2">
                <div class="user-menu text-end d-none d-sm-block me-2">
                    <span class="user-name d-block fw-bold" style="font-size: 14px;"><?php echo htmlspecialchars($usuarioNome ?? 'Usuário'); ?></span>
                    <span class="user-role d-block text-warning text-uppercase" style="font-size: 11px;"><?php echo htmlspecialchars($usuarioRole ?? ''); ?></span>
                </div>

                <a href="index.php?action=floor" class="btn btn-sm btn-outline-light d-inline-flex align-items-center gap-2" style="border-color: var(--border-soft);">
                    <i class="fa-solid fa-border-all text-success"></i> <span class="d-none d-sm-inline">Salão de Mesas</span>
                </a>

                <?php if (isset($usuarioRole) && strtolower(trim($usuarioRole)) === 'administrador'): ?>
                    <a href="index.php?action=gerenciar_mesas" class="btn btn-sm btn-outline-light d-none d-md-inline-flex align-items-center gap-2" style="border-color: var(--border-soft);">
                        <i class="fa-solid fa-sliders text-warning"></i> <span>Painel Admin</span>
                    </a>
                <?php endif; ?>

                <a href="index.php?action=logout" class="btn-logout ms-2" title="Sair">
                    <i class="fa-solid fa-sign-out-alt"></i>
                </a>
            </div>
        </header>
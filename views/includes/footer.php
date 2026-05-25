<?php
/**
 * Componente Reutilizável: Footer Geral
 * @var string $usuarioRole
 */
$actionAtual = $_GET['action'] ?? 'floor';
?>
    <nav class="bottom-nav">
        <a href="index.php?action=floor" class="nav-item <?php echo ($actionAtual === 'floor' || $actionAtual === 'dashboard') ? 'active' : ''; ?>" title="Salão">
            <i class="fa-solid fa-border-all"></i><span>FLOOR</span>
        </a>
        <a href="#" class="nav-item" title="Pedidos Ativos">
            <i class="fa-solid fa-bowl-food"></i><span>ORDERS</span>
        </a>
        <a href="#" class="nav-item" title="Caixa / Checkout">
            <i class="fa-solid fa-credit-card"></i><span>CHECKOUT</span>
        </a>
        
        <?php if (isset($usuarioRole) && strtolower(trim($usuarioRole)) === 'administrador'): ?>
            <a href="index.php?action=gerenciar_mesas" class="nav-item <?php echo ($actionAtual === 'gerenciar_mesas') ? 'active' : ''; ?>" title="Configurações">
                <i class="fa-solid fa-sliders"></i><span>SETTINGS</span>
            </a>
        <?php endif; ?>
    </nav>

</main> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php if (isset($jsEspecifico)): ?>
    <script src="views/assets/js/<?php echo $jsEspecifico; ?>"></script>
<?php endif; ?>
</body>
</html>
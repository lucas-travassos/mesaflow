<?php
/**
 * index.php — Front Controller (router principal)
 * Todas as requisições passam por aqui.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/controllers/LoginController.php';
require_once __DIR__ . '/controllers/MesaController.php';

$action = $_GET['action'] ?? 'default';

// ─────────────────────────────────────────────
// Helpers de guarda (evita repetição de código)
// ─────────────────────────────────────────────

function requireAuth() {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: index.php?erro=Faça login para acessar o sistema.');
        exit;
    }
}

function requireAdmin() {
    requireAuth();
    if ($_SESSION['usuario_role'] !== 'administrador') {
        header('Location: index.php?action=floor&erro=Acesso negado.');
        exit;
    }
}

function requireAuthJson() {
    if (!isset($_SESSION['usuario_id'])) {
        header('Content-Type: application/json');
        echo json_encode(['sucesso' => false, 'erro' => 'Não autenticado']);
        exit;
    }
}

function requireAdminJson() {
    requireAuthJson();
    if ($_SESSION['usuario_role'] !== 'administrador') {
        header('Content-Type: application/json');
        echo json_encode(['sucesso' => false, 'erro' => 'Não autorizado']);
        exit;
    }
}

// ─────────────────────────────────────────────
// Roteamento
// ─────────────────────────────────────────────

switch ($action) {

    // Autenticação
    case 'login':
        (new LoginController())->autenticar();
        break;

    case 'logout':
        (new LoginController())->logout();
        break;

    // Dashboard (floor)
    case 'floor':
    case 'dashboard':
        requireAuth();
        (new MesaController())->dashboard();
        break;

    // Tela CRUD de mesas (admin)
    case 'gerenciar_mesas':
        requireAdmin();
        (new MesaController())->gerenciar();
        break;

    // ── Endpoints JSON ─────────────────────────

    // Listar mesas em JSON (dashboard.js + crud-mesas.js)
    case 'mesas_json':
    case 'listar_mesas':
    case 'listar_mesas_json':   // CORREÇÃO: crud-mesas.js chamava essa action que não existia
        requireAuthJson();
        (new MesaController())->getMesasJson();
        break;

    // Abrir mesa (dashboard.js) — CORREÇÃO: action não existia no router
    case 'abrir_mesa':
        requireAuthJson();
        (new MesaController())->abrirMesa();
        break;

    // Fechar mesa
    case 'fechar_mesa':
        requireAuthJson();
        (new MesaController())->fecharMesa();
        break;

    // CRUD de mesas (apenas admin)
    case 'criar_mesa':
        requireAdminJson();
        (new MesaController())->criarMesa();
        break;

    case 'atualizar_mesa':
        requireAdminJson();
        (new MesaController())->atualizarMesa();
        break;

    case 'deletar_mesa':
        requireAdminJson();
        (new MesaController())->deletarMesa();
        break;

    // ── Default ────────────────────────────────
    default:
        if (isset($_SESSION['usuario_id'])) {
            header('Location: index.php?action=floor');
        } else {
            $erro = $_GET['erro'] ?? null;
            include __DIR__ . '/views/login.php';
        }
        break;
}

<?php
// Ativa tratamento de sessões globais
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/controllers/LoginController.php';
require_once __DIR__ . '/controllers/MesaController.php';
require_once __DIR__ . '/controllers/MesaCRUDController.php';

$action = isset($_GET['action']) ? $_GET['action'] : 'default';

switch ($action) {
    case 'login':
        $controller = new LoginController();
        $controller->autenticar();
        break;

    case 'logout':
        $controller = new LoginController();
        $controller->logout();
        break;

    case 'floor':
    case 'dashboard':
        // Validação: usuário autenticado?
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: index.php?erro=Faça login para acessar o sistema.");
            exit;
        }

        $mesaController = new MesaController();
        $mesaController->dashboard();
        break;

    case 'mesas_json':
        // API: Retorna dados de mesas em JSON (para AJAX)
        if (!isset($_SESSION['usuario_id'])) {
            header('Content-Type: application/json');
            echo json_encode(['sucesso' => false, 'erro' => 'Não autenticado']);
            exit;
        }

        $mesaController = new MesaController();
        $mesaController->getMesasJson();
        break;

    case 'abrir_mesa':
        // AJAX: Abrir uma mesa
        if (!isset($_SESSION['usuario_id'])) {
            header('Content-Type: application/json');
            echo json_encode(['sucesso' => false, 'erro' => 'Não autenticado']);
            exit;
        }

        $mesaController = new MesaController();
        $mesaController->abrirMesa();
        break;

    case 'fechar_mesa':
        // AJAX: Fechar uma mesa
        if (!isset($_SESSION['usuario_id'])) {
            header('Content-Type: application/json');
            echo json_encode(['sucesso' => false, 'erro' => 'Não autenticado']);
            exit;
        }

        $mesaController = new MesaController();
        $mesaController->fecharMesa();
        break;

    // ===== ROTAS DE CRUD =====
    case 'gerenciar_mesas':
        // Exibir página de gerenciamento de mesas
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: index.php?erro=Faça login para acessar o sistema.");
            exit;
        }

        $crudController = new MesaCRUDController();
        $crudController->listar();
        break;

    case 'listar_mesas_json':
        // API CRUD: Retorna lista de mesas em JSON
        if (!isset($_SESSION['usuario_id'])) {
            header('Content-Type: application/json');
            echo json_encode(['sucesso' => false, 'erro' => 'Não autenticado']);
            exit;
        }

        $crudController = new MesaCRUDController();
        $crudController->listarJson();
        break;

    case 'criar_mesa':
        // AJAX CRUD: Criar nova mesa
        if (!isset($_SESSION['usuario_id'])) {
            header('Content-Type: application/json');
            echo json_encode(['sucesso' => false, 'erro' => 'Não autenticado']);
            exit;
        }

        $crudController = new MesaCRUDController();
        $crudController->criar();
        break;

    case 'atualizar_mesa':
        // AJAX CRUD: Atualizar mesa existente
        if (!isset($_SESSION['usuario_id'])) {
            header('Content-Type: application/json');
            echo json_encode(['sucesso' => false, 'erro' => 'Não autenticado']);
            exit;
        }

        $crudController = new MesaCRUDController();
        $crudController->atualizar();
        break;

    case 'deletar_mesa':
        // AJAX CRUD: Deletar uma mesa
        if (!isset($_SESSION['usuario_id'])) {
            header('Content-Type: application/json');
            echo json_encode(['sucesso' => false, 'erro' => 'Não autenticado']);
            exit;
        }

        $crudController = new MesaCRUDController();
        $crudController->deletar();
        break;

    default:
        // Rota padrão: Exibir tela de login
        $controller = new LoginController();
        $controller->index();
        break;
}
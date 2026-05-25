<?php
/**
 * Controller: MesaCRUDController
 * Responsável pela lógica de CRUD (Create, Read, Update, Delete) de mesas
 * 
 * Métodos principais:
 * - listar() : Exibe tela de gerenciamento de mesas
 * - criar() : Cria uma nova mesa
 * - atualizar() : Atualiza dados de uma mesa existente
 * - deletar() : Deleta uma mesa
 * - listarJson() : Retorna mesas em JSON para AJAX
 */

require_once __DIR__ . '/../models/Mesa.php';

class MesaCRUDController {
    private $mesaModel;
    private $usuarioRole;
    private $usuarioId;

    public function __construct() {
        // Verificar autenticação
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: index.php?erro=Você precisa estar autenticado.");
            exit;
        }

        // Verificar permissão (apenas administrador por enquanto)
        if ($_SESSION['usuario_role'] !== 'administrador') {
            header("Location: index.php?erro=Você não tem permissão para acessar esta área.");
            exit;
        }

        $this->mesaModel = new Mesa();
        $this->usuarioId = $_SESSION['usuario_id'];
        $this->usuarioRole = $_SESSION['usuario_role'];
    }

    /**
     * Exibir página de gerenciamento de mesas
     * 
     * Dados passados para a view:
     * - mesas: array de todas as mesas com dados de pedidos
     * - totalMesas: int total de mesas
     * - usuarioNome: string nome do usuário
     * - usuarioRole: string role do usuário
     */
    public function listar() {
        $mesas = $this->mesaModel->listar();
        $totalMesas = $this->mesaModel->getTotalMesas();

        $usuarioNome = $_SESSION['usuario_nome'];
        $usuarioRole = $this->usuarioRole;

        require_once __DIR__ . '/../views/gerenciar-mesas.php';
    }

    /**
     * Criar uma nova mesa
     * 
     * Espera POST com: numero, status (opcional)
     * Retorna JSON
     */
    public function criar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['sucesso' => false, 'erro' => 'Método inválido']);
            exit;
        }

        $numero = filter_input(INPUT_POST, 'numero', FILTER_VALIDATE_INT);
        $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING) ?? 'disponivel';

        if (!$numero || $numero <= 0) {
            header('Content-Type: application/json');
            echo json_encode(['sucesso' => false, 'erro' => 'Número da mesa inválido']);
            exit;
        }

        $mesaId = $this->mesaModel->criar($numero, $status);

        if ($mesaId) {
            header('Content-Type: application/json');
            echo json_encode([
                'sucesso' => true,
                'mensagem' => 'Mesa criada com sucesso',
                'mesa_id' => $mesaId,
                'numero' => $numero
            ]);
        } else {
            header('Content-Type: application/json');
            echo json_encode([
                'sucesso' => false,
                'erro' => 'Erro ao criar mesa. Verifique se o número já existe.'
            ]);
        }
        exit;
    }

    /**
     * Atualizar dados de uma mesa existente
     * 
     * Espera POST com: mesa_id, numero (opcional), status (opcional)
     * Retorna JSON
     */
    public function atualizar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['sucesso' => false, 'erro' => 'Método inválido']);
            exit;
        }

        $mesaId = filter_input(INPUT_POST, 'mesa_id', FILTER_VALIDATE_INT);
        $numero = filter_input(INPUT_POST, 'numero', FILTER_VALIDATE_INT);
        $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);

        if (!$mesaId) {
            header('Content-Type: application/json');
            echo json_encode(['sucesso' => false, 'erro' => 'Mesa ID inválido']);
            exit;
        }

        // Buscar mesa
        $mesa = $this->mesaModel->buscarPorId($mesaId);
        if (!$mesa) {
            header('Content-Type: application/json');
            echo json_encode(['sucesso' => false, 'erro' => 'Mesa não encontrada']);
            exit;
        }

        // Montar dados para atualizar
        $dados = [];
        if ($numero) {
            $dados['numero'] = $numero;
        }
        if ($status) {
            $dados['status'] = $status;
        }

        if (empty($dados)) {
            header('Content-Type: application/json');
            echo json_encode(['sucesso' => false, 'erro' => 'Nenhum dado para atualizar']);
            exit;
        }

        if ($this->mesaModel->atualizar($mesaId, $dados)) {
            header('Content-Type: application/json');
            echo json_encode([
                'sucesso' => true,
                'mensagem' => 'Mesa atualizada com sucesso',
                'mesa_id' => $mesaId
            ]);
        } else {
            header('Content-Type: application/json');
            echo json_encode([
                'sucesso' => false,
                'erro' => 'Erro ao atualizar mesa. Verifique se o número já existe.'
            ]);
        }
        exit;
    }

    /**
     * Deletar uma mesa
     * 
     * Espera POST com: mesa_id
     * Retorna JSON
     */
    public function deletar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['sucesso' => false, 'erro' => 'Método inválido']);
            exit;
        }

        $mesaId = filter_input(INPUT_POST, 'mesa_id', FILTER_VALIDATE_INT);

        if (!$mesaId) {
            header('Content-Type: application/json');
            echo json_encode(['sucesso' => false, 'erro' => 'Mesa ID inválido']);
            exit;
        }

        // Buscar mesa
        $mesa = $this->mesaModel->buscarPorId($mesaId);
        if (!$mesa) {
            header('Content-Type: application/json');
            echo json_encode(['sucesso' => false, 'erro' => 'Mesa não encontrada']);
            exit;
        }

        // Verificar se mesa tem pedido aberto
        if ($mesa['pedido_id'] !== null) {
            header('Content-Type: application/json');
            echo json_encode([
                'sucesso' => false,
                'erro' => 'Não é possível deletar uma mesa com pedido aberto'
            ]);
            exit;
        }

        if ($this->mesaModel->deletar($mesaId)) {
            header('Content-Type: application/json');
            echo json_encode([
                'sucesso' => true,
                'mensagem' => 'Mesa deletada com sucesso',
                'mesa_id' => $mesaId
            ]);
        } else {
            header('Content-Type: application/json');
            echo json_encode([
                'sucesso' => false,
                'erro' => 'Erro ao deletar mesa'
            ]);
        }
        exit;
    }

    /**
     * Retornar lista de mesas em JSON (para AJAX)
     */
    public function listarJson() {
        header('Content-Type: application/json');

        $mesas = $this->mesaModel->listar();

        $mesasFormatadas = array_map(function($mesa) {
            return [
                'mesa_id' => (int) $mesa['mesa_id'],
                'numero' => (int) $mesa['numero'],
                'status' => $mesa['status'],
                'tem_pedido' => !empty($mesa['pedido_id']),
                'pedido_id' => $mesa['pedido_id'] ? (int) $mesa['pedido_id'] : null,
                'valor_total' => $mesa['status'] === 'ocupada' ? floatval($mesa['valor_total']) : 0,
                'tempo_minutos' => $mesa['status'] === 'ocupada' ? intval($mesa['tempo_ocupacao_minutos']) : 0,
                'garcom' => $mesa['garcom_responsavel'] ?? 'N/A'
            ];
        }, $mesas);

        echo json_encode([
            'sucesso' => true,
            'mesas' => $mesasFormatadas,
            'total' => count($mesasFormatadas)
        ]);
        exit;
    }
}

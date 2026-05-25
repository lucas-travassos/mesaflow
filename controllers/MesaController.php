<?php
/**
 * Controller: MesaController
 * Ponto central de todas as operações de mesa (dashboard, CRUD, AJAX).
 * MesaCRUDController.php foi eliminado — toda lógica está aqui.
 */

require_once __DIR__ . '/../models/Mesa.php';

class MesaController {

    private Mesa   $mesaModel;
    private string $usuarioRole;
    private int    $usuarioId;
    private string $usuarioNome;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: index.php?erro=Você precisa estar autenticado.');
            exit;
        }
        $this->mesaModel  = new Mesa();
        $this->usuarioId   = (int) $_SESSION['usuario_id'];
        $this->usuarioRole = $_SESSION['usuario_role'] ?? 'garcom';
        $this->usuarioNome = $_SESSION['usuario_nome'] ?? 'Usuário';
    }

    // ─────────────────────────────────────────────
    // Views
    // ─────────────────────────────────────────────

    /**
     * Exibe o dashboard de floor management.
     */
    public function dashboard() {
        $mesas            = $this->mesaModel->listar();
        $totalMesas       = $this->mesaModel->getTotalMesas();
        $mesasOcupadas    = $this->mesaModel->getMesasOcupadas();
        $mesasDisponiveis = $this->mesaModel->getMesasDisponiveis();
        $faturamentoTotal = $this->mesaModel->getFaturamentoTotal();
        $usuarioNome      = $this->usuarioNome;
        $usuarioRole      = $this->usuarioRole;

        include __DIR__ . '/../views/dashboard.php';
    }

    /**
     * Exibe a tela de gerenciamento (CRUD) de mesas — apenas admin.
     */
    public function gerenciar() {
        if ($this->usuarioRole !== 'administrador') {
            header('Location: index.php?action=floor&erro=Acesso restrito.');
            exit;
        }
        $mesas       = $this->mesaModel->listar();
        $totalMesas  = $this->mesaModel->getTotalMesas();
        $usuarioNome = $this->usuarioNome;
        $usuarioRole = $this->usuarioRole;

        include __DIR__ . '/../views/gerenciar-mesas.php';
    }

    // ─────────────────────────────────────────────
    // Endpoints JSON (AJAX)
    // ─────────────────────────────────────────────

    /**
     * Retorna lista de mesas + totalizadores em JSON.
     * CORREÇÃO: estrutura alinhada com o que dashboard.js espera:
     *   { sucesso, mesas[], totalizadores{} }
     */
    public function getMesasJson() {
        header('Content-Type: application/json');

        $mesas = $this->mesaModel->listar();

        $mesasFormatadas = array_map(function ($mesa) {
            $minutos = (int) ($mesa['tempo_ocupacao_minutos'] ?? 0);
            return [
                'mesa_id'        => (int)  $mesa['mesa_id'],
                'numero'         => (int)  $mesa['numero'],
                'status'         => $mesa['status'],
                'tem_pedido'     => !empty($mesa['pedido_id']),
                'pedido_id'      => $mesa['pedido_id'] ? (int) $mesa['pedido_id'] : null,
                'valor_total'    => $mesa['status'] === 'ocupada' ? (float) $mesa['valor_total'] : 0.0,
                'tempo_minutos'  => $minutos,
                'tempo_formatado'=> Mesa::formatarTempo($minutos),
                'garcom'         => $mesa['garcom_nome'] ?? 'N/A',
            ];
        }, $mesas);

        echo json_encode([
            'sucesso' => true,
            'mesas'   => $mesasFormatadas,
            'totalizadores' => [
                'total'            => $this->mesaModel->getTotalMesas(),
                'disponiveis'      => $this->mesaModel->getMesasDisponiveis(),
                'ocupadas'         => $this->mesaModel->getMesasOcupadas(),
                'faturamento_total'=> $this->mesaModel->getFaturamentoTotal(),
            ],
        ]);
        exit;
    }

    /**
     * Abre uma mesa: cria pedido e marca como 'ocupada'.
     */
    public function abrirMesa() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['sucesso' => false, 'erro' => 'Método inválido']);
            exit;
        }

        $mesaId = filter_input(INPUT_POST, 'mesa_id', FILTER_VALIDATE_INT);
        if (!$mesaId) {
            echo json_encode(['sucesso' => false, 'erro' => 'Mesa ID inválido']);
            exit;
        }

        $pedidoId = $this->mesaModel->abrirMesa($mesaId, $this->usuarioId);
        if ($pedidoId) {
            echo json_encode(['sucesso' => true, 'mensagem' => 'Mesa aberta com sucesso', 'pedido_id' => $pedidoId]);
        } else {
            echo json_encode(['sucesso' => false, 'erro' => 'Não foi possível abrir a mesa. Verifique se já está ocupada.']);
        }
        exit;
    }

    /**
     * Fecha uma mesa: finaliza o pedido e marca como 'disponivel'.
     */
    public function fecharMesa() {
        header('Content-Type: application/json');

        $mesaId = filter_input(INPUT_POST, 'mesa_id', FILTER_VALIDATE_INT);
        if (!$mesaId) {
            echo json_encode(['sucesso' => false, 'erro' => 'Mesa ID inválido']);
            exit;
        }

        if ($this->mesaModel->fecharMesa($mesaId)) {
            echo json_encode(['sucesso' => true, 'mensagem' => 'Mesa fechada com sucesso']);
        } else {
            echo json_encode(['sucesso' => false, 'erro' => 'Falha ao fechar a mesa']);
        }
        exit;
    }

    /**
     * Cria uma nova mesa (apenas admin).
     */
    public function criarMesa() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['sucesso' => false, 'erro' => 'Método inválido']);
            exit;
        }

        $numero = filter_input(INPUT_POST, 'numero', FILTER_VALIDATE_INT);
        if (!$numero || $numero <= 0) {
            echo json_encode(['sucesso' => false, 'erro' => 'Número da mesa inválido']);
            exit;
        }

        $mesaId = $this->mesaModel->criar($numero);
        if ($mesaId) {
            echo json_encode(['sucesso' => true, 'mensagem' => 'Mesa criada com sucesso', 'mesa_id' => $mesaId, 'numero' => $numero]);
        } else {
            echo json_encode(['sucesso' => false, 'erro' => 'Erro ao criar mesa. Verifique se o número já existe.']);
        }
        exit;
    }

    /**
     * Atualiza número e/ou status de uma mesa (apenas admin).
     */
    public function atualizarMesa() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['sucesso' => false, 'erro' => 'Método inválido']);
            exit;
        }

        $mesaId = filter_input(INPUT_POST, 'mesa_id', FILTER_VALIDATE_INT);
        $numero = filter_input(INPUT_POST, 'numero',  FILTER_VALIDATE_INT);
        $status = filter_input(INPUT_POST, 'status',  FILTER_SANITIZE_SPECIAL_CHARS);

        if (!$mesaId) {
            echo json_encode(['sucesso' => false, 'erro' => 'Mesa ID inválido']);
            exit;
        }

        $dados = [];
        if ($numero && $numero > 0) $dados['numero'] = $numero;
        if ($status)               $dados['status'] = $status;

        if (empty($dados)) {
            echo json_encode(['sucesso' => false, 'erro' => 'Nenhum dado para atualizar']);
            exit;
        }

        if ($this->mesaModel->atualizar($mesaId, $dados)) {
            echo json_encode(['sucesso' => true, 'mensagem' => 'Mesa atualizada com sucesso']);
        } else {
            echo json_encode(['sucesso' => false, 'erro' => 'Erro ao atualizar mesa. Verifique se o número já existe.']);
        }
        exit;
    }

    /**
     * Deleta uma mesa (apenas admin).
     */
    public function deletarMesa() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['sucesso' => false, 'erro' => 'Método inválido']);
            exit;
        }

        $mesaId = filter_input(INPUT_POST, 'mesa_id', FILTER_VALIDATE_INT);
        if (!$mesaId) {
            echo json_encode(['sucesso' => false, 'erro' => 'Mesa ID inválido']);
            exit;
        }

        if ($this->mesaModel->deletar($mesaId)) {
            echo json_encode(['sucesso' => true, 'mensagem' => 'Mesa removida com sucesso']);
        } else {
            echo json_encode(['sucesso' => false, 'erro' => 'Não é possível deletar uma mesa com pedido aberto.']);
        }
        exit;
    }
}

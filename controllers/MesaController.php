<?php
/**
 * Controller: MesaController
 * Responsável pela lógica de apresentação e controle de fluxo de mesas
 * 
 * Métodos principais:
 * - dashboard() : Exibe a tela de dashboard de mesas
 * - getMesasJson() : Retorna dados de mesas em JSON (para AJAX)
 * - abrirMesa() : Abre uma mesa (inicia pedido)
 * - fecharMesa() : Fecha uma mesa (finaliza pedido)
 */

require_once __DIR__ . '/../models/Mesa.php';

class MesaController {
    private $mesaModel;
    private $usuarioRole;
    private $usuarioId;

    public function __construct() {
        // Injetar dependências
        $this->mesaModel = new Mesa();
        
        // Verificar autenticação
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: index.php?erro=Você precisa estar autenticado.");
            exit;
        }

        $this->usuarioId = $_SESSION['usuario_id'];
        $this->usuarioRole = $_SESSION['usuario_role'];
    }

    /**
     * Exibir dashboard principal de mesas (Floor Management)
     * 
     * Dados passados para a view:
     * - mesas: array de mesas com dados de pedidos
     * - totalMesas: int total de mesas
     * - mesasOcupadas: int total de mesas ocupadas
     * - mesasDisponiveis: int total de mesas disponíveis
     * - faturamentoTotal: float total em reais
     * - usuarioNome: string nome do usuário logado
     * - usuarioRole: string role do usuário
     */
    public function dashboard() {
        // Verificar permissão (por enquanto apenas admin, futuro: estender para garçom)
        // if ($this->usuarioRole !== 'administrador') {
        //     header("Location: index.php?erro=Você não tem permissão para acessar o dashboard.");
        //     exit;
        // }

        // Buscar dados do banco
        $mesas = $this->mesaModel->listar();
        $totalMesas = $this->mesaModel->getTotalMesas();
        $mesasOcupadas = $this->mesaModel->getMesasOcupadas();
        $mesasDisponiveis = $this->mesaModel->getMesasDisponiveis();
        $faturamentoTotal = $this->mesaModel->getFaturamentoTotal();

        // Passar dados para a view
        $usuarioNome = $_SESSION['usuario_nome'];
        $usuarioRole = $this->usuarioRole;

        // Incluir view
        require_once __DIR__ . '/../views/dashboard.php';
    }

    /**
     * Retornar dados de mesas em JSON (para requisições AJAX)
     * 
     * @return void Retorna JSON e encerra
     */
    public function getMesasJson() {
        header('Content-Type: application/json');

        $mesas = $this->mesaModel->listar();

        // Processar para formato mais amigável ao JSON
        $mesasFormatadas = array_map(function($mesa) {
            return [
                'mesa_id' => (int) $mesa['mesa_id'],
                'numero' => (int) $mesa['numero'],
                'status' => $mesa['status'],
                'tem_pedido' => !empty($mesa['pedido_id']),
                'pedido_id' => $mesa['pedido_id'] ? (int) $mesa['pedido_id'] : null,
                'valor_total' => $mesa['status'] === 'ocupada' ? floatval($mesa['valor_total']) : 0,
                'tempo_minutos' => $mesa['status'] === 'ocupada' ? intval($mesa['tempo_ocupacao_minutos']) : 0,
                'tempo_formatado' => $mesa['status'] === 'ocupada' ? Mesa::formatarTempo($mesa['tempo_ocupacao_minutos']) : '0m',
                'garcom' => $mesa['garcom_responsavel'] ?? 'N/A'
            ];
        }, $mesas);

        echo json_encode([
            'sucesso' => true,
            'mesas' => $mesasFormatadas,
            'totalizadores' => [
                'total' => $this->mesaModel->getTotalMesas(),
                'ocupadas' => $this->mesaModel->getMesasOcupadas(),
                'disponiveis' => $this->mesaModel->getMesasDisponiveis(),
                'faturamento_total' => $this->mesaModel->getFaturamentoTotal()
            ]
        ]);
        exit;
    }

    /**
     * Abrir uma mesa (criar um novo pedido)
     * 
     * Espera POST com: mesa_id
     */
    public function abrirMesa() {
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

        // Atualizar status para ocupada
        if ($this->mesaModel->abrirMesa($mesaId)) {
            header('Content-Type: application/json');
            echo json_encode([
                'sucesso' => true,
                'mensagem' => 'Mesa aberta com sucesso',
                'mesa_id' => $mesaId
            ]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['sucesso' => false, 'erro' => 'Erro ao atualizar mesa']);
        }
        exit;
    }

    /**
     * Fechar uma mesa (finalizar pedido)
     * 
     * Espera POST com: mesa_id
     */
    public function fecharMesa() {
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

        // Atualizar status para disponível
        if ($this->mesaModel->fecharMesa($mesaId)) {
            header('Content-Type: application/json');
            echo json_encode([
                'sucesso' => true,
                'mensagem' => 'Mesa fechada com sucesso',
                'mesa_id' => $mesaId
            ]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['sucesso' => false, 'erro' => 'Erro ao fechar mesa']);
        }
        exit;
    }
}

<?php
/**
 * Model: Mesa
 * Responsável por todas as operações de banco relacionadas às mesas.
 */

require_once __DIR__ . '/../config/Database.php';

class Mesa {
    private $db;
    private $table = 'mesas';

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Lista todas as mesas com dados do pedido aberto (se houver).
     * Alias da coluna do garçom corrigido para 'garcom_nome'.
     */
    public function listar() {
        $query = "
            SELECT
                m.mesa_id,
                m.numero,
                m.status,
                p.pedido_id,
                p.status       AS pedido_status,
                p.data_abertura,
                p.valor_total,
                TIMESTAMPDIFF(MINUTE, p.data_abertura, NOW()) AS tempo_ocupacao_minutos,
                u.nome         AS garcom_nome
            FROM {$this->table} m
            LEFT JOIN pedidos p ON m.mesa_id = p.mesa_id AND p.status = 'aberto'
            LEFT JOIN usuarios u ON p.usuario_id = u.usuario_id
            ORDER BY m.numero ASC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca uma mesa pelo ID.
     */
    public function buscarPorId($mesaId) {
        $query = "SELECT * FROM {$this->table} WHERE mesa_id = :mesa_id LIMIT 1";
        $stmt  = $this->db->prepare($query);
        $stmt->bindParam(':mesa_id', $mesaId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Busca uma mesa pelo número.
     */
    public function buscarPorNumero($numero) {
        $query = "SELECT * FROM {$this->table} WHERE numero = :numero LIMIT 1";
        $stmt  = $this->db->prepare($query);
        $stmt->bindParam(':numero', $numero, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Cria uma nova mesa.
     * Retorna o ID inserido ou false em caso de falha.
     */
    public function criar($numero, $status = 'disponivel') {
        if (!is_numeric($numero) || $numero <= 0) {
            return false;
        }
        if (!in_array($status, ['disponivel', 'ocupada'])) {
            $status = 'disponivel';
        }
        if ($this->buscarPorNumero($numero)) {
            return false; // número já existe
        }
        $query = "INSERT INTO {$this->table} (numero, status) VALUES (:numero, :status)";
        $stmt  = $this->db->prepare($query);
        $stmt->bindParam(':numero', $numero, PDO::PARAM_INT);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        if ($stmt->execute()) {
            return (int) $this->db->lastInsertId();
        }
        return false;
    }

    /**
     * Atalho para criar mesa sem status (compatibilidade interna).
     */
    public function criarMesa($numero) {
        return $this->criar($numero) ? true : false;
    }

    /**
     * Atualiza campos de uma mesa via array associativo.
     * Aceita: numero, status.
     */
    public function atualizar($mesaId, array $dados) {
        if (empty($dados)) {
            return false;
        }

        $sets   = [];
        $params = [':mesa_id' => $mesaId];

        foreach ($dados as $campo => $valor) {
            if ($campo === 'numero' && is_numeric($valor) && $valor > 0) {
                $existente = $this->buscarPorNumero($valor);
                if ($existente && (int)$existente['mesa_id'] !== (int)$mesaId) {
                    return false; // número em uso por outra mesa
                }
                $sets[]           = 'numero = :numero';
                $params[':numero'] = (int) $valor;
            } elseif ($campo === 'status' && in_array($valor, ['disponivel', 'ocupada'])) {
                $sets[]           = 'status = :status';
                $params[':status'] = $valor;
            }
        }

        if (empty($sets)) {
            return false;
        }

        $query = "UPDATE {$this->table} SET " . implode(', ', $sets) . " WHERE mesa_id = :mesa_id";
        $stmt  = $this->db->prepare($query);
        foreach ($params as $chave => $valor) {
            $stmt->bindValue($chave, $valor);
        }
        return $stmt->execute();
    }

    /**
     * Atalho para atualizar número e/ou status (compatibilidade com MesaController).
     */
    public function atualizarMesa($mesaId, $numero, $status = null) {
        $dados = ['numero' => $numero];
        if ($status !== null) {
            $dados['status'] = $status;
        }
        return $this->atualizar($mesaId, $dados);
    }

    /**
     * Abre uma mesa: cria um pedido 'aberto' e marca a mesa como 'ocupada'.
     * Retorna o pedido_id criado ou false.
     */
    public function abrirMesa($mesaId, $usuarioId) {
        // Verifica se já existe pedido aberto nessa mesa
        $queryCheck = "SELECT pedido_id FROM pedidos WHERE mesa_id = :mesa_id AND status = 'aberto' LIMIT 1";
        $stmtCheck  = $this->db->prepare($queryCheck);
        $stmtCheck->bindParam(':mesa_id', $mesaId, PDO::PARAM_INT);
        $stmtCheck->execute();
        if ($stmtCheck->fetch()) {
            return false; // mesa já está ocupada
        }

        $this->db->beginTransaction();
        try {
            // Cria o pedido
            $queryPedido = "INSERT INTO pedidos (mesa_id, usuario_id, status, valor_total) VALUES (:mesa_id, :usuario_id, 'aberto', 0.00)";
            $stmtPedido  = $this->db->prepare($queryPedido);
            $stmtPedido->bindParam(':mesa_id',   $mesaId,   PDO::PARAM_INT);
            $stmtPedido->bindParam(':usuario_id', $usuarioId, PDO::PARAM_INT);
            $stmtPedido->execute();
            $pedidoId = (int) $this->db->lastInsertId();

            // Atualiza status da mesa
            $queryMesa = "UPDATE {$this->table} SET status = 'ocupada' WHERE mesa_id = :mesa_id";
            $stmtMesa  = $this->db->prepare($queryMesa);
            $stmtMesa->bindParam(':mesa_id', $mesaId, PDO::PARAM_INT);
            $stmtMesa->execute();

            $this->db->commit();
            return $pedidoId;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Fecha uma mesa: marca o pedido como 'pago' e a mesa como 'disponivel'.
     * CORREÇÃO: usava 'finalizado' que não existe no ENUM — corrigido para 'pago'.
     */
    public function fecharMesa($mesaId) {
        $this->db->beginTransaction();
        try {
            $queryPedido = "UPDATE pedidos SET status = 'pago', data_fechamento = NOW() WHERE mesa_id = :mesa_id AND status = 'aberto'";
            $stmtPedido  = $this->db->prepare($queryPedido);
            $stmtPedido->bindParam(':mesa_id', $mesaId, PDO::PARAM_INT);
            $stmtPedido->execute();

            $queryMesa = "UPDATE {$this->table} SET status = 'disponivel' WHERE mesa_id = :mesa_id";
            $stmtMesa  = $this->db->prepare($queryMesa);
            $stmtMesa->bindParam(':mesa_id', $mesaId, PDO::PARAM_INT);
            $stmtMesa->execute();

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Deleta uma mesa. Bloqueia se houver pedido aberto.
     */
    public function deletar($mesaId) {
        $queryCheck = "SELECT pedido_id FROM pedidos WHERE mesa_id = :mesa_id AND status = 'aberto' LIMIT 1";
        $stmtCheck  = $this->db->prepare($queryCheck);
        $stmtCheck->bindParam(':mesa_id', $mesaId, PDO::PARAM_INT);
        $stmtCheck->execute();
        if ($stmtCheck->fetch()) {
            return false;
        }
        $query = "DELETE FROM {$this->table} WHERE mesa_id = :mesa_id";
        $stmt  = $this->db->prepare($query);
        $stmt->bindParam(':mesa_id', $mesaId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // ─────────────────────────────────────────────
    // Métodos de agregação (totalizadores)
    // ─────────────────────────────────────────────

    public function getTotalMesas() {
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM {$this->table}");
        return (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function getMesasOcupadas() {
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM {$this->table} WHERE status = 'ocupada'");
        return (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function getMesasDisponiveis() {
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM {$this->table} WHERE status = 'disponivel'");
        return (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function getFaturamentoTotal() {
        $stmt = $this->db->query("SELECT COALESCE(SUM(valor_total), 0) AS total FROM pedidos WHERE status = 'aberto'");
        return (float) $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    // ─────────────────────────────────────────────
    // Helpers estáticos de formatação
    // ─────────────────────────────────────────────

    /**
     * Formata minutos em string legível (ex: 1h 30m).
     */
    public static function formatarTempo($minutos) {
        if ($minutos === null || $minutos <= 0) return '0m';
        if ($minutos < 60) return $minutos . 'm';
        $horas = (int) floor($minutos / 60);
        $mins  = $minutos % 60;
        return $mins === 0 ? $horas . 'h' : $horas . 'h ' . $mins . 'm';
    }

    /**
     * Calcula percentual de progresso de ocupação (base: 120 min = 100%).
     * CORREÇÃO: método estava sendo chamado no dashboard.php mas não existia.
     */
    public static function calcularProgressoOcupacao($minutos) {
        if ($minutos === null || $minutos <= 0) return 0;
        return (int) min(round(($minutos / 120) * 100), 100);
    }
}

<?php
/**
 * Model: Mesa
 * Responsável pela manipulação e lógica de dados da tabela 'mesas'
 * 
 * Métodos principais:
 * - listar() : Retorna todas as mesas com informações de pedidos
 * - buscarPorId() : Busca uma mesa específica
 * - getTotalMesas() : Conta total de mesas
 * - getMesasOcupadas() : Conta mesas com status 'ocupada'
 * - getMesasDisponiveis() : Conta mesas com status 'disponivel'
 * - getFaturamentoTotal() : Soma valor total de todos os pedidos abertos
 */

require_once __DIR__ . '/../config/Database.php';

class Mesa {
    private $db;
    private $table = 'mesas';

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Listar todas as mesas com informações agregadas de pedidos
     * 
     * @return array Array de mesas com dados de pedidos (tempo de ocupação, total da conta)
     */
    public function listar() {
        $query = "
            SELECT 
                m.mesa_id,
                m.numero,
                m.status,
                p.pedido_id,
                p.status AS pedido_status,
                p.data_abertura,
                p.valor_total,
                TIMESTAMPDIFF(MINUTE, p.data_abertura, NOW()) AS tempo_ocupacao_minutos,
                u.nome AS garcom_responsavel
            FROM {$this->table} m
            LEFT JOIN pedidos p ON m.mesa_id = p.mesa_id AND p.status = 'aberto'
            LEFT JOIN usuarios u ON p.usuario_id = u.usuario_id
            ORDER BY m.numero ASC
        ";

        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Buscar uma mesa específica pelo ID
     * 
     * @param int $mesaId ID da mesa
     * @return array|false Dados da mesa ou false se não encontrada
     */
    public function buscarPorId($mesaId) {
        $query = "
            SELECT 
                m.*,
                p.pedido_id,
                p.status AS pedido_status,
                p.data_abertura,
                p.valor_total,
                TIMESTAMPDIFF(MINUTE, p.data_abertura, NOW()) AS tempo_ocupacao_minutos
            FROM {$this->table} m
            LEFT JOIN pedidos p ON m.mesa_id = p.mesa_id AND p.status = 'aberto'
            WHERE m.mesa_id = :mesa_id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':mesa_id', $mesaId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch();
    }

    /**
     * Buscar mesa por número
     * 
     * @param int $numero Número da mesa
     * @return array|false Dados da mesa ou false
     */
    public function buscarPorNumero($numero) {
        $query = "SELECT * FROM {$this->table} WHERE numero = :numero LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':numero', $numero, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch();
    }

    /**
     * Contar total de mesas no restaurante
     * 
     * @return int Total de mesas
     */
    public function getTotalMesas() {
        $query = "SELECT COUNT(*) as total FROM {$this->table}";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();
        
        return (int) $result['total'];
    }

    /**
     * Contar mesas com status 'ocupada'
     * 
     * @return int Total de mesas ocupadas
     */
    public function getMesasOcupadas() {
        $query = "SELECT COUNT(*) as total FROM {$this->table} WHERE status = 'ocupada'";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();
        
        return (int) $result['total'];
    }

    /**
     * Contar mesas com status 'disponivel'
     * 
     * @return int Total de mesas disponíveis
     */
    public function getMesasDisponiveis() {
        $query = "SELECT COUNT(*) as total FROM {$this->table} WHERE status = 'disponivel'";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();
        
        return (int) $result['total'];
    }

    /**
     * Calcular faturamento total (soma de todos os pedidos abertos)
     * 
     * @return float Valor total em reais
     */
    public function getFaturamentoTotal() {
        $query = "SELECT COALESCE(SUM(valor_total), 0) as total FROM pedidos WHERE status = 'aberto'";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();
        
        return (float) $result['total'];
    }

    /**
     * Atualizar status da mesa
     * 
     * @param int $mesaId ID da mesa
     * @param string $status Novo status (disponivel|ocupada)
     * @return bool Sucesso da operação
     */
    public function atualizarStatus($mesaId, $status) {
        $statusValidos = ['disponivel', 'ocupada'];
        
        if (!in_array($status, $statusValidos)) {
            return false;
        }

        $query = "UPDATE {$this->table} SET status = :status WHERE mesa_id = :mesa_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->bindParam(':mesa_id', $mesaId, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    /**
     * Abrir uma mesa (mudar para ocupada)
     * 
     * @param int $mesaId ID da mesa
     * @return bool Sucesso da operação
     */
    public function abrirMesa($mesaId) {
        return $this->atualizarStatus($mesaId, 'ocupada');
    }

    /**
     * Fechar uma mesa (mudar para disponivel)
     * 
     * @param int $mesaId ID da mesa
     * @return bool Sucesso da operação
     */
    public function fecharMesa($mesaId) {
        return $this->atualizarStatus($mesaId, 'disponivel');
    }

    /**
     * Buscar mesas por status
     * 
     * @param string $status Status desejado
     * @return array Array de mesas
     */
    public function buscarPorStatus($status) {
        $query = "SELECT * FROM {$this->table} WHERE status = :status ORDER BY numero ASC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Criar uma nova mesa
     * 
     * @param int $numero Número da mesa
     * @param string $status Status inicial (padrão: disponivel)
     * @return bool|int Sucesso e ID da mesa criada
     */
    public function criar($numero, $status = 'disponivel') {
        // Validar número
        if (!is_numeric($numero) || $numero <= 0) {
            return false;
        }

        // Verificar se número já existe
        $existe = $this->buscarPorNumero($numero);
        if ($existe) {
            return false;
        }

        // Validar status
        $statusValidos = ['disponivel', 'ocupada'];
        if (!in_array($status, $statusValidos)) {
            $status = 'disponivel';
        }

        $query = "INSERT INTO {$this->table} (numero, status) VALUES (:numero, :status)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':numero', $numero, PDO::PARAM_INT);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);

        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }

        return false;
    }

    /**
     * Atualizar dados de uma mesa
     * 
     * @param int $mesaId ID da mesa
     * @param array $dados Array com campos a atualizar (numero, status)
     * @return bool Sucesso da operação
     */
    public function atualizar($mesaId, $dados) {
        $camposPermitidos = ['numero', 'status'];
        $atualizacoes = [];
        $params = [];

        // Montar query dinamicamente
        foreach ($dados as $campo => $valor) {
            if (in_array($campo, $camposPermitidos)) {
                if ($campo === 'numero') {
                    // Validar se novo número não existe
                    $mesaExistente = $this->buscarPorNumero($valor);
                    if ($mesaExistente && $mesaExistente['mesa_id'] != $mesaId) {
                        continue; // Pular este campo
                    }
                    $atualizacoes[] = "numero = :numero";
                    $params[':numero'] = (int) $valor;
                }

                if ($campo === 'status') {
                    $statusValidos = ['disponivel', 'ocupada'];
                    if (!in_array($valor, $statusValidos)) {
                        continue; // Pular este campo
                    }
                    $atualizacoes[] = "status = :status";
                    $params[':status'] = $valor;
                }
            }
        }

        if (empty($atualizacoes)) {
            return false;
        }

        $query = "UPDATE {$this->table} SET " . implode(", ", $atualizacoes) . " WHERE mesa_id = :mesa_id";
        $stmt = $this->db->prepare($query);
        $params[':mesa_id'] = $mesaId;

        foreach ($params as $chave => $valor) {
            $stmt->bindValue($chave, $valor);
        }

        return $stmt->execute();
    }

    /**
     * Deletar uma mesa
     * 
     * Nota: Só permite deletar mesas disponíveis (sem pedidos abertos)
     * 
     * @param int $mesaId ID da mesa
     * @return bool Sucesso da operação
     */
    public function deletar($mesaId) {
        // Buscar mesa
        $mesa = $this->buscarPorId($mesaId);
        if (!$mesa) {
            return false;
        }

        // Verificar se mesa tem pedido aberto
        if ($mesa['pedido_id'] !== null) {
            return false; // Não permite deletar mesa com pedido aberto
        }

        // Se chegou aqui, mesa está disponível e sem pedidos
        $query = "DELETE FROM {$this->table} WHERE mesa_id = :mesa_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':mesa_id', $mesaId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Converter tempo em minutos para formato legível (ex: "45 min" ou "1h 30min")
     * 
     * @param int $minutos Número de minutos
     * @return string Tempo formatado
     */
    public static function formatarTempo($minutos) {
        if ($minutos === null) {
            return '0m';
        }

        if ($minutos < 60) {
            return $minutos . 'm';
        }

        $horas = floor($minutos / 60);
        $mins = $minutos % 60;
        
        if ($mins === 0) {
            return $horas . 'h';
        }

        return $horas . 'h ' . $mins . 'm';
    }

    /**
     * Calcular percentual de progresso para barra circular (0-100)
     * Assume que uma mesa lota em ~120 minutos (2 horas)
     * 
     * @param int $minutos Tempo de ocupação em minutos
     * @return int Percentual (0-100)
     */
    public static function calcularProgressoOcupacao($minutos) {
        if ($minutos === null) {
            return 0;
        }

        $progress = ($minutos / 120) * 100;
        
        return min($progress, 100); // Cap em 100%
    }
}

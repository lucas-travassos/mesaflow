<?php
require_once __DIR__ . '/../models/Usuario.php';

class LoginController {
    private $debug = false; // Debug desativado
    private $debugFile = __DIR__ . '/../debug.log';
    
    public function index() {
        // Exibe a tela de login
        $erro = isset($_GET['erro']) ? $_GET['erro'] : null;
        require_once __DIR__ . '/../views/login.php';
    }

    public function autenticar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $senha = $_POST['senha'] ?? '';

            $this->log("=== TENTATIVA DE LOGIN ===");
            $this->log("Email recebido: " . $email);
            $this->log("Senha recebida: " . (strlen($senha) . " caracteres"));

            if (empty($email) || empty($senha)) {
                header("Location: index.php?erro=Campos obrigatórios vazios.");
                exit;
            }

            $modelUsuario = new Usuario();
            $usuario = $modelUsuario->buscarPorEmail($email);

            // Verifica se usuário existe e se está ativo no sistema
            if ($usuario) {
                $this->log("✓ Usuário encontrado no banco: " . $usuario['nome']);
                $this->log("Email no banco: " . $usuario['email']);
                $this->log("Status: " . $usuario['status']);
                $this->log("Hash no banco: " . substr($usuario['senha'], 0, 20) . "...");

                if ($usuario['status'] !== 'ativo') {
                    $this->log("✗ Usuário inativo");
                    header("Location: index.php?erro=Sua conta está inativa. Contate o administrador.");
                    exit;
                }

                // Verifica a hash criptográfica da senha
                $senhaValida = password_verify($senha, $usuario['senha']);
                $this->log("Password verify resultado: " . ($senhaValida ? "VERDADEIRO" : "FALSO"));

                if ($senhaValida) {
                    $this->log("✓ AUTENTICAÇÃO BEM-SUCEDIDA");
                    
                    // Inicializa sessão
                    if (session_status() === PHP_SESSION_NONE) {
                        session_start();
                    }

                    $_SESSION['usuario_id']     = $usuario['usuario_id'];
                    $_SESSION['usuario_nome']   = $usuario['nome'];
                    $_SESSION['usuario_role']   = $usuario['nivel_acesso']; // administrador, garcom, caixa

                    // Redirecionamento para o dashboard de mesas
                    header("Location: index.php?action=floor");
                    exit;
                } else {
                    $this->log("✗ Senha inválida");
                }
            } else {
                $this->log("✗ Usuário NÃO encontrado no banco para email: " . $email);
            }

            // Fallback para credenciais inválidas (segurança contra enumeração de e-mails)
            $this->log("FALHA NA AUTENTICAÇÃO\n");
            header("Location: index.php?erro=E-mail ou senha inválidos.");
            exit;
        }
    }

    private function log($mensagem) {
        if ($this->debug) {
            file_put_contents($this->debugFile, date('Y-m-d H:i:s') . " - " . $mensagem . "\n", FILE_APPEND);
        }
    }

    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_destroy();
        header("Location: index.php");
        exit;
    }
}
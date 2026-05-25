<?php
/**
 * SCRIPT DE TESTE DE SENHA
 * Acesse: http://localhost/mesaflow/test_password.php
 * Use este script para gerar o hash correto e testar a senha
 */

// Senha que você quer usar
$senhaDesejada = 'admin123';

// Gera o hash correto
$hashCorreto = password_hash($senhaDesejada, PASSWORD_BCRYPT);

// Testa a verificação
$verificacao = password_verify($senhaDesejada, $hashCorreto);

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MesaFlow - Teste de Senha</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #161514;
            color: #fff;
            padding: 40px 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: rgba(35, 32, 29, 0.78);
            padding: 30px;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.14);
        }
        h1 {
            color: #c7ff00;
            margin-bottom: 30px;
        }
        .info-box {
            background: rgba(70, 160, 255, 0.1);
            border-left: 4px solid #46a0ff;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .success-box {
            background: rgba(199, 255, 0, 0.1);
            border-left: 4px solid #c7ff00;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .error-box {
            background: rgba(255, 68, 68, 0.1);
            border-left: 4px solid #ff4444;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        code {
            background: #000;
            padding: 10px;
            border-radius: 4px;
            display: block;
            margin: 10px 0;
            word-break: break-all;
            font-size: 12px;
        }
        .copy-button {
            background: #c7ff00;
            color: #000;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
            font-weight: bold;
        }
        .copy-button:hover {
            background: #a8d600;
        }
        .sql-command {
            background: #1a1a1f;
            padding: 15px;
            border-radius: 4px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Teste de Senha - MesaFlow</h1>

        <div class="info-box">
            <strong>Senha testada:</strong> <code><?php echo htmlspecialchars($senhaDesejada); ?></code>
        </div>

        <div class="success-box">
            <strong>✓ Hash Gerado (BCRYPT):</strong>
            <code><?php echo htmlspecialchars($hashCorreto); ?></code>
            <button class="copy-button" onclick="copiar('<?php echo htmlspecialchars($hashCorreto); ?>')">Copiar Hash</button>
        </div>

        <div class="success-box">
            <strong>✓ Verificação:</strong> 
            <?php if ($verificacao): ?>
                <span style="color: #c7ff00;">✓ SUCESSO - Senha verificada corretamente!</span>
            <?php else: ?>
                <span style="color: #ff4444;">✗ FALHA - Senha não corresponde ao hash!</span>
            <?php endif; ?>
        </div>

        <h2 style="margin-top: 40px; color: #c7ff00;">📋 Comando SQL para atualizar o banco</h2>
        
        <p>Execute este comando no seu banco de dados (phpMyAdmin ou linha de comando MySQL):</p>

        <div class="sql-command">
            <code>UPDATE usuarios SET senha = '<?php echo htmlspecialchars($hashCorreto); ?>' WHERE email = 'admin@mesaflow.com';</code>
            <button class="copy-button" onclick="copiar(`UPDATE usuarios SET senha = '<?php echo htmlspecialchars($hashCorreto); ?>' WHERE email = 'admin@mesaflow.com';`)">Copiar SQL</button>
        </div>

        <h2 style="margin-top: 40px; color: #c7ff00;">🔧 Instruções</h2>
        <ol>
            <li>Copie o comando SQL acima</li>
            <li>Abra <strong>phpMyAdmin</strong> (http://localhost/phpmyadmin)</li>
            <li>Selecione o banco de dados <strong>mesaflow</strong></li>
            <li>Vá para a aba <strong>SQL</strong></li>
            <li>Cole o comando e clique em <strong>Executar</strong></li>
            <li>Tente fazer login novamente com <strong>admin@mesaflow.com / admin123</strong></li>
        </ol>

        <div class="info-box" style="margin-top: 40px;">
            <strong>💡 Dica:</strong> Se preferir usar outra senha, modifique a variável <code>$senhaDesejada</code> no topo deste arquivo (linha 8) e recarregue a página.
        </div>

        <a href="index.php" style="display: inline-block; margin-top: 20px; color: #c7ff00; text-decoration: none;">← Voltar ao Login</a>
    </div>

    <script>
        function copiar(texto) {
            navigator.clipboard.writeText(texto).then(() => {
                alert('✓ Copiado para a área de transferência!');
            }).catch(() => {
                alert('Erro ao copiar. Tente manualmente.');
            });
        }
    </script>
</body>
</html>

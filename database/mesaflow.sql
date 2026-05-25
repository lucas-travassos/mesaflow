-- 1. TABELA DE USUÁRIOS (Administrador, Garçom, Caixa)
CREATE TABLE `usuarios`(
    `usuario_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE, -- Adicionado UNIQUE para evitar e-mails duplicados
    `senha` VARCHAR(255) NOT NULL,
    `nivel_acesso` ENUM('administrador', 'garcom', 'caixa') NOT NULL,
    `status` ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 2. TABELA DE MESAS
CREATE TABLE `mesas`(
    `mesa_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `numero` INT NOT NULL UNIQUE, -- Evita a criação de duas mesas com o mesmo número
    `status` ENUM('disponivel', 'ocupada') NOT NULL DEFAULT 'disponivel'
);

-- 3. TABELA DE PRODUTOS
CREATE TABLE `produtos`(
    `produto_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(100) NOT NULL,
    `descricao` TEXT NULL, -- Alterado para NULL pois nem todo produto precisa de descrição longa
    `preco` DECIMAL(8, 2) NOT NULL,
    `status` ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 4. TABELA DE PEDIDOS (Controle de abertura e fechamento da mesa)
CREATE TABLE `pedidos`(
    `pedido_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `mesa_id` INT UNSIGNED NOT NULL,
    `usuario_id` INT UNSIGNED NOT NULL, -- Usuário que abriu ou gerencia o pedido
    `data_abertura` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `data_fechamento` DATETIME NULL, -- NULL por padrão, preenchido no fechamento
    `status` ENUM('aberto', 'pago', 'cancelado') NOT NULL DEFAULT 'aberto',
    `valor_total` DECIMAL(8, 2) NOT NULL DEFAULT 0.00
);

-- 5. TABELA DE ITENS DO PEDIDO (Produtos vinculados ao pedido)
CREATE TABLE `itens_pedido`(
    `item_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `pedido_id` INT UNSIGNED NOT NULL,
    `produto_id` INT UNSIGNED NOT NULL,
    `quantidade` INT NOT NULL,
    `preco_unitario` DECIMAL(8, 2) NOT NULL, -- Guarda o preço do momento da venda
    `observacao` VARCHAR(255) NULL -- Alterado para NULL (nem todo pedido tem observação)
);

-- Relações e Chaves Estrangeiras (Constraints)
ALTER TABLE `pedidos` 
    ADD CONSTRAINT `fk_pedidos_mesas` FOREIGN KEY(`mesa_id`) REFERENCES `mesas`(`mesa_id`),
    ADD CONSTRAINT `fk_pedidos_usuarios` FOREIGN KEY(`usuario_id`) REFERENCES `usuarios`(`usuario_id`);

ALTER TABLE `itens_pedido` 
    ADD CONSTRAINT `fk_itens_pedido_pedidos` FOREIGN KEY(`pedido_id`) REFERENCES `pedidos`(`pedido_id`) ON DELETE CASCADE,
    ADD CONSTRAINT `fk_itens_pedido_produtos` FOREIGN KEY(`produto_id`) REFERENCES `produtos`(`produto_id`);


-- Inserir 20 mesas (números de 1 a 20)
INSERT INTO `mesas` (`numero`, `status`) VALUES
(1, 'disponivel'),
(2, 'disponivel'),
(3, 'disponivel'),
(4, 'disponivel'),
(5, 'disponivel'),
(6, 'disponivel'),
(7, 'disponivel'),
(8, 'disponivel'),
(9, 'disponivel'),
(10, 'disponivel'),
(11, 'disponivel'),
(12, 'disponivel'),
(13, 'disponivel'),
(14, 'disponivel'),
(15, 'disponivel'),
(16, 'disponivel'),
(17, 'disponivel'),
(18, 'disponivel'),
(19, 'disponivel'),
(20, 'disponivel');

-- Verificar inserção
SELECT COUNT(*) AS total_mesas FROM mesas;

INSERT INTO `pedidos` (`mesa_id`, `usuario_id`, `data_abertura`, `status`, `valor_total`) VALUES
(1, 1, DATE_SUB(NOW(), INTERVAL 45 MINUTE), 'aberto', 127.50),
(3, 1, DATE_SUB(NOW(), INTERVAL 78 MINUTE), 'aberto', 245.80),
(4, 1, DATE_SUB(NOW(), INTERVAL 22 MINUTE), 'aberto', 68.20),
(6, 1, DATE_SUB(NOW(), INTERVAL 61 MINUTE), 'aberto', 189.40),
(8, 1, DATE_SUB(NOW(), INTERVAL 15 MINUTE), 'aberto', 42.90);

UPDATE `mesas` SET `status` = 'ocupada' WHERE `mesa_id` IN (1, 3, 4, 6, 8);

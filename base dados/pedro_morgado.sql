-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Tempo de geração: 13-Jul-2025 às 17:59
-- Versão do servidor: 8.0.42
-- versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de dados: `pedro_morgado`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `bilhetes`
--

CREATE TABLE `bilhetes` (
  `id` int NOT NULL,
  `evento_id` int NOT NULL,
  `nome` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `quantidade` int NOT NULL,
  `comprado_em` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(20) NOT NULL DEFAULT 'Pendente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dados da tabela `bilhetes`
--

INSERT INTO `bilhetes` (`id`, `evento_id`, `nome`, `email`, `quantidade`, `comprado_em`, `status`) VALUES
(1, 1, 'Secção A - Concerto Porto - 15€', 'clienteA@email.com', 2, '2025-07-11 16:00:29', 'Pendente'),
(2, 1, 'Secção B - Concerto Porto - 10€', 'clienteB@email.com', 3, '2025-07-11 16:00:29', 'Pendente'),
(3, 2, 'Concerto Lisboa - 15,50€', 'clienteLisboa@email.com', 1, '2025-07-11 16:00:29', 'Pendente');

-- --------------------------------------------------------

--
-- Estrutura da tabela `cart`
--

CREATE TABLE `cart` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `event_id` int NOT NULL,
  `quantidade` int NOT NULL DEFAULT '1',
  `adicionado_em` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `config`
--

CREATE TABLE `config` (
  `id` int NOT NULL,
  `chave` varchar(100) NOT NULL,
  `valor` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dados da tabela `config`
--

INSERT INTO `config` (`id`, `chave`, `valor`) VALUES
(1, 'admin_email', 'pmorgado77@gmail.com');

-- --------------------------------------------------------

--
-- Estrutura da tabela `eventos`
--

CREATE TABLE `eventos` (
  `id` int NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descricao` text,
  `data` date DEFAULT NULL,
  `hora` varchar(10) DEFAULT NULL,
  `local` varchar(255) DEFAULT NULL,
  `categoria` varchar(100) NOT NULL DEFAULT 'Concertos',
  `criado_em` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `preco` decimal(10,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dados da tabela `eventos`
--

INSERT INTO `eventos` (`id`, `titulo`, `descricao`, `data`, `hora`, `local`, `categoria`, `criado_em`, `preco`) VALUES
(1, 'Concerto Porto', 'Concerto ao vivo no Porto', '2025-09-15', '22h', 'Casa da Música', 'Concertos', '2025-07-11 16:00:29', 0.00),
(2, 'Concerto Lisboa', 'Concerto ao vivo em Lisboa', '2025-10-10', '22h', 'Coliseu dos Recreios', 'Concertos', '2025-07-11 16:00:29', 0.00),
(3, 'Wild Flowers @ Wish You Were Here', '', '2025-01-25', '22h', 'Porto', 'Concertos', '2025-07-11 16:00:29', 0.00),
(4, 'Wild Flowers @ Teatro Passagem de Nível', '', '2024-10-31', '22h', 'Alfornelos', 'Concertos', '2025-07-11 16:00:29', 0.00),
(5, 'Getbacks @ Taskinha do Mar', '', '2024-01-06', '16h', 'Alfornelos', 'Concertos', '2025-07-11 16:00:29', 0.00),
(6, 'Arrústica @ Teatro Passagem de Nível', '', '2023-10-28', '22h', 'Alfornelos', 'Concertos', '2025-07-11 16:00:29', 0.00),
(7, 'Colina de Feras @ Festas Santulhão', '', '2022-08-14', '23h', 'Bragança', 'Concertos', '2025-07-11 16:00:29', 0.00),
(8, 'Colina de Feras @ Cooperativa São João dos Montes', '', '2022-08-06', '22h', 'Vila Franca de Xira', 'Concertos', '2025-07-11 16:00:29', 0.00),
(9, 'Arrústica @ Festival de Telheiras', '', '2022-05-28', '20h', 'Lisboa', 'Concertos', '2025-07-11 16:00:29', 0.00),
(10, 'Lost Train Trio @ Great Lisbon Club, Camones Bar', '', '2021-12-04', '19h30', 'Lisboa', 'Concertos', '2025-07-11 16:00:29', 0.00),
(11, 'Lost Train Trio @ Maria Cachucha Restaurante Bar', '', '2021-11-05', '22h00', 'Torres Vedras', 'Concertos', '2025-07-11 16:00:29', 0.00),
(12, 'Lost Train Trio @ Museu da Música', '', '2021-10-29', '19h00', 'Lisboa', 'Concertos', '2025-07-11 16:00:29', 0.00),
(13, 'Gata Funk @ Festival Telheiras', '', '2021-09-17', '18h', 'Lisboa', 'Concertos', '2025-07-11 16:00:29', 0.00),
(14, 'Lost Train Trio @ Fábrica da Pólvora', '', '2021-09-11', '19h00', 'Oeiras', 'Concertos', '2025-07-11 16:00:29', 0.00),
(15, 'eCLETRICa @ Museu da Música', '', '2020-02-21', '19h00', 'Lisboa', 'Concertos', '2025-07-11 16:00:29', 0.00),
(16, 'Wild Flowers @ Nirvana Studios Custom Café', '', '2020-01-18', '23h00', 'Oeiras', 'Concertos', '2025-07-11 16:00:29', 0.00),
(17, 'Album \"Caixa de Surpresas\"', '', NULL, '', '', 'Álbuns', '2025-07-11 16:00:29', 0.00),
(18, 'Album \"Ecletrica\"', '', NULL, '', '', 'Álbuns', '2025-07-11 16:00:29', 0.00),
(19, 'Album \"Ecletrica II\"', '', NULL, '', '', 'Álbuns', '2025-07-11 16:00:29', 0.00),
(20, 'Album \"HOME\"', '', NULL, '', '', 'Álbuns', '2025-07-11 16:00:29', 0.00),
(21, 'Album \"Retrato\"', '', NULL, '', '', 'Álbuns', '2025-07-11 16:00:29', 0.00);

-- --------------------------------------------------------

--
-- Estrutura da tabela `events`
--

CREATE TABLE `events` (
  `id` int NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `descricao` text,
  `data` date NOT NULL,
  `hora` time DEFAULT NULL,
  `local` varchar(150) NOT NULL,
  `preco` decimal(8,2) NOT NULL DEFAULT '15.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `mensagens`
--

CREATE TABLE `mensagens` (
  `id` int NOT NULL,
  `nome` varchar(100) NOT NULL,
  `apelido` varchar(100) NOT NULL,
  `data_nascimento` date DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `telefone` varchar(30) DEFAULT NULL,
  `mensagem` text NOT NULL,
  `data_envio` datetime NOT NULL,
  `respondido` tinyint(1) DEFAULT '0',
  `resposta` text,
  `data_resposta` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dados da tabela `mensagens`
--

INSERT INTO `mensagens` (`id`, `nome`, `apelido`, `data_nascimento`, `email`, `telefone`, `mensagem`, `data_envio`, `respondido`, `resposta`, `data_resposta`) VALUES
(1, 'Rita', 'Nunes', '1978-05-19', 'rita.nunes.lx@gmail.com', '931683393', 'olá', '2025-07-13 14:48:49', 0, NULL, NULL),
(2, 'Rita', 'Nunes', '1978-05-19', 'rita.nunes.lx@gmail.com', '931683393', 'ola', '2025-07-13 14:49:32', 0, NULL, NULL),
(3, 'Rita', 'Nunes', '1978-05-19', 'rita.nunes.lx@gmail.com', '931683393', 'oi', '2025-07-13 14:50:40', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura da tabela `orders`
--

CREATE TABLE `orders` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dados da tabela `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `created_at`) VALUES
(2, 1, '2025-07-13 15:56:54');

-- --------------------------------------------------------

--
-- Estrutura da tabela `order_items`
--

CREATE TABLE `order_items` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dados da tabela `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`) VALUES
(1, 2, 17, 1);

-- --------------------------------------------------------

--
-- Estrutura da tabela `purchases`
--

CREATE TABLE `purchases` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `event_id` int NOT NULL,
  `quantidade` int NOT NULL,
  `comprado_em` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `data_registo` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `user_access_log`
--

CREATE TABLE `user_access_log` (
  `id` int NOT NULL,
  `email` varchar(255) NOT NULL,
  `access_time` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dados da tabela `user_access_log`
--

INSERT INTO `user_access_log` (`id`, `email`, `access_time`) VALUES
(1, 'admin@admin.com', '2025-07-13 13:10:12'),
(2, 'admin@admin.com', '2025-07-13 13:10:13'),
(3, 'admin@admin.com', '2025-07-13 13:11:43'),
(4, 'admin@admin.com', '2025-07-13 13:13:10'),
(5, 'admin@admin.com', '2025-07-13 13:13:15'),
(6, 'admin@admin.com', '2025-07-13 13:13:28'),
(7, 'admin@admin.com', '2025-07-13 13:22:45'),
(8, 'admin@admin.com', '2025-07-13 13:23:04'),
(9, 'admin@admin.com', '2025-07-13 13:23:39'),
(10, 'admin@admin.com', '2025-07-13 13:23:44'),
(11, 'admin@admin.com', '2025-07-13 13:29:56'),
(12, 'admin@admin.com', '2025-07-13 13:35:58'),
(13, 'admin@admin.com', '2025-07-13 13:55:23'),
(14, 'admin@admin.com', '2025-07-13 13:55:35'),
(15, 'admin@admin.com', '2025-07-13 14:41:48'),
(16, 'admin@admin.com', '2025-07-13 14:41:53'),
(17, 'admin@admin.com', '2025-07-13 14:42:52'),
(18, 'admin@admin.com', '2025-07-13 14:43:40'),
(19, 'admin@admin.com', '2025-07-13 14:43:47'),
(20, 'admin@admin.com', '2025-07-13 14:44:20'),
(21, 'admin@admin.com', '2025-07-13 14:49:42'),
(22, 'admin@admin.com', '2025-07-13 14:59:03'),
(23, 'admin@admin.com', '2025-07-13 17:40:40'),
(24, 'admin@admin.com', '2025-07-13 17:40:44'),
(25, 'admin@admin.com', '2025-07-13 17:40:51'),
(26, 'admin@admin.com', '2025-07-13 17:46:57');

-- --------------------------------------------------------

--
-- Estrutura da tabela `utilizadores`
--

CREATE TABLE `utilizadores` (
  `idutilizadores` int NOT NULL,
  `email` varchar(100) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dados da tabela `utilizadores`
--

INSERT INTO `utilizadores` (`idutilizadores`, `email`, `nome`, `password`, `is_admin`) VALUES
(1, 'admin@admin.com', 'Admin', '$2y$10$lP7gm9rqbdDlp8Ya67u4seCAOspBoQ2CKgqg1ndgdt7UbXmdQ8/Y.', 1),
(2, 'rita.nunes.lx@gmail.com', 'Rita Nunes', '$2y$10$A/abd3SidyZBmXu9BkNwBeI2MDdx40HqQZUhQgJPNg47xkdV6P6Li', 0);

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `bilhetes`
--
ALTER TABLE `bilhetes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evento_id` (`evento_id`);

--
-- Índices para tabela `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Índices para tabela `config`
--
ALTER TABLE `config`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `chave` (`chave`);

--
-- Índices para tabela `eventos`
--
ALTER TABLE `eventos`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `mensagens`
--
ALTER TABLE `mensagens`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Índices para tabela `purchases`
--
ALTER TABLE `purchases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Índices para tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Índices para tabela `user_access_log`
--
ALTER TABLE `user_access_log`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `utilizadores`
--
ALTER TABLE `utilizadores`
  ADD PRIMARY KEY (`idutilizadores`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `bilhetes`
--
ALTER TABLE `bilhetes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `config`
--
ALTER TABLE `config`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `eventos`
--
ALTER TABLE `eventos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT de tabela `events`
--
ALTER TABLE `events`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `mensagens`
--
ALTER TABLE `mensagens`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `purchases`
--
ALTER TABLE `purchases`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `user_access_log`
--
ALTER TABLE `user_access_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de tabela `utilizadores`
--
ALTER TABLE `utilizadores`
  MODIFY `idutilizadores` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`);

--
-- Limitadores para a tabela `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `eventos` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `purchases`
--
ALTER TABLE `purchases`
  ADD CONSTRAINT `purchases_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `purchases_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

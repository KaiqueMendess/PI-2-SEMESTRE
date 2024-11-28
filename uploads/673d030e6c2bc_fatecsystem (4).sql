-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 19/11/2024 às 00:20
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `fatecsystem`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `comentarios`
--

CREATE TABLE `comentarios` (
  `id` int(11) NOT NULL,
  `justificativa_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `comentario` text NOT NULL,
  `data_hora` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `comentarios`
--

INSERT INTO `comentarios` (`id`, `justificativa_id`, `usuario_id`, `comentario`, `data_hora`) VALUES
(1, 12, 1, 'não gostei', '2024-11-04 03:57:08');

-- --------------------------------------------------------

--
-- Estrutura para tabela `justificativas`
--

CREATE TABLE `justificativas` (
  `id` int(11) NOT NULL,
  `data_falta` date NOT NULL,
  `tipo_justificativa` varchar(100) DEFAULT NULL,
  `motivo` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'em análise',
  `data_envio` timestamp NOT NULL DEFAULT current_timestamp(),
  `motivo_rejeicao` text DEFAULT NULL,
  `comprovante` varchar(255) DEFAULT NULL,
  `usuario_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `justificativas`
--

INSERT INTO `justificativas` (`id`, `data_falta`, `tipo_justificativa`, `motivo`, `status`, `data_envio`, `motivo_rejeicao`, `comprovante`, `usuario_id`) VALUES
(12, '2024-10-31', 'dds', 'Doente', 'aprovado', '2024-11-04 03:53:29', NULL, NULL, 2);

-- --------------------------------------------------------

--
-- Estrutura para tabela `logs`
--

CREATE TABLE `logs` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `acao` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `data_hora` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `professores`
--

CREATE TABLE `professores` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `matricula` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `reposicoes`
--

CREATE TABLE `reposicoes` (
  `id` int(11) NOT NULL,
  `data_reposicao` date NOT NULL,
  `horario_inicio` time DEFAULT NULL,
  `horario_termino` time DEFAULT NULL,
  `disciplina` varchar(100) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'em análise',
  `data_envio` timestamp NOT NULL DEFAULT current_timestamp(),
  `motivo_rejeicao` text DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `descricao` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `reposicoes`
--

INSERT INTO `reposicoes` (`id`, `data_reposicao`, `horario_inicio`, `horario_termino`, `disciplina`, `status`, `data_envio`, `motivo_rejeicao`, `usuario_id`, `descricao`) VALUES
(8, '2024-11-06', '20:00:00', '23:00:00', 'DSM', 'em análise', '2024-11-06 00:23:05', NULL, 2, 'Covid 19');

-- --------------------------------------------------------

--
-- Estrutura para tabela `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `perfil` enum('professor','coordenador') NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `token_redefinicao` varchar(255) DEFAULT NULL,
  `token_expira` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `users`
--

INSERT INTO `users` (`id`, `nome`, `email`, `senha`, `perfil`, `criado_em`, `token_redefinicao`, `token_expira`) VALUES
(1, 'kaique', 'kaiquemendesn10@gmail.com', '$2y$10$IMyna1zUnIi4pGl3.1tpLuSgAF5LnVFQ6zo3iGCIXykbb2F4LnlZ.', 'coordenador', '2024-11-04 03:10:38', '460eec6f053c79e842c50c6e5df499ee65c7eeb981991b8e585d090e953448de46a9d9f846454dd069297dd8c2479f048ec6', '2024-11-19 04:06:22'),
(2, 'junior', 'junior@gmail.com', '$2y$10$4CzWRnYmkAWLJoeM6h89zeAE87Az3j8XG4A/WynEUuzHa.elJ21Z6', 'professor', '2024-11-04 03:11:44', NULL, NULL),
(3, 'Kaique Da Silva Mendes', 'kaiquemendesn11@gmail.com', '$2y$10$KUlY7B9yDOQ.s.NXUvOlsebdkUnQnRttvYVi7zf8od7r6Tu.2sGjS', 'professor', '2024-11-18 23:09:34', NULL, NULL);

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `comentarios`
--
ALTER TABLE `comentarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `justificativa_id` (`justificativa_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `justificativas`
--
ALTER TABLE `justificativas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_usuario` (`usuario_id`);

--
-- Índices de tabela `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `professores`
--
ALTER TABLE `professores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `matricula` (`matricula`);

--
-- Índices de tabela `reposicoes`
--
ALTER TABLE `reposicoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reposicoes_ibfk_usuario_id` (`usuario_id`);

--
-- Índices de tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `comentarios`
--
ALTER TABLE `comentarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `justificativas`
--
ALTER TABLE `justificativas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `professores`
--
ALTER TABLE `professores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `reposicoes`
--
ALTER TABLE `reposicoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `comentarios`
--
ALTER TABLE `comentarios`
  ADD CONSTRAINT `comentarios_ibfk_1` FOREIGN KEY (`justificativa_id`) REFERENCES `justificativas` (`id`),
  ADD CONSTRAINT `comentarios_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`);

--
-- Restrições para tabelas `justificativas`
--
ALTER TABLE `justificativas`
  ADD CONSTRAINT `fk_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`);

--
-- Restrições para tabelas `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`);

--
-- Restrições para tabelas `reposicoes`
--
ALTER TABLE `reposicoes`
  ADD CONSTRAINT `reposicoes_ibfk_usuario_id` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 06/11/2024 às 02:00
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
(8, '2024-11-06', '20:00:00', '23:00:00', 'DSM', 'em análise', '2024-11-06 00:23:05', NULL, 2, 'teste');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `reposicoes`
--
ALTER TABLE `reposicoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reposicoes_ibfk_usuario_id` (`usuario_id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `reposicoes`
--
ALTER TABLE `reposicoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `reposicoes`
--
ALTER TABLE `reposicoes`
  ADD CONSTRAINT `reposicoes_ibfk_usuario_id` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

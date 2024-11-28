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
(12, '2024-10-31', 'dds', 'Doente', 'aprovado', '2024-11-04 03:53:29', NULL, 'C:/xampp/htdocs/Sistemfatec/uploads/672845394cff1-1.0 - Formulario JUSTIFICATIVA DE FALTAS.pdf', 2);

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `justificativas`
--
ALTER TABLE `justificativas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_usuario` (`usuario_id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `justificativas`
--
ALTER TABLE `justificativas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `justificativas`
--
ALTER TABLE `justificativas`
  ADD CONSTRAINT `fk_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

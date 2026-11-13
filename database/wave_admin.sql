-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 13-Nov-2026 às 22:50
-- Versão do servidor: 10.11.16-MariaDB
-- versão do PHP: 8.4.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de dados: `wave_admin`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `administradores`
--

CREATE TABLE `administradores` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `mfa_habilitado` tinyint(1) DEFAULT 0,
  `segredo_totp` varchar(255) DEFAULT NULL,
  `criado_em` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `administradores`
--

INSERT INTO `administradores` (`id`, `nome`, `email`, `senha`, `ativo`, `mfa_habilitado`, `segredo_totp`, `criado_em`) VALUES
(1, 'Administrador Wave', 'admin@wave.com', '$2y$12$0wl81drOOmMF8sJwRZbrkeDGpsAPiBtbOkrm8YpsIPlDJyXz4Djt6', 1, 1, 'BMK3D2C4GCG6YV5YIMF4TRNLTPYLGTUK', '2026-11-13 17:29:25');

-- --------------------------------------------------------

--
-- Estrutura da tabela `albuns`
--

CREATE TABLE `albuns` (
  `id` int(11) NOT NULL,
  `artista_id` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `data_lancamento` date DEFAULT NULL,
  `capa` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `albuns`
--

INSERT INTO `albuns` (`id`, `artista_id`, `titulo`, `data_lancamento`, `capa`) VALUES
(1, 2, 'WHEN WE ALL FALL ASLEEP, WHERE DO WE GO?', '2019-03-29', 'uploads/albuns/d44442bf7060f52bef72af817b2262d2.jpg'),
(4, 3, 'Cry Baby', '2015-08-14', NULL),
(5, 4, 'Marília Mendonça: Ao Vivo', '2016-03-04', NULL),
(6, 5, 'Boiadeira Internacional (Ao Vivo)', '2024-02-23', NULL),
(7, 6, 'Xou da Xuxa 3', '1988-06-30', NULL);

-- --------------------------------------------------------

--
-- Estrutura da tabela `artistas`
--

CREATE TABLE `artistas` (
  `id` int(11) NOT NULL,
  `nome_artistico` varchar(150) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `biografia` text DEFAULT NULL,
  `status` enum('ativo','pendente','bloqueado') DEFAULT 'pendente',
  `criado_em` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `artistas`
--

INSERT INTO `artistas` (`id`, `nome_artistico`, `email`, `biografia`, `status`, `criado_em`) VALUES
(1, 'Artista Teste', 'artista@wave.com', 'Artista para testes do sistema.', 'ativo', '2026-11-13 18:05:44'),
(2, 'Billie Eillish', 'billie@gmail.com', 'Billie Eilish Pirate Baird O\'Connell é uma cantora e compositora estadunidense. Ganhou popularidade em 2016, quando lançou o single de estreia \"Ocean Eyes\" no SoundCloud, posteriormente lançado pelas gravadoras Darkroom e Interscope Records.', 'ativo', '2026-11-13 19:01:17'),
(3, 'Melanie Martinez', 'melanie@waveadmin.local', 'Cantora e compositora norte-americana de pop alternativo.', 'ativo', '2026-11-13 21:45:45'),
(4, 'Marília Mendonça', 'marilia@waveadmin.local', 'Cantora e compositora brasileira, conhecida por seus sucessos no sertanejo.', 'ativo', '2026-11-13 21:45:45'),
(5, 'Ana Castela', 'anacastela@waveadmin.local', 'Cantora brasileira conhecida como Boiadeira, destaque do sertanejo.', 'ativo', '2026-11-13 21:45:45'),
(6, 'Xuxa', 'xuxa@waveadmin.local', 'Apresentadora e cantora brasileira conhecida por suas músicas infantis.', 'ativo', '2026-11-13 21:45:45');

-- --------------------------------------------------------

--
-- Estrutura da tabela `generos`
--

CREATE TABLE `generos` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `generos`
--

INSERT INTO `generos` (`id`, `nome`) VALUES
(3, 'Indie'),
(6, 'Música infantil'),
(1, 'Pop'),
(5, 'Pop alternativo'),
(2, 'Rock'),
(4, 'Sertanejo');

-- --------------------------------------------------------

--
-- Estrutura da tabela `logs`
--

CREATE TABLE `logs` (
  `id` int(11) NOT NULL,
  `administrador_id` int(11) DEFAULT NULL,
  `acao` varchar(150) NOT NULL,
  `descricao` text DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `criado_em` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `logs`
--

INSERT INTO `logs` (`id`, `administrador_id`, `acao`, `descricao`, `ip`, `criado_em`) VALUES
(1, 1, 'Cadastro', 'Usuário cadastrado: giulia@gmail.com', '::1', '2026-11-13 18:18:21'),
(2, 1, 'Cadastro', 'Usuário cadastrado: bruno@gmail.com', '::1', '2026-11-13 18:18:45'),
(3, 1, 'Cadastro', 'Usuário cadastrado: joao@gmail.com', '::1', '2026-11-13 18:19:11'),
(4, 1, 'Cadastro', 'Usuário cadastrado: kleber@gmail.com', '::1', '2026-11-13 18:19:38'),
(5, 1, 'Edição', 'Usuário atualizado: joao@gmail.com', '::1', '2026-11-13 18:19:45'),
(6, 1, 'Alteração de status', 'Usuário ID 3: bloqueado', '::1', '2026-11-13 18:19:47'),
(7, 1, 'Alteração de status', 'Usuário ID 3: ativo', '::1', '2026-11-13 18:19:50'),
(8, 1, 'Cadastro', 'Usuário cadastrado: matheus@gmail.com', '::1', '2026-11-13 18:21:01'),
(9, 1, 'Exclusão', 'Usuário excluído: matheus@gmail.com', '::1', '2026-11-13 18:21:43'),
(10, 1, 'Edição', 'Usuário atualizado: joao@gmail.com', '::1', '2026-11-13 18:21:47'),
(11, 1, 'Cadastro', 'Artista cadastrado: Billie Eillish', '::1', '2026-11-13 19:01:17'),
(12, 1, 'Alteração de status', 'Artista ID 2: pendente para ativo', '::1', '2026-11-13 19:07:28'),
(13, 1, 'Edição', 'Artista atualizado: Billie Eillish', '::1', '2026-11-13 19:07:43'),
(14, 1, 'Cadastro de música', 'Música cadastrada: Bad Guy', '::1', '2026-11-13 19:22:24'),
(15, 1, 'Edição de música', 'Música ID 6 editada: Bad Guy', '::1', '2026-11-13 19:25:27'),
(16, 1, 'Exclusão de música', 'Música excluída: Bad Guy (ID: 6)', '::1', '2026-11-13 19:27:13'),
(17, 1, 'Cadastro de música', 'Música cadastrada: Bad Guy', '::1', '2026-11-13 19:27:54'),
(18, 1, 'Cadastro de álbum', 'Álbum cadastrado: WHEN WE ALL FALL ASLEEP, WHERE DO WE GO?', '::1', '2026-11-13 19:40:06'),
(19, 1, 'Edição de música', 'Música ID 7 editada: Bad Guy', '::1', '2026-11-13 19:40:28'),
(20, 1, 'Edição de música', 'Música ID 7 editada: Bad Guy', '::1', '2026-11-13 19:40:40'),
(21, 1, 'Edição de música', 'Música ID 7 editada: Bad Guy', '::1', '2026-11-13 19:40:50'),
(22, 1, 'Edição de álbum', 'Álbum ID 1 atualizado: WHEN WE ALL FALL ASLEEP, WHERE DO WE GO?', '::1', '2026-11-13 19:45:33'),
(23, 1, 'Cadastro de álbum', 'Álbum cadastrado: abacadra', '::1', '2026-11-13 20:25:25'),
(24, 1, 'Exclusão de álbum', 'Álbum excluído: abacadra', '::1', '2026-11-13 20:25:30'),
(25, 1, 'Cadastro de álbum', 'Álbum cadastrado: abcd', '::1', '2026-11-13 20:30:50'),
(26, 1, 'Exclusão de álbum', 'Álbum excluído: abcd', '::1', '2026-11-13 20:30:54'),
(27, 1, 'Cadastro de gênero', 'Gênero cadastrado: Rock', '::1', '2026-11-13 20:41:16'),
(28, 1, 'Cadastro de gênero', 'Gênero cadastrado: Indie', '::1', '2026-11-13 20:41:23'),
(29, 1, 'Cadastro de gênero', 'Gênero cadastrado: Sertanejo', '::1', '2026-11-13 20:41:35'),
(30, 1, 'Edição de música', 'Música ID 7 editada: Bad Guy', '::1', '2026-11-13 20:42:05'),
(31, 1, 'Ativação de MFA', 'Autenticação em duas etapas ativada.', '::1', '2026-11-13 21:22:06');

-- --------------------------------------------------------

--
-- Estrutura da tabela `musicas`
--

CREATE TABLE `musicas` (
  `id` int(11) NOT NULL,
  `artista_id` int(11) NOT NULL,
  `album_id` int(11) DEFAULT NULL,
  `genero_id` int(11) DEFAULT NULL,
  `titulo` varchar(150) NOT NULL,
  `duracao` time DEFAULT NULL,
  `reproducoes` bigint(20) DEFAULT 0,
  `status` enum('publicada','pendente','removida') DEFAULT 'pendente',
  `criada_em` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `musicas`
--

INSERT INTO `musicas` (`id`, `artista_id`, `album_id`, `genero_id`, `titulo`, `duracao`, `reproducoes`, `status`, `criada_em`) VALUES
(1, 1, NULL, 1, 'Noite Neon', '00:02:10', 1500, 'publicada', '2026-11-13 18:05:44'),
(2, 1, NULL, 1, 'Além do Som', '00:00:00', 2300, 'publicada', '2026-11-13 18:05:44'),
(3, 1, NULL, 1, 'Wave de Verão', '00:02:00', 900, 'publicada', '2026-11-13 18:05:44'),
(4, 1, NULL, 1, 'Luzes da Cidade', '00:00:00', 3100, 'publicada', '2026-11-13 18:05:44'),
(5, 1, NULL, 1, 'Universo Musical', '00:02:20', 1800, 'publicada', '2026-11-13 18:05:44'),
(7, 2, 1, 3, 'Bad Guy', '00:03:15', 0, 'publicada', '2026-11-13 19:27:54'),
(8, 3, 4, 5, 'Cry Baby', '00:03:59', 1580000, 'publicada', '2026-11-13 21:45:45'),
(9, 3, 4, 5, 'Dollhouse', '00:03:51', 2450000, 'publicada', '2026-11-13 21:45:45'),
(10, 3, 4, 5, 'Sippy Cup', '00:03:15', 1320000, 'publicada', '2026-11-13 21:45:45'),
(11, 3, 4, 5, 'Carousel', '00:03:50', 980000, 'publicada', '2026-11-13 21:45:45'),
(12, 3, 4, 5, 'Pity Party', '00:03:24', 2100000, 'publicada', '2026-11-13 21:45:45'),
(13, 4, 5, 4, 'Infiel', '00:03:21', 5800000, 'publicada', '2026-11-13 21:45:45'),
(14, 4, 5, 4, 'Como Faz com Ela', '00:02:30', 3200000, 'publicada', '2026-11-13 21:45:45'),
(15, 4, 5, 4, 'Hoje Somos Só Metade', '00:02:49', 1850000, 'publicada', '2026-11-13 21:45:45'),
(16, 4, 5, 4, 'O Que É Amor Pra Você', '00:03:04', 2600000, 'publicada', '2026-11-13 21:45:45'),
(17, 4, 5, 4, 'Alô Porteiro', '00:03:16', 4200000, 'publicada', '2026-11-13 21:45:45'),
(18, 5, 6, 4, 'Boiadeira', '00:04:28', 3500000, 'publicada', '2026-11-13 21:45:46'),
(19, 5, 6, 4, 'Bonde das Boiadeiras', '00:02:31', 1800000, 'publicada', '2026-11-13 21:45:46'),
(20, 5, 6, 4, 'Coração Bipolar', '00:02:36', 2900000, 'publicada', '2026-11-13 21:45:46'),
(21, 5, 6, 4, 'Nosso Quadro', '00:02:55', 8500000, 'publicada', '2026-11-13 21:45:46'),
(22, 5, 6, 4, 'Solteiro Forçado', '00:03:14', 6200000, 'publicada', '2026-11-13 21:45:46'),
(23, 6, 7, 6, 'Ilariê', '00:05:41', 7200000, 'publicada', '2026-11-13 21:45:46'),
(24, 6, 7, 6, 'Bombom', '00:04:23', 2800000, 'publicada', '2026-11-13 21:45:46'),
(25, 6, 7, 6, 'O Praga é Uma Praga', '00:03:40', 950000, 'publicada', '2026-11-13 21:45:46'),
(26, 6, 7, 6, 'Xuxerife', '00:02:55', 780000, 'publicada', '2026-11-13 21:45:46'),
(27, 6, 7, 6, 'Beijinhos Estalados', '00:05:03', 1100000, 'publicada', '2026-11-13 21:45:46');

-- --------------------------------------------------------

--
-- Estrutura da tabela `tokens_mfa`
--

CREATE TABLE `tokens_mfa` (
  `id` int(11) NOT NULL,
  `administrador_id` int(11) NOT NULL,
  `codigo` varchar(255) NOT NULL,
  `expira_em` datetime NOT NULL,
  `utilizado` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `status` enum('ativo','bloqueado') DEFAULT 'ativo',
  `criado_em` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `status`, `criado_em`) VALUES
(1, 'Giulia Gomez', 'giulia@gmail.com', 'ativo', '2026-11-13 18:18:21'),
(2, 'Bruno Passos', 'bruno@gmail.com', 'ativo', '2026-11-13 18:18:45'),
(3, 'João Lamin', 'joao@gmail.com', 'ativo', '2026-11-13 18:19:11'),
(4, 'Professor Kleber', 'kleber@gmail.com', 'ativo', '2026-11-13 18:19:38');

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `administradores`
--
ALTER TABLE `administradores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Índices para tabela `albuns`
--
ALTER TABLE `albuns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `artista_id` (`artista_id`);

--
-- Índices para tabela `artistas`
--
ALTER TABLE `artistas`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `generos`
--
ALTER TABLE `generos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nome` (`nome`);

--
-- Índices para tabela `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `administrador_id` (`administrador_id`);

--
-- Índices para tabela `musicas`
--
ALTER TABLE `musicas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `artista_id` (`artista_id`),
  ADD KEY `album_id` (`album_id`),
  ADD KEY `genero_id` (`genero_id`);

--
-- Índices para tabela `tokens_mfa`
--
ALTER TABLE `tokens_mfa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `administrador_id` (`administrador_id`);

--
-- Índices para tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `administradores`
--
ALTER TABLE `administradores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `albuns`
--
ALTER TABLE `albuns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `artistas`
--
ALTER TABLE `artistas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `generos`
--
ALTER TABLE `generos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT de tabela `musicas`
--
ALTER TABLE `musicas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de tabela `tokens_mfa`
--
ALTER TABLE `tokens_mfa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `albuns`
--
ALTER TABLE `albuns`
  ADD CONSTRAINT `albuns_ibfk_1` FOREIGN KEY (`artista_id`) REFERENCES `artistas` (`id`);

--
-- Limitadores para a tabela `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`administrador_id`) REFERENCES `administradores` (`id`) ON DELETE SET NULL;

--
-- Limitadores para a tabela `musicas`
--
ALTER TABLE `musicas`
  ADD CONSTRAINT `musicas_ibfk_1` FOREIGN KEY (`artista_id`) REFERENCES `artistas` (`id`),
  ADD CONSTRAINT `musicas_ibfk_2` FOREIGN KEY (`album_id`) REFERENCES `albuns` (`id`),
  ADD CONSTRAINT `musicas_ibfk_3` FOREIGN KEY (`genero_id`) REFERENCES `generos` (`id`);

--
-- Limitadores para a tabela `tokens_mfa`
--
ALTER TABLE `tokens_mfa`
  ADD CONSTRAINT `tokens_mfa_ibfk_1` FOREIGN KEY (`administrador_id`) REFERENCES `administradores` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

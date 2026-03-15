-- Criar banco de dados
CREATE DATABASE IF NOT EXISTS mineaddonsnews 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE mineaddonsnews;

-- Tabela de usuários
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    token_confirmacao VARCHAR(100) NULL,
    email_confirmado BOOLEAN DEFAULT FALSE,
    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP,
    ultimo_login DATETIME NULL,
    ultimo_ip VARCHAR(45) NULL,
    status ENUM('ativo', 'bloqueado', 'pendente') DEFAULT 'pendente',
    nivel ENUM('user', 'vip', 'admin') DEFAULT 'user',
    reset_token VARCHAR(100) NULL,
    reset_expira DATETIME NULL,
    INDEX idx_email (email),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de addons
CREATE TABLE addons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(200) NOT NULL,
    descricao TEXT,
    imagem_url VARCHAR(500),
    preco DECIMAL(5,2) DEFAULT 0.00,
    link_terabox VARCHAR(500),
    link_premium VARCHAR(500) NULL,
    tipo ENUM('gratis', 'pago') DEFAULT 'gratis',
    downloads INT DEFAULT 0,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    ativo BOOLEAN DEFAULT TRUE,
    INDEX idx_tipo (tipo),
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de compras
CREATE TABLE compras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    addon_id INT NOT NULL,
    valor_pago DECIMAL(5,2) NOT NULL,
    payment_id VARCHAR(100) NULL,
    status_pagamento ENUM('pendente', 'aprovado', 'cancelado') DEFAULT 'pendente',
    data_compra DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_aprovacao DATETIME NULL,
    ip_compra VARCHAR(45),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (addon_id) REFERENCES addons(id) ON DELETE CASCADE,
    INDEX idx_usuario (usuario_id),
    INDEX idx_status (status_pagamento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de tentativas de login (rate limiting)
CREATE TABLE login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255),
    ip VARCHAR(45),
    tentativa DATETIME DEFAULT CURRENT_TIMESTAMP,
    sucesso BOOLEAN DEFAULT FALSE,
    INDEX idx_ip (ip),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de sessões ativas
CREATE TABLE sessoes_ativas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    session_id VARCHAR(255) NOT NULL,
    ip VARCHAR(45),
    user_agent TEXT,
    data_login DATETIME DEFAULT CURRENT_TIMESTAMP,
    ultima_acao DATETIME DEFAULT CURRENT_TIMESTAMP,
    expira DATETIME,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_session (session_id),
    INDEX idx_usuario (usuario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de logs de segurança
CREATE TABLE security_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NULL,
    acao VARCHAR(100),
    ip VARCHAR(45),
    user_agent TEXT,
    detalhes TEXT,
    data_ocorrencia DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_data (data_ocorrencia)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inserir alguns addons de exemplo
INSERT INTO addons (nome, descricao, preco, link_terabox, tipo) VALUES
('Armas Medieval Plus', '15 armas medievais, texturas 4D', 2.00, 'https://terabox.com/s/1a2b3c4d5e', 'pago'),
('Novos Mobs Fantasia', '8 criaturas míticas', 2.00, 'https://terabox.com/s/f6g7h8i9j0', 'pago'),
('Addon Básico Grátis', 'Algumas ferramentas extras', 0.00, 'https://terabox.com/s/gratis1', 'gratis'),
('Textura Padrão Melhorada', 'Texturas em 4K', 0.00, 'https://terabox.com/s/gratis2', 'gratis');

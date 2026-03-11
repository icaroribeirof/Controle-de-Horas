CREATE DATABASE IF NOT EXISTS controle_horas;
USE controle_horas;

-- Tabela de usuários
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    tema_preferido ENUM('claro', 'escuro') DEFAULT 'claro',
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de atividades
CREATE TABLE atividades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    nome_atividade VARCHAR(200) NOT NULL,
    nome_cliente VARCHAR(100) NOT NULL,
    data_execucao DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fim TIME NOT NULL,
    data_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Índices para melhor performance
CREATE INDEX idx_usuario_data ON atividades(usuario_id, data_execucao);
CREATE INDEX idx_data_execucao ON atividades(data_execucao);
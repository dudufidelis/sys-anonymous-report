-- Script SQL para criar tabelas
-- Tabela usuarios
CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome_usuario VARCHAR(255) NOT NULL,
    senha_hash VARCHAR(255) NOT NULL,
    ip VARCHAR(45)
);

-- Tabela denuncias
CREATE TABLE denuncias (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(255) NULL,
    telefone VARCHAR(20) NULL,
    cpf VARCHAR(20) NULL,
    mensagem TEXT NOT NULL,
    data_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    ip VARCHAR(45)
);

-- Ajustes em produção (execute conforme necessidade):
-- Permitir nome NULL (anônimos reais)
-- ALTER TABLE denuncias MODIFY nome VARCHAR(255) NULL;
-- Se ainda não tiver as colunas telefone/cpf:
-- ALTER TABLE denuncias ADD COLUMN telefone VARCHAR(20) NULL AFTER nome;
-- ALTER TABLE denuncias ADD COLUMN cpf VARCHAR(20) NULL AFTER telefone;

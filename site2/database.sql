SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS db_tcc;
USE db_tcc;

-- =====================================================
-- Tabela: USUARIOS
-- Armazena informações dos usuários (alunos, professores, admin)
-- Roles: aluno, professor, admin
-- =====================================================
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    telefone VARCHAR(20),
    role ENUM('aluno', 'professor', 'admin') DEFAULT 'aluno',
    aprovado TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_aprovado (aprovado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Adicionar constraint único apenas se não existir
ALTER TABLE usuarios ADD CONSTRAINT unique_telefone_role 
UNIQUE (telefone, role);

-- OU se a tabela já tem dados e quer fazer backup:
-- ALTER TABLE usuarios DROP INDEX IF EXISTS unique_telefone_role;
-- ALTER TABLE usuarios ADD UNIQUE INDEX unique_telefone_role (telefone, role);

-- =====================================================
-- Tabela: LINKS_EMAILS
-- Armazena tokens de confirmação de email para registro
-- Tokens único, gerado via uniqid() no PHP
-- =====================================================
CREATE TABLE IF NOT EXISTS links_emails (
    id INT AUTO_INCREMENT PRIMARY KEY,
    link VARCHAR(64) NOT NULL UNIQUE,
    usuario_id INT NOT NULL,
    situacao TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_links_emails_usuario FOREIGN KEY (usuario_id) 
        REFERENCES usuarios(id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_link (link),
    INDEX idx_usuario_id (usuario_id),
    INDEX idx_situacao (situacao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Tabela: POSTAGENS
-- Armazena postagens dos professores (questões ou arquivos)
-- Tipos: questao (discursiva/objetiva), arquivo
-- alternativas_json: {"alternativas": [...], "resposta_correta": index}
-- =====================================================
CREATE TABLE IF NOT EXISTS postagens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    professor_id INT NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    tipo_postagem ENUM('questao', 'arquivo') DEFAULT 'questao',
    tipo_questao ENUM('discursiva', 'objetiva') DEFAULT 'discursiva',
    conteudo LONGTEXT,
    arquivo_path VARCHAR(500),
    enunciado LONGTEXT,
    alternativas_json LONGTEXT,
    resposta_correta VARCHAR(500),
    obrigatoria TINYINT(1) DEFAULT 0,
    materias VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_postagens_professor FOREIGN KEY (professor_id) 
        REFERENCES usuarios(id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_professor_id (professor_id),
    INDEX idx_created_at (created_at),
    INDEX idx_tipo_postagem (tipo_postagem),
    INDEX idx_tipo_questao (tipo_questao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Tabela: RESPOSTAS_ALUNOS
-- Armazena as respostas dos alunos às questões
-- acertou: 1 = resposta correta, 0 = resposta incorreta
-- =====================================================
CREATE TABLE IF NOT EXISTS respostas_alunos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aluno_id INT NOT NULL,
    postagem_id INT NOT NULL,
    resposta_escolhida VARCHAR(500) NOT NULL,
    acertou TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_respostas_aluno FOREIGN KEY (aluno_id) 
        REFERENCES usuarios(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_respostas_postagem FOREIGN KEY (postagem_id) 
        REFERENCES postagens(id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_aluno_id (aluno_id),
    INDEX idx_postagem_id (postagem_id),
    INDEX idx_created_at (created_at),
    UNIQUE KEY unique_aluno_postagem (aluno_id, postagem_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Tabela: COMENTARIOS
-- Armazena comentários nas postagens
-- Permite discussão e feedback entre alunos e professores
-- =====================================================
CREATE TABLE IF NOT EXISTS comentarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    postagem_id INT NOT NULL,
    usuario_id INT NOT NULL,
    texto LONGTEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_comentarios_postagem FOREIGN KEY (postagem_id) 
        REFERENCES postagens(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_comentarios_usuario FOREIGN KEY (usuario_id) 
        REFERENCES usuarios(id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_postagem_id (postagem_id),
    INDEX idx_usuario_id (usuario_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;


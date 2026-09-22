-- ================================================================
-- SCRIPT SQL - SISTEMA DE CURSOS E INSCRIÇÕES
-- Projeto: Humaniza RR - Campanha de Olho nos Olhinhos
-- ================================================================

-- Tabela de Cursos/Palestras
CREATE TABLE IF NOT EXISTS cursos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    duracao VARCHAR(50) NOT NULL,
    local VARCHAR(255) NOT NULL,
    palestrante VARCHAR(255) NOT NULL,
    presidente VARCHAR(255) NOT NULL,
    vice_presidente VARCHAR(255) NOT NULL,
    data_evento DATETIME NOT NULL,
    status ENUM('Aberto', 'Rascunho', 'Encerrado') DEFAULT 'Rascunho',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Inscritos (pessoas físicas)
CREATE TABLE IF NOT EXISTS inscritos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    telefone VARCHAR(20),
    tipo ENUM('Acadêmico', 'Profissional de Saúde') NOT NULL,
    instituicao VARCHAR(255),
    semestre_atuacao VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_tipo (tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Inscrições (vínculo entre cursos e inscritos)
CREATE TABLE IF NOT EXISTS inscricoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_curso INT NOT NULL,
    id_inscrito INT NOT NULL,
    data_inscricao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Confirmada', 'Cancelada') DEFAULT 'Confirmada',
    FOREIGN KEY (id_curso) REFERENCES cursos(id) ON DELETE CASCADE,
    FOREIGN KEY (id_inscrito) REFERENCES inscritos(id) ON DELETE CASCADE,
    UNIQUE KEY unique_inscricao (id_curso, id_inscrito),
    INDEX idx_curso (id_curso),
    INDEX idx_data (data_inscricao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- DADOS INICIAIS - 58 REGISTROS ÚNICOS DE INSCRITOS
-- ================================================================

-- Nota: Estes são dados de exemplo. Substitua pelos dados reais da campanha.

INSERT INTO inscritos (nome, email, telefone, tipo, instituicao, semestre_atuacao) VALUES
('Ana Silva Santos', 'ana.silva@email.com', '(95) 98765-4321', 'Acadêmico', 'Universidade Federal de Roraima', '5º semestre'),
('Carlos Oliveira Mendes', 'carlos.mendes@email.com', '(95) 99123-4567', 'Profissional de Saúde', 'Hospital Geral de Roraima', 'Cardiologia'),
('Maria Fernanda Costa', 'maria.costa@email.com', '(95) 98234-5678', 'Acadêmico', 'Faculdade Estácio de Roraima', '3º semestre'),
('João Pedro Almeida', 'joao.almeida@email.com', '(95) 99345-6789', 'Profissional de Saúde', 'UPA Zona Norte', 'Emergência'),
('Fernanda Lima Rocha', 'fernanda.lima@email.com', '(95) 98456-7890', 'Acadêmico', 'Universidade Federal de Roraima', '7º semestre'),
('Ricardo Souza Pereira', 'ricardo.souza@email.com', '(95) 99567-8901', 'Profissional de Saúde', 'Clínica São Francisco', 'Ortopedia'),
('Juliana Martins Barbosa', 'juliana.martins@email.com', '(95) 98678-9012', 'Acadêmico', 'Faculdade de Medicina de Boa Vista', '4º semestre'),
('Marcos Vinicius Duarte', 'marcos.duarte@email.com', '(95) 99789-0123', 'Profissional de Saúde', 'Hospital Américo Brasiliense', 'UTI'),
('Patricia Araújo Nunes', 'patricia.araujo@email.com', '(95) 98890-1234', 'Acadêmico', 'Universidade Federal de Roraima', '6º semestre'),
('Roberto Carlos Ferreira', 'roberto.ferreira@email.com', '(95) 99901-2345', 'Profissional de Saúde', 'Secretaria Municipal de Saúde', 'Saúde Pública'),
('Camila Rodrigues Gomes', 'camila.gomes@email.com', '(95) 98012-3456', 'Acadêmico', 'Faculdade Estácio de Roraima', '2º semestre'),
('Lucas Henrique Dias', 'lucas.dias@email.com', '(95) 99123-4568', 'Profissional de Saúde', 'Hospital Geral de Roraima', 'Neurologia'),
('Beatriz Carvalho Moreira', 'beatriz.carvalho@email.com', '(95) 98234-5679', 'Acadêmico', 'Universidade Federal de Roraima', '8º semestre'),
('Felipe Augusto Ramos', 'felipe.ramos@email.com', '(95) 99345-6780', 'Profissional de Saúde', 'Clínica Oftalmológica Visão', 'Oftalmologia'),
('Gabriela Pinto Castro', 'gabriela.castro@email.com', '(95) 98456-7891', 'Acadêmico', 'Faculdade de Medicina de Boa Vista', '5º semestre'),
('Rodrigo Tavares Lopes', 'rodrigo.lopes@email.com', '(95) 99567-8902', 'Profissional de Saúde', 'UPA Zona Sul', 'Pediatria'),
('Amanda Cristina Moura', 'amanda.moura@email.com', '(95) 98678-9013', 'Acadêmico', 'Universidade Federal de Roraima', '4º semestre'),
('Bruno Henrique Freitas', 'bruno.freitas@email.com', '(95) 99789-0124', 'Profissional de Saúde', 'Hospital Américo Brasiliense', 'Ginecologia'),
('Larissa Vitória Azevedo', 'larissa.azevedo@email.com', '(95) 98890-1235', 'Acadêmico', 'Faculdade Estácio de Roraima', '6º semestre'),
('Thiago Nascimento Correia', 'thiago.correia@email.com', '(95) 99901-2346', 'Profissional de Saúde', 'Clínica Cardiológica Coração', 'Cardiologia'),
('Vanessa Regina Teixeira', 'vanessa.teixeira@email.com', '(95) 98012-3457', 'Acadêmico', 'Universidade Federal de Roraima', '3º semestre'),
('André Luiz Monteiro', 'andre.monteiro@email.com', '(95) 99123-4569', 'Profissional de Saúde', 'Secretaria Estadual de Saúde', 'Epidemiologia'),
('Priscila Cunha Cardoso', 'priscila.cardoso@email.com', '(95) 98234-5670', 'Acadêmico', 'Faculdade de Medicina de Boa Vista', '7º semestre'),
('Gustavo Henrique Borges', 'gustavo.borges@email.com', '(95) 99345-6781', 'Profissional de Saúde', 'Hospital Geral de Roraima', 'Oncologia'),
('Raquel Santana Medeiros', 'raquel.medeiros@email.com', '(95) 98456-7892', 'Acadêmico', 'Universidade Federal de Roraima', '5º semestre'),
('Leonardo Fernandes Cruz', 'leonardo.cruz@email.com', '(95) 99567-8903', 'Profissional de Saúde', 'Clínica de Fisioterapia Vida', 'Fisioterapia'),
('Tatiane Coelho Andrade', 'tatiane.andrade@email.com', '(95) 98678-9014', 'Acadêmico', 'Faculdade Estácio de Roraima', '4º semestre'),
('Daniel Soares Xavier', 'daniel.xavier@email.com', '(95) 99789-0125', 'Profissional de Saúde', 'UPA Zona Oeste', 'Clínica Médica'),
('Mariana Batista Rezende', 'mariana.rezende@email.com', '(95) 98890-1236', 'Acadêmico', 'Universidade Federal de Roraima', '6º semestre'),
('Pedro Henrique Morais', 'pedro.morais@email.com', '(95) 99901-2347', 'Profissional de Saúde', 'Hospital Américo Brasiliense', 'Ortopedia'),
('Cristiane Aparecida Viana', 'cristiane.viana@email.com', '(95) 98012-3458', 'Acadêmico', 'Faculdade de Medicina de Boa Vista', '3º semestre'),
('Alexandre Pires Guimarães', 'alexandre.guimaraes@email.com', '(95) 99123-4560', 'Profissional de Saúde', 'Clínica Dermatológica Pele', 'Dermatologia'),
('Elaine Cristina Farias', 'elaine.farias@email.com', '(95) 98234-5671', 'Acadêmico', 'Universidade Federal de Roraima', '7º semestre'),
('Marcelo José Nogueira', 'marcelo.nogueira@email.com', '(95) 99345-6782', 'Profissional de Saúde', 'Secretaria Municipal de Saúde', 'Vigilância Sanitária'),
('Simone Aparecida Reis', 'simone.reis@email.com', '(95) 98456-7893', 'Acadêmico', 'Faculdade Estácio de Roraima', '5º semestre'),
('Fábio Roberto Assis', 'fabio.assis@email.com', '(95) 99567-8904', 'Profissional de Saúde', 'Hospital Geral de Roraima', 'Radiologia'),
('Luciana Mara Fonseca', 'luciana.fonseca@email.com', '(95) 98678-9015', 'Acadêmico', 'Universidade Federal de Roraima', '4º semestre'),
('Renato César Machado', 'renato.machado@email.com', '(95) 99789-0126', 'Profissional de Saúde', 'Clínica Psicológica Mente', 'Psicologia'),
('Adriana Lúcia Campos', 'adriana.campos@email.com', '(95) 98890-1237', 'Acadêmico', 'Faculdade de Medicina de Boa Vista', '6º semestre'),
('Sérgio Antônio Braga', 'sergio.braga@email.com', '(95) 99901-2348', 'Profissional de Saúde', 'UPA Zona Leste', 'Urgência e Emergência'),
('Carla Denise Araújo', 'carla.araujo@email.com', '(95) 98012-3459', 'Acadêmico', 'Universidade Federal de Roraima', '3º semestre'),
('Edson Luis Matos', 'edson.matos@email.com', '(95) 99123-4561', 'Profissional de Saúde', 'Hospital Américo Brasiliense', 'Nefrologia'),
('Rosângela Maria Dutra', 'rosangela.dutra@email.com', '(95) 98234-5672', 'Acadêmico', 'Faculdade Estácio de Roraima', '7º semestre'),
('Vinicius Guilherme Paiva', 'vinicius.paiva@email.com', '(95) 99345-6783', 'Profissional de Saúde', 'Clínica Endocrinológica Vida', 'Endocrinologia'),
('Monique Cristine Esteves', 'monique.esteves@email.com', '(95) 98456-7894', 'Acadêmico', 'Universidade Federal de Roraima', '5º semestre'),
('Igor William Caldas', 'igor.caldas@email.com', '(95) 99567-8905', 'Profissional de Saúde', 'Secretaria Estadual de Saúde', 'Imunização'),
('Kelly Regina Bittencourt', 'kelly.bittencourt@email.com', '(95) 98678-9016', 'Acadêmico', 'Faculdade de Medicina de Boa Vista', '4º semestre'),
('Evandro José Guedes', 'evandro.guedes@email.com', '(95) 99789-0127', 'Profissional de Saúde', 'Hospital Geral de Roraima', 'Gastroenterologia'),
('Deborah Lorraine Torres', 'deborah.torres@email.com', '(95) 98890-1238', 'Acadêmico', 'Universidade Federal de Roraima', '6º semestre'),
('Hélio Marcos Bezerra', 'helio.bezerra@email.com', '(95) 99901-2349', 'Profissional de Saúde', 'Clínica Reabilitar', 'Medicina Física'),
('Ingrid Nayara Coutinho', 'ingrid.coutinho@email.com', '(95) 98012-3450', 'Acadêmico', 'Faculdade Estácio de Roraima', '3º semestre'),
('Jéferson Luís Ramalho', 'jeferson.ramalho@email.com', '(95) 99123-4562', 'Profissional de Saúde', 'UPA Zona Norte', 'Atenção Básica'),
('Nilza Soares Jardim', 'nilza.jardim@email.com', '(95) 98234-5673', 'Acadêmico', 'Universidade Federal de Roraima', '7º semestre'),
('Otávio César Magalhães', 'otavio.magalhaes@email.com', '(95) 99345-6784', 'Profissional de Saúde', 'Hospital Américo Brasiliense', 'Pneumologia'),
('Quezia Fernandes Leal', 'quezia.leal@email.com', '(95) 98456-7895', 'Acadêmico', 'Faculdade de Medicina de Boa Vista', '5º semestre'),
('Wagner Renato Henriques', 'wagner.henriques@email.com', '(95) 99567-8906', 'Profissional de Saúde', 'Clínica Urológica Rim', 'Urologia'),
('Yasmin Giovanna Melo', 'yasmin.melo@email.com', '(95) 98678-9017', 'Acadêmico', 'Universidade Federal de Roraima', '4º semestre'),
('Zeca Felipe Trindade', 'zeca.trindade@email.com', '(95) 99789-0128', 'Profissional de Saúde', 'Secretaria Municipal de Saúde', 'Saúde da Família');

-- ================================================================
-- CURSO DE EXEMPLO (OPCIONAL)
-- ================================================================

INSERT INTO cursos (nome, descricao, duracao, local, palestrante, presidente, vice_presidente, data_evento, status) VALUES
('Campanha de Olho nos Olhinhos - Lançamento', 
 'Evento de lançamento da campanha de prevenção à cegueira evitável em Roraima. Abordagens sobre saúde ocular, prevenção e cuidados básicos.',
 '4 horas',
 'Auditório Principal - Hospital Geral de Roraima, Av. Brigadeiro Eduardo Gomes, 2005 - Boa Vista/RR',
 'Dr. Especialista em Oftalmologia',
 'Nome do Presidente',
 'Nome do Vice-Presidente',
 '2025-10-15 08:00:00',
 'Aberto');

-- ================================================================
-- FIM DO SCRIPT
-- ================================================================

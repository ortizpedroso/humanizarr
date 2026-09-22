# 📋 GUIA DE IMPLEMENTAÇÃO - SISTEMA DE CURSOS E INSCRIÇÕES

## ✅ Implementação Concluída

O sistema completo de gestão de cursos e inscrições foi implementado com sucesso no repositório Humaniza RR.

---

## 🗂️ Arquivos Criados/Modificados

### Models (Camada de Negócio)
- `models/Curso.php` - CRUD completo de cursos
- `models/Inscricao.php` - Processamento de inscrições com deduplicação

### Painel Administrativo (`public/`)
- `public/lista_cursos.php` - Listagem com estatísticas
- `public/criar_curso.php` - Formulário de criação
- `public/toggle_status.php` - Alternar status Aberto/Rascunho
- `public/excluir_curso.php` - Exclusão segura
- `public/inscricao.php` - Página pública de inscrição
- `public/processar_inscricao.php` - Processamento + e-mail
- `public/includes/sidebar.php` - Menu atualizado

---

## 🚀 PRÓXIMOS PASSOS (IMPORTANTE)

### 1. Executar Script SQL no Banco de Dados

Acesse o **phpMyAdmin** na Hostinger e execute:

```sql
-- Tabela de Cursos
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

-- Tabela de Inscritos
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

-- Tabela de Inscrições
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
```

### 2. Configurar E-mail (PHPMailer)

Edite o arquivo `public/processar_inscricao.php` e descomente/configure:

```php
// Linha ~60 a ~130
$mail->Host       = 'smtp.seudominio.com.br';     // Seu SMTP Hostinger
$mail->Username   = 'eventos@seudominio.com.br';  // E-mail criado no cPanel
$mail->Password   = 'senha_do_email';              // Senha do e-mail
```

### 3. Acessar o Sistema

1. **Painel Admin:** `https://seudominio.com/public/lista_cursos.php`
2. **Criar Primeiro Curso:** Clique em "Novo Curso"
3. **Link de Inscrição Pública:** Botão "Ver Link de Inscrição" na lista

---

## 🔒 Segurança Implementada

✅ Prepared Statements (PDO) - SQL Injection  
✅ htmlspecialchars() - XSS  
✅ Validação de e-mail  
✅ Transações database - Integridade  
✅ ON DELETE CASCADE - Integridade referencial  
✅ Verificação de sessão - Acesso restrito  

---

## 📊 Funcionalidades

### Painel Administrativo
- [x] Listar todos os cursos com estatísticas
- [x] Criar novo curso/palestra
- [x] Alternar status (Aberto/Rascunho)
- [x] Excluir curso (remove inscrições automaticamente)
- [x] Ver número de inscritos por curso
- [x] Link direto para página de inscrição

### Página Pública de Inscrição
- [x] Design responsivo Bootstrap 5
- [x] Máscara de telefone automática
- [x] Campos dinâmicos (Acadêmico/Profissional)
- [x] Validação de campos obrigatórios
- [x] Mensagens de sucesso/erro
- [x] Chancela institucional
- [x] Link para Política de Privacidade

### Backend
- [x] Deduplicação por e-mail
- [x] Transações para integridade
- [x] PHPMailer configurado (comentar/descomentar)
- [x] Tratamento de erros

---

## 🎨 Identidade Visual

- Cores: Vermelho Humaniza (#E30613)
- Fonte: Montserrat (Google Fonts)
- Ícones: Bootstrap Icons
- Responsivo: Mobile-first

---

## 📞 Suporte

Dúvidas sobre a implementação? Consulte:
- `AUDITORIA_TECNICA.md` - Diretrizes de segurança e UX
- Comentários nos arquivos PHP - Explicações inline

---

**Implementado por:** Assistente de Desenvolvimento  
**Data:** Setembro/2025  
**Versão:** 1.0.0

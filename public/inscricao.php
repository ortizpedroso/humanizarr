<?php
session_start();

require_once '../config/Database.php';
require_once '../models/Curso.php';

$database = new Database();
$db = $database->getConnection();
$curso = new Curso($db);

// Verifica se ID do curso foi passado
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['msg_erro'] = "Curso não encontrado.";
    header("Location: ../index.php");
    exit;
}

$id_curso = (int)$_GET['id'];
$curso_item = $curso->lerPorId($id_curso);

// Verifica se curso existe e está com status Aberto
if (!$curso_item || $curso_item['status'] != 'Aberto') {
    $titulo_pagina = "Inscrições Encerradas";
    $curso_indisponivel = true;
} else {
    $titulo_pagina = htmlspecialchars($curso_item['nome']);
    $curso_indisponivel = false;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo_pagina ?> - Humaniza RR</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
        }
        .hero-section {
            background: linear-gradient(135deg, #E30613 0%, #c41c26 100%);
            color: white;
            padding: 60px 0;
            margin-bottom: 40px;
        }
        .card-info {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .card-info:hover {
            transform: translateY(-3px);
        }
        .form-container {
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 4px 30px rgba(0,0,0,0.1);
        }
        .btn-inscrever {
            padding: 15px 40px;
            font-weight: 700;
            font-size: 1.1rem;
            border-radius: 50px;
        }
        .chancela {
            background: #f8f9fa;
            border-left: 4px solid #E30613;
            padding: 20px;
            margin-top: 30px;
            border-radius: 8px;
        }
        .icone-curso {
            font-size: 3rem;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero-section text-center">
        <div class="container">
            <i class="bi bi-mortarboard-fill icone-curso"></i>
            <h1 class="fw-bold mb-3"><?= $curso_indisponivel ? 'Inscrições Encerradas' : htmlspecialchars($curso_item['nome']) ?></h1>
            <?php if (!$curso_indisponivel): ?>
                <p class="lead mb-0">Garanta sua vaga neste evento exclusivo!</p>
            <?php endif; ?>
        </div>
    </section>

    <div class="container mb-5">
        <?php if ($curso_indisponivel): ?>
            <!-- Curso indisponível -->
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card card-info p-5 text-center">
                        <i class="bi bi-exclamation-triangle-fill text-warning fs-1 mb-3"></i>
                        <h3 class="mb-3">Inscrições Indisponíveis</h3>
                        <p class="text-muted mb-4">
                            <?php if (!$curso_item): ?>
                                Este curso não foi encontrado ou foi removido.
                            <?php else: ?>
                                As inscrições para este curso estão temporariamente fechadas.
                                Entre em contato conosco para mais informações.
                            <?php endif; ?>
                        </p>
                        <a href="../index.php" class="btn btn-outline-danger px-4">
                            <i class="bi bi-house me-2"></i>Voltar ao Site
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="row">
                <!-- Informações do Curso -->
                <div class="col-lg-5 mb-4">
                    <div class="card card-info p-4 mb-3">
                        <h4 class="fw-bold mb-4"><i class="bi bi-info-circle me-2"></i>Informações do Evento</h4>
                        
                        <div class="mb-3">
                            <strong><i class="bi bi-calendar-event me-2"></i>Data e Horário:</strong><br>
                            <?= date('d/m/Y \à\s H:i', strtotime($curso_item['data_evento'])) ?>h
                        </div>
                        
                        <div class="mb-3">
                            <strong><i class="bi bi-clock me-2"></i>Duração:</strong><br>
                            <?= htmlspecialchars($curso_item['duracao']) ?>
                        </div>
                        
                        <div class="mb-3">
                            <strong><i class="bi bi-geo-alt me-2"></i>Local:</strong><br>
                            <?= htmlspecialchars($curso_item['local']) ?>
                        </div>
                        
                        <div class="mb-3">
                            <strong><i class="bi bi-person-badge me-2"></i>Palestrante:</strong><br>
                            <?= htmlspecialchars($curso_item['palestrante']) ?>
                        </div>
                        
                        <?php if (!empty($curso_item['descricao'])): ?>
                        <div class="mb-3">
                            <strong><i class="bi bi-file-text me-2"></i>Descrição:</strong><br>
                            <?= nl2br(htmlspecialchars($curso_item['descricao'])) ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Chancela Institucional -->
                    <div class="chancela">
                        <h6 class="fw-bold mb-3"><i class="bi bi-shield-check me-2"></i>Realização:</h6>
                        <p class="mb-2"><strong>Instituto Humaniza RR</strong></p>
                        <p class="mb-1"><small>Presidente: <?= htmlspecialchars($curso_item['presidente']) ?></small></p>
                        <p class="mb-0"><small>Vice-Presidente: <?= htmlspecialchars($curso_item['vice_presidente']) ?></small></p>
                    </div>
                </div>

                <!-- Formulário de Inscrição -->
                <div class="col-lg-7">
                    <div class="form-container">
                        <h3 class="fw-bold mb-4 text-center">
                            <i class="bi bi-pencil-square me-2"></i>Ficha de Inscrição
                        </h3>

                        <?php if (isset($_SESSION['sucesso_inscricao'])): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bi bi-check-circle me-2"></i>
                                <strong>Inscrição realizada com sucesso!</strong><br>
                                Enviamos um e-mail de confirmação para o endereço informado.
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            <?php unset($_SESSION['sucesso_inscricao']); ?>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['erro_inscricao'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <?= htmlspecialchars($_SESSION['erro_inscricao']) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            <?php unset($_SESSION['erro_inscricao']); ?>
                        <?php endif; ?>

                        <form action="processar_inscricao.php?id_curso=<?= $id_curso ?>" method="POST">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="nome" class="form-label">Nome Completo *</label>
                                    <input type="text" class="form-control form-control-lg" id="nome" name="nome" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">E-mail *</label>
                                    <input type="email" class="form-control form-control-lg" id="email" name="email" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="telefone" class="form-label">Telefone/WhatsApp *</label>
                                    <input type="tel" class="form-control form-control-lg" id="telefone" name="telefone" 
                                           placeholder="(XX) XXXXX-XXXX" required maxlength="15">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="tipo" class="form-label">Tipo de Inscrição *</label>
                                    <select class="form-select form-select-lg" id="tipo" name="tipo" required onchange="toggleCampoExtra()">
                                        <option value="">Selecione...</option>
                                        <option value="Acadêmico">Acadêmico</option>
                                        <option value="Profissional de Saúde">Profissional de Saúde</option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="instituicao" class="form-label" id="label_extra">Instituição *</label>
                                    <input type="text" class="form-control form-control-lg" id="instituicao" name="instituicao" required>
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label for="semestre_atuacao" class="form-label" id="label_semestre">Semestre/Atuação *</label>
                                    <input type="text" class="form-control form-control-lg" id="semestre_atuacao" name="semestre_atuacao" 
                                           placeholder="Ex: 5º semestre ou Cardiologia" required>
                                </div>
                            </div>

                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-danger btn-inscrever">
                                    <i class="bi bi-check-circle me-2"></i>Confirmar Inscrição
                                </button>
                            </div>

                            <p class="text-muted text-center mt-3 mb-0 small">
                                <i class="bi bi-lock me-1"></i>Seus dados estão seguros. Ao se inscrever, você concorda com nossa 
                                <a href="../politica-privacidade.php" target="_blank">Política de Privacidade</a>.
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-4 mt-5">
        <div class="container">
            <p class="mb-0">&copy; 2025 Instituto Humaniza RR. Todos os direitos reservados.</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Máscara de Telefone e Script de Toggle -->
    <script>
        // Máscara de telefone
        document.getElementById('telefone').addEventListener('input', function(e) {
            let valor = e.target.value.replace(/\D/g, '');
            
            if (valor.length > 11) valor = valor.substring(0, 11);
            
            if (valor.length >= 6) {
                valor = valor.replace(/(\d{2})(\d{5})(\d{4}).*/, '($1) $2-$3');
            } else if (valor.length >= 2) {
                valor = valor.replace(/(\d{2})(\d{0,5}).*/, '($1) $2');
            }
            
            e.target.value = valor;
        });

        // Toggle de campos baseado no tipo
        function toggleCampoExtra() {
            const tipo = document.getElementById('tipo').value;
            const labelExtra = document.getElementById('label_extra');
            const labelSemestre = document.getElementById('label_semestre');
            const inputExtra = document.getElementById('instituicao');
            const inputSemestre = document.getElementById('semestre_atuacao');

            if (tipo === 'Acadêmico') {
                labelExtra.textContent = 'Instituição de Ensino *';
                labelSemestre.textContent = 'Semestre *';
                inputExtra.placeholder = 'Ex: Universidade Federal de Roraima';
                inputSemestre.placeholder = 'Ex: 5º semestre';
            } else if (tipo === 'Profissional de Saúde') {
                labelExtra.textContent = 'Instituição/Empresa *';
                labelSemestre.textContent = 'Área de Atuação *';
                inputExtra.placeholder = 'Ex: Hospital Geral de Roraima';
                inputSemestre.placeholder = 'Ex: Cardiologia, UTI, Emergência';
            } else {
                labelExtra.textContent = 'Instituição *';
                labelSemestre.textContent = 'Semestre/Atuação *';
                inputExtra.placeholder = '';
                inputSemestre.placeholder = '';
            }
        }

        // Inicializa o toggle ao carregar a página
        document.addEventListener('DOMContentLoaded', function() {
            toggleCampoExtra();
        });
    </script>
</body>
</html>

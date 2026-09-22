<?php
/**
 * PÁGINA DOE AQUI - INSTITUTO HUMANIZA RR
 * Localização: src/doe-aqui.php
 */
require_once __DIR__ . '/config/Database.php';

$database = new Database();
$db = $database->getConnection();

// Busca Redes Sociais para o Footer
try {
    $redes_raw = $db->query("SELECT * FROM configuracoes")->fetchAll(PDO::FETCH_ASSOC);
    $redes = [];
    foreach($redes_raw as $r) { $redes[$r['chave']] = $r['valor']; }
} catch(Exception $e) { $redes = []; }

include __DIR__ . '/public/includes/header.php';
?>

<div class="py-5 shadow-sm" style="background-color: var(--h-red);">
    <div class="container text-center py-4">
        <h1 class="display-4 fw-bold text-white mb-2">Doe Aqui</h1>
        <p class="text-white-50 mb-0 fs-5">Sua doação transforma vidas e fortalece nossas ações em Roraima</p>
    </div>
</div>

<section class="bg-light">
    <div class="container">
        <div class="row g-4 justify-content-center">
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm p-4 p-md-5 rounded-4 h-100 text-center bg-white">
                    <i class="bi bi-qr-code-scan display-1 text-danger mb-4"></i>
                    <h3 class="fw-bold mb-3">Doação via PIX</h3>
                    <p class="text-muted mb-4">Aponte a câmera do seu celular para o QR Code ou use nossa chave PIX abaixo.</p>
                    
                    <div class="bg-light p-3 rounded-3 mb-4 border-dashed border-2">
                        <img src="https://via.placeholder.com/200?text=QR+CODE+PIX" class="img-fluid rounded mb-3" style="max-width: 200px;">
                        <div class="fw-bold text-dark">Chave CNPJ:</div>
                        <div class="text-danger fs-5 fw-bold">00.000.000/0001-00</div>
                    </div>
                    
                    <p class="small text-muted">Instituto Humaniza Roraima</p>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card border-0 shadow-sm p-4 p-md-5 rounded-4 h-100 bg-white">
                    <h3 class="fw-bold mb-4 text-center">Outras Doações</h3>
                    
                    <div class="mb-4">
                        <h6 class="fw-bold text-danger"><i class="bi bi-box-seam me-2"></i>Doações Físicas</h6>
                        <p class="small text-muted" style="text-align: justify;">Recebemos alimentos não perecíveis, roupas em bom estado, calçados e materiais escolares diretamente em nossa sede.</p>
                    </div>

                    <div class="mb-4">
                        <h6 class="fw-bold text-danger"><i class="bi bi-building me-2"></i>Nossa Sede</h6>
                        <p class="small text-muted">Boa Vista, Roraima<br>Segunda a Sexta: 08h às 18h</p>
                    </div>

                    <div class="p-4 bg-light rounded-4 border-start border-danger border-5 mt-auto">
                        <h6 class="fw-bold mb-2">Dúvidas sobre doações?</h6>
                        <p class="small mb-3 text-muted">Fale diretamente com nossa equipe de apoio via WhatsApp.</p>
                        <a href="https://wa.me/<?= str_replace(['(', ')', '-', ' '], '', $redes['whatsapp'] ?? '') ?>" class="btn btn-success w-100 rounded-pill fw-bold">
                            <i class="bi bi-whatsapp me-2"></i>CONVERSAR AGORA
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/public/includes/footer.php'; ?>
<?php
/**
 * Termos de Uso - Instituto Humaniza RR
 * Localização: /termos-uso.php
 */
include __DIR__ . '/public/includes/header.php';
?>

<div class="py-5 shadow-sm" style="background-color: var(--h-red);">
    <div class="container text-center py-4">
        <h1 class="display-4 fw-bold text-white mb-2">Termos de Uso</h1>
        <p class="text-white-50 mb-0 fs-5">Condições gerais de utilização do site</p>
    </div>
</div>

<section class="bg-light py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card border-0 shadow-sm p-4 p-md-5 rounded-4 bg-white">
                    
                    <div class="alert alert-info border-0" role="alert">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        <strong>Atualizado em:</strong> <?= date('d/m/Y') ?> | 
                        <strong>Versão:</strong> 1.0
                    </div>

                    <h2 class="fw-bold mt-4 mb-3" style="color: var(--h-red);">1. ACEITAÇÃO DOS TERMOS</h2>
                    <p style="text-align: justify; line-height: 1.8;">
                        Ao acessar e utilizar o site do <strong>Instituto Humaniza RR</strong>, 
                        você concorda com estes Termos de Uso na íntegra. Caso não concorde com 
                        algum destes termos, solicitamos que não utilize este site.
                    </p>

                    <h2 class="fw-bold mt-5 mb-3" style="color: var(--h-red);">2. SOBRE O SITE</h2>
                    <p style="text-align: justify; line-height: 1.8;">
                        Este site tem como objetivo apresentar as atividades, projetos e iniciativas 
                        do Instituto Humaniza RR, além de facilitar o contato para doações, 
                        voluntariado e parcerias.
                    </p>
                    <ul style="line-height: 2;">
                        <li><strong>URL:</strong> www.humanizarr.org</li>
                        <li><strong>Mantenedor:</strong> Instituto Humaniza RR</li>
                        <li><strong>Finalidade:</strong> Institucional e informativa</li>
                    </ul>

                    <h2 class="fw-bold mt-5 mb-3" style="color: var(--h-red);">3. USO PERMITIDO</h2>
                    <div class="card bg-success bg-opacity-10 border-0 mb-3">
                        <div class="card-body">
                            <h6 class="fw-bold text-success"><i class="bi bi-check-circle me-2"></i>Você PODE:</h6>
                            <ul class="mb-0" style="line-height: 1.8;">
                                <li>Acessar e navegar pelo site livremente</li>
                                <li>Compartilhar conteúdo nas redes sociais (com créditos)</li>
                                <li>Entrar em contato através dos canais oficiais</li>
                                <li>Fazer doações através dos meios disponibilizados</li>
                                <li>Usar o site para fins pessoais e informativos</li>
                            </ul>
                        </div>
                    </div>

                    <div class="card bg-danger bg-opacity-10 border-0 mb-3">
                        <div class="card-body">
                            <h6 class="fw-bold text-danger"><i class="bi bi-x-circle me-2"></i>Você NÃO PODE:</h6>
                            <ul class="mb-0" style="line-height: 1.8;">
                                <li>Modificar, copiar ou distribuir conteúdo sem autorização</li>
                                <li>Usar o site para fins comerciais não autorizados</li>
                                <li>Tentar acessar áreas restritas do sistema</li>
                                <li>Utilizar bots, crawlers ou scripts automatizados</li>
                                <li>Enviar spam ou conteúdo malicioso através dos formulários</li>
                                <li>Se passar por outra pessoa ou entidade</li>
                            </ul>
                        </div>
                    </div>

                    <h2 class="fw-bold mt-5 mb-3" style="color: var(--h-red);">4. PROPRIEDADE INTELECTUAL</h2>
                    <p style="text-align: justify; line-height: 1.8;">
                        Todo o conteúdo deste site (textos, imagens, logos, vídeos, layout) é de 
                        propriedade exclusiva do Instituto Humaniza RR e está protegido pela Lei 
                        de Direitos Autorais (Lei nº 9.610/98).
                    </p>
                    <div class="alert alert-warning border-0" role="alert">
                        <strong>Importante:</strong> É permitida a reprodução de conteúdos para 
                        fins jornalísticos ou educacionais, desde que citada a fonte.
                    </div>

                    <h2 class="fw-bold mt-5 mb-3" style="color: var(--h-red);">5. FORMULÁRIOS E CADASTROS</h2>
                    <p style="text-align: justify; line-height: 1.8;">
                        Ao preencher formulários no site, você declara que:
                    </p>
                    <ul style="line-height: 2;">
                        <li>✓ As informações fornecidas são verdadeiras e precisas</li>
                        <li>✓ Você tem autorização para usar os dados informados</li>
                        <li>✓ Compreende que os dados serão utilizados conforme a Política de Privacidade</li>
                        <li>✓ Concorda em receber comunicações relacionadas à sua solicitação</li>
                    </ul>

                    <h2 class="fw-bold mt-5 mb-3" style="color: var(--h-red);">6. DOAÇÕES</h2>
                    <p style="text-align: justify; line-height: 1.8;">
                        As doações realizadas através do site são processadas de forma segura. 
                        O Instituto Humaniza RR se compromete a:
                    </p>
                    <ul style="line-height: 2;">
                        <li>✅ Utilizar os recursos conforme a missão institucional</li>
                        <li>✅ Emitir recibo de doação quando solicitado</li>
                        <li>✅ Manter transparência sobre a aplicação dos recursos</li>
                    </ul>
                    <p style="text-align: justify; line-height: 1.8;">
                        <strong>Nota:</strong> Doações não são reembolsáveis, exceto em casos de 
                        erro comprovado no processo.
                    </p>

                    <h2 class="fw-bold mt-5 mb-3" style="color: var(--h-red);">7. LINKS EXTERNOS</h2>
                    <p style="text-align: justify; line-height: 1.8;">
                        Este site pode conter links para websites de terceiros (redes sociais, 
                        parceiros, etc.). O Instituto Humaniza RR não se responsabiliza pelo 
                        conteúdo, políticas de privacidade ou práticas desses sites.
                    </p>

                    <h2 class="fw-bold mt-5 mb-3" style="color: var(--h-red);">8. DISPONIBILIDADE DO SITE</h2>
                    <p style="text-align: justify; line-height: 1.8;">
                        Embora nos esforcemos para manter o site sempre disponível, não garantimos:
                    </p>
                    <ul style="line-height: 2;">
                        <li>Funcionamento ininterrupto ou livre de erros</li>
                        <li>Correção imediata de falhas ou vulnerabilidades</li>
                        <li>Compatibilidade com todos os navegadores e dispositivos</li>
                    </ul>
                    <p style="text-align: justify; line-height: 1.8;">
                        Reservamo-nos o direito de suspender o acesso temporariamente para 
                        manutenção ou atualizações.
                    </p>

                    <h2 class="fw-bold mt-5 mb-3" style="color: var(--h-red);">9. LIMITAÇÃO DE RESPONSABILIDADE</h2>
                    <div class="card bg-light border-0 mb-3">
                        <div class="card-body">
                            <p class="mb-0" style="text-align: justify; line-height: 1.8;">
                                O Instituto Humaniza RR não será responsável por danos diretos, 
                                indiretos, incidentais ou consequenciais decorrentes do uso ou 
                                incapacidade de uso deste site, incluindo perda de dados, lucros 
                                cessantes ou interrupção de atividades.
                            </p>
                        </div>
                    </div>

                    <h2 class="fw-bold mt-5 mb-3" style="color: var(--h-red);">10. MODIFICAÇÕES NOS TERMOS</h2>
                    <p style="text-align: justify; line-height: 1.8;">
                        Reservamo-nos o direito de modificar estes Termos de Uso a qualquer 
                        momento. Alterações entrarão em vigor imediatamente após publicação 
                        no site. Recomendamos revisão periódica desta página.
                    </p>

                    <h2 class="fw-bold mt-5 mb-3" style="color: var(--h-red);">11. RESCISÃO</h2>
                    <p style="text-align: justify; line-height: 1.8;">
                        Podemos suspender ou encerrar seu acesso ao site, a nosso exclusivo 
                        critério, sem aviso prévio, caso identifique descumprimento destes 
                        Termos de Uso.
                    </p>

                    <h2 class="fw-bold mt-5 mb-3" style="color: var(--h-red);">12. LEGISLAÇÃO APLICÁVEL</h2>
                    <p style="text-align: justify; line-height: 1.8;">
                        Estes Termos são regidos pelas leis da República Federativa do Brasil. 
                        Qualquer disputa será resolvida no foro da Comarca de Boa Vista/RR, 
                        com renúncia a qualquer outro, por mais privilegiado que seja.
                    </p>

                    <h2 class="fw-bold mt-5 mb-3" style="color: var(--h-red);">13. CONTATO</h2>
                    <div class="card border-primary mb-4">
                        <div class="card-body">
                            <h6 class="fw-bold text-primary"><i class="bi bi-envelope-fill me-2"></i>Dúvidas sobre os Termos?</h6>
                            <p class="mb-2"><strong>E-mail:</strong> juridico@humanizarr.org</p>
                            <p class="mb-0"><strong>Endereço:</strong> Boa Vista - Roraima</p>
                        </div>
                    </div>

                    <div class="mt-5 pt-4 border-top">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <p class="text-muted small mb-0">
                                    Ao utilizar este site, você declara que leu, compreendeu e 
                                    concordou com todos os termos descritos acima.
                                </p>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <p class="text-muted small mb-0">
                                    Última atualização: <?= date('d/m/Y') ?>
                                </p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/public/includes/footer.php'; ?>

<footer class="footer-simple mt-auto">
    <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center" style="padding-top: 0.5rem; padding-bottom: 0.5rem;">

        <div class="footer-brand d-flex align-items-center" style="margin-bottom: 0;">
            <i class="fa-solid fa-couch me-2"></i>
            <span class="fw-bold">Dua Putra Furniture</span>
        </div>

        <div class="footer-text text-center" style="margin-bottom: 0;">
            ©Copyright<?= date('Y') ?> – <strong>23552011143 | Haris Nurpazri</strong> | Mahasiswa UTB – TIF RP 23 CNS B
        </div>

        <div class="footer-social d-flex">
            <a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a>
            <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
            <a href="#" title="YouTube"><i class="fab fa-youtube"></i></a>
        </div>

    </div>
</footer>
    <?php
    // Embed frontend session helper inline so pages don't need to compute relative paths.
    // Prefer modular frontend script if available, otherwise fallback to assets/js/session.js
    $modulePath = __DIR__ . '/../modules/frontend/session_client.js';
    $sessionJsPath = __DIR__ . '/../assets/js/session.js';
    if (file_exists($modulePath)) {
        echo "\n<script>\n" . file_get_contents($modulePath) . "\n</script>\n";
    } else if (file_exists($sessionJsPath)) {
        echo "\n<script>\n" . file_get_contents($sessionJsPath) . "\n</script>\n";
    }
    ?>
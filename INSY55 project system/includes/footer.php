<?php declare(strict_types=1); ?>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="<?= e(base_url('assets/js/app.js')) ?>"></script>
<?php if (!empty($extraScripts)): ?>
    <?php foreach ((array) $extraScripts as $script): ?>
        <?php $src = preg_match('#^https?://#i', $script) ? $script : base_url($script); ?>
        <script src="<?= e($src) ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>
</body>
</html>

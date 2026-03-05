<footer class="custom-footer">
    <div class="footer-content">
        <span>© 2025 Emby Admin Panel • Realizzato da <a href="https://github.com/MiottoNicola">Nicola Miotto</a></span>
        <span class="footer-links">
            <a href="https://t.me/sonicmaster">Supporto</a> |
            <a href="#">Telegram Bot</a>
        </span>
    </div>
</footer>
<style>
    .custom-footer {
        background: #009688;
        color: #fff;
        padding: 18px 0 12px 0;
        text-align: center;
        font-size: 1em;
        margin-top: 48px;
        box-shadow: 0 -2px 8px #00968822;
        position: relative;
        z-index: 5;
    }

    .footer-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
    }

    .footer-links a {
        color: #e0f2f1;
        text-decoration: none;
        margin: 0 6px;
        font-size: 0.98em;
        transition: color 0.2s;
    }

    .footer-links a:hover {
        color: #fff;
        text-decoration: underline;
    }
</style>
    <?php if (isset($toastMsg)): ?>
        <script>
            showToast(<?php echo json_encode($toastMsg); ?>, 3000, <?php echo json_encode($toastColor ?? '#43a047'); ?>);
        </script>
    <?php endif; ?>
</body>

</html>
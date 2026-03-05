<!-- Toast Notification Component - riutilizzabile -->
<style>
.toast {
    display: none;
    position: fixed;
    top: 32px;
    right: 32px;
    min-width: 260px;
    max-width: 350px;
    background: #43a047;
    color: #fff;
    padding: 18px 32px 18px 18px;
    border-radius: 8px;
    box-shadow: 0 2px 12px #0002;
    z-index: 9999;
    font-size: 1.08em;
    animation: fadein 0.3s;
}
.toast .close {
    background: none;
    border: none;
    color: #fff;
    font-size: 1.3em;
    position: absolute;
    top: 8px;
    right: 12px;
    cursor: pointer;
}
@keyframes fadein {
    from { opacity: 0; right: 0; }
    to { opacity: 1; right: 32px; }
}
</style>
<div id="toast" class="toast">
    <span id="toast-message"></span>
    <button class="close" onclick="document.getElementById('toast').style.display='none'">&times;</button>
</div>
<script>
function showToast(message, duration = 3000, color = '#43a047') {
    var toast = document.getElementById('toast');
    var msg = document.getElementById('toast-message');
    toast.style.background = color;
    msg.textContent = message;
    toast.style.display = 'block';
    setTimeout(function() {
        toast.style.display = 'none';
    }, duration);
}
</script>
<!-- /Toast Notification Component -->

    <?php if (isset($toastMsg)): ?>
        <script>
            showToast(<?php echo json_encode($toastMsg); ?>, 3000, <?php echo json_encode($toastColor ?? '#43a047'); ?>);
        </script>
    <?php endif; ?>
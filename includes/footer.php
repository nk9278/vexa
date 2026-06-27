<?php
// includes/footer.php
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) die('Direct access denied.');
?>
    <!-- jQuery Core (Required for Legacy Interoperability & Easy AJAX) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

    <!-- Chart.js (Loaded globally but utilized per module) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Global App JS -->
    <script src="<?= ASSETS_URL ?>js/app.js"></script>
</body>
</html>

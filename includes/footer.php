<?php
$isAdmin = isset($_SESSION['login']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
?>
<footer>
    <p>&copy; <?= date("Y"); ?> <?= defined('APP_NAME') ? APP_NAME : 'Outdoor Rental'; ?>. Semua Hak Dilindungi.</p>
</footer>

<?php if (!$isAdmin) { ?>
    <!-- Floating WhatsApp Widget -->
    <a href="https://wa.me/6281234567890?text=Halo%20Admin%20Outdoor%20Rental,%20saya%20ingin%20bertanya%20seputar%20sewa%20peralatan" 
       target="_blank" 
       class="floating-whatsapp no-print" 
       title="Hubungi Admin via WhatsApp">
        <span>💬</span> Chat Admin WA
    </a>
<?php } ?>

</body>
</html>
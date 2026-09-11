<?php
$isAdmin = isset($_SESSION['login']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
?>

<footer>
    <p>&copy; <?= date("Y"); ?> <?= defined('APP_NAME') ? APP_NAME : 'Mada Adventure'; ?> (Outdoor Rental). Semua Hak Dilindungi.</p>
</footer>

<?php if (!$isAdmin) { ?>
    <!-- Floating WhatsApp Widget -->
    <a href="https://wa.me/6281234567890?text=Halo%20Admin%20Mada%20Adventure,%20saya%20ingin%20bertanya%20seputar%20sewa%20peralatan"
        target="_blank"
        class="floating-whatsapp no-print"
        title="Hubungi Admin via WhatsApp">
        <span>💬</span> Hubungi Admin WA
    </a>
<?php } ?>

</div> <!-- Tag penutup untuk <div id="app"> -->

<!-- Helper Dialog Konfirmasi Modern (SweetAlert2) -->
<script>
function konfirmasiForm(formId, judul, pesan, ikon = 'question', teksTombol = 'Ya, Lanjutkan', warnaTombol = '#10b981') {
    Swal.fire({
        title: judul,
        text: pesan,
        icon: ikon,
        showCancelButton: true,
        confirmButtonColor: warnaTombol,
        cancelButtonColor: '#64748b',
        confirmButtonText: teksTombol,
        cancelButtonText: 'Batal',
        reverseButtons: true,
        customClass: {
            popup: 'swal-custom-popup',
            title: 'swal-custom-title',
            confirmButton: 'swal-btn-confirm',
            cancelButton: 'swal-btn-cancel'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.getElementById(formId);
            if (form) {
                form.submit();
            }
        }
    });
}

function konfirmasiHapus(url, judul = 'Apakah Anda yakin?', pesan = 'Data akan dihapus permanen dari sistem.') {
    Swal.fire({
        title: judul,
        text: pesan,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        customClass: {
            popup: 'swal-custom-popup',
            title: 'swal-custom-title',
            confirmButton: 'swal-btn-confirm',
            cancelButton: 'swal-btn-cancel'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });
}
</script>

</body>

</html>
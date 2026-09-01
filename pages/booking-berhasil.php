<?php

require_once '../config/config.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include '../includes/header.php';

?>

<section class="booking-success">

    <h1>Booking Berhasil! 🎉</h1>

    <p>
        Booking kamu berhasil disimpan.
    </p>

    <p>
        Silakan tunggu konfirmasi dari admin.
    </p>

    <br>

    <a href="peralatan.php" class="btn">
        Kembali ke Peralatan
    </a>

</section>

<?php

include '../includes/footer.php';

?>
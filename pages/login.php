<?php

require_once '../config/config.php';
require_once '../config/functions.php';

if (isset($_SESSION['login'])) {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        header("Location: ../admin/index.php");
    } else {
        header("Location: ../index.php");
    }
    exit;
}

$error = "";

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user['password'])) {
            $_SESSION['login'] = true;
            $_SESSION['id_user'] = $user['id'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header("Location: ../admin/index.php");
                exit;
            } else {
                header("Location: ../index.php");
                exit;
            }
        } else {
            $error = "Password yang Anda masukkan salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
}

include '../includes/header.php';

?>

<section class="login auth-card">
    <div class="auth-header">
        <div class="auth-icon">🔑</div>
        <h2>Masuk ke Akun Anda</h2>
        <p class="text-muted">Gunakan akun Anda untuk menyewa peralatan outdoor dan memantau riwayat transaksi.</p>
    </div>

    <?php if (isset($_GET['register']) && $_GET['register'] === 'success') { ?>
        <div class="success-box">
            ✓ Pendaftaran berhasil! Silakan masuk menggunakan akun baru Anda.
        </div>
    <?php } ?>

    <?php if ($error != "") { ?>
        <div class="error-box">
            <?= htmlspecialchars($error); ?>
        </div>
    <?php } ?>

    <form action="" method="POST" class="auth-form">
        <label>Nama Pengguna (Username)</label>
        <input
            type="text"
            name="username"
            placeholder="Masukkan nama pengguna"
            value="<?= htmlspecialchars($_POST['username'] ?? ''); ?>"
            required>

        <label>Kata Sandi (Password)</label>
        <input
            type="password"
            name="password"
            placeholder="Masukkan kata sandi"
            required>

        <button
            type="submit"
            name="login"
            class="btn btn-primary btn-block btn-auth mt-4">
            Masuk Sekarang →
        </button>
    </form>

    <div class="auth-footer mt-4">
        <p>Belum memiliki akun? <a href="register.php" class="text-link"><strong>Daftar Akun Baru</strong></a></p>
    </div>
</section>

<?php

include '../includes/footer.php';

?>
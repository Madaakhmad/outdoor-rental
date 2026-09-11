<?php

require_once '../config/config.php';

if (isset($_SESSION['login'])) {
    header("Location: ../index.php");
    exit;
}

$errors = [];

if (isset($_POST['register'])) {
    $nama = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $konfirmasi = $_POST['konfirmasi'];

    // Validasi
    if ($nama === '') {
        $errors[] = "Nama lengkap harus diisi.";
    }

    if ($username === '') {
        $errors[] = "Username harus diisi.";
    }

    if (strlen($password) < 4) {
        $errors[] = "Password minimal 4 karakter.";
    }

    if ($password !== $konfirmasi) {
        $errors[] = "Konfirmasi password tidak cocok.";
    }

    // Cek Username Duplikat
    if (empty($errors)) {
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            $errors[] = "Username sudah digunakan, silakan gunakan username lain.";
        }
    }

    // Simpan User Baru
    if (empty($errors)) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $role = "customer";

        $stmtInsert = mysqli_prepare(
            $conn,
            "INSERT INTO users (nama, username, password, role) VALUES (?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param($stmtInsert, "ssss", $nama, $username, $passwordHash, $role);

        if (mysqli_stmt_execute($stmtInsert)) {
            header("Location: login.php?register=success");
            exit;
        } else {
            $errors[] = "Registrasi gagal. Silakan coba lagi.";
        }
    }
}

include '../includes/header.php';

?>

<section class="login auth-card">
    <div class="auth-header">
        <div class="auth-icon">📝</div>
        <h2>Daftar Akun Baru</h2>
        <p class="text-muted">Buat akun untuk mulai menyewa berbagai perlengkapan outdoor.</p>
    </div>

    <?php if (!empty($errors)) { ?>
        <div class="error-box">
            <ul>
                <?php foreach ($errors as $error) { ?>
                    <li><?= htmlspecialchars($error); ?></li>
                <?php } ?>
            </ul>
        </div>
    <?php } ?>

    <form method="POST" class="auth-form">
        <label>Nama Lengkap</label>
        <input
            type="text"
            name="nama"
            placeholder="Contoh: Budi Santoso"
            value="<?= htmlspecialchars($_POST['nama'] ?? ''); ?>"
            required>

        <label>Nama Pengguna (Username)</label>
        <input
            type="text"
            name="username"
            placeholder="Pilih nama pengguna"
            value="<?= htmlspecialchars($_POST['username'] ?? ''); ?>"
            required>

        <label>Kata Sandi (Password)</label>
        <input
            type="password"
            name="password"
            placeholder="Minimal 4 karakter"
            required>

        <label>Konfirmasi Kata Sandi</label>
        <input
            type="password"
            name="konfirmasi"
            placeholder="Ulangi kata sandi di atas"
            required>

        <button
            type="submit"
            name="register"
            class="btn btn-primary btn-block btn-auth mt-4">
            Daftar Sekarang →
        </button>
    </form>

    <div class="auth-footer mt-4">
        <p>Sudah punya akun? <a href="login.php" class="text-link"><strong>Masuk di sini</strong></a></p>
    </div>
</section>

<?php

include '../includes/footer.php';

?>
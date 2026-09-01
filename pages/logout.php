<?php

require_once '../config/config.php';

// Hapus semua session
session_unset();
session_destroy();

// Kembali ke halaman login
header("Location: login.php");
exit;

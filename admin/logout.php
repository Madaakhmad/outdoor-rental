<?php

require_once '../config/config.php';

// Menghapus semua data session
session_unset();

// Menghancurkan session
session_destroy();

// Kembali ke halaman login
header("Location: ../pages/login.php");
exit;

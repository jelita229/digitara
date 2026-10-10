<?php
// 1. HUBUNGKAN KONEKSI DATABASE
include 'koneksi.php';

// Inisialisasi variabel untuk menampung pesan alert
$alert_message = "";
$alert_type    = "";

// 2. PROSES LOGIKA REGISTER (HANYA BERJALAN JIKA FORM DI-SUBMIT)
if (isset($_POST['register'])) {
    
    // Ambil data dari form dan amankan dari SQL Injection
    $nama     = mysqli_real_escape_string($conn, $_POST['nama']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    // Cek apakah username sudah terdaftar
    $cek_user = "SELECT username FROM users WHERE username = '$username'";
    $result   = mysqli_query($conn, $cek_user);

    if (mysqli_num_rows($result) > 0) {
        $alert_type    = "warning";
        $alert_message = "Username sudah digunakan! Silakan cari username lain.";
    } else {
        // Enkripsi password demi keamanan data user
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);

        // Query insert data ke tabel users
        $sql = "INSERT INTO users (nama, username, password) VALUES ('$nama', '$username', '$password_hashed')";

        if (mysqli_query($conn, $sql)) {
            $alert_type    = "success";
            $alert_message = "Registrasi berhasil! Selamat Berjuang Sukses.";
        } else {
            $alert_type    = "danger";
            $alert_message = "Registrasi gagal. Terjadi kesalahan sistem.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Registrasi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .register-container { max-width: 450px; margin-top: 80px; }
    </style>
</head>
<body>

<div class="container register-container">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white text-center py-3">
            <h4 class="mb-0">Form Pendaftaran</h4>
        </div>
        <div class="card-body p-4">
            
            <?php if (!empty($alert_message)): ?>
                <div class="alert alert-<?= $alert_type; ?> alert-dismissible fade show" role="alert">
                    <?= $alert_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="mb-3">
                    <label for="nama" class="form-label">Nama Lengkap</label>
                    <input type="text" class="form-control" id="nama" name="nama" placeholder="Masukkan nama lengkap" required>
                </div>

                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan username" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password" required>
                </div>

                <button type="submit" name="register" class="btn btn-primary w-100 mt-2">Daftar Sekarang</button>
                <a class="btn btn-outline-primary" href="index.php">Kembali</a>
                
                <p class="small mb-0">Sudah punya akun? <a href="login.php" class="text-decoration-none">Login Disini!</a></p>
            </form>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
// 1. HUBUNGKAN KONEKSI DATABASE & MULAI SESSION
include 'koneksi.php';
session_start();

// Jika user sudah login, langsung alihkan ke halaman utama/dashboard (opsional)
if (isset($_SESSION['username'])) {
    // header("Location: dashboard.php"); // Buka komentar ini jika sudah ada halaman dashboard
    // exit();
}

// Inisialisasi variabel untuk menampung pesan alert
$alert_message = "";
$alert_type    = "";

// 2. PROSES LOGIKA LOGIN (HANYA BERJALAN JIKA FORM DI-SUBMIT)
if (isset($_POST['login'])) {
    
    // Ambil data dari form dan amankan dari SQL Injection
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    // Cari user berdasarkan username
    $query  = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) === 1) {
        // Ambil data user dari database
        $row = mysqli_fetch_assoc($result);

        // Verifikasi password yang diinput dengan password_hash yang ada di database
        // Verifikasi password yang diinput dengan password_hash yang ada di database
        if (password_verify($password, $row['password'])) {
            
            // Membuat Session User
            $_SESSION['id_user']  = $row['id'];
            $_SESSION['nama']     = $row['nama'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['is_login'] = true; // Tambahan flag untuk memastikan user sudah login

            // Redirect langsung ke halaman index.php
            header("Location: index.php");
            exit(); // Menghentikan eksekusi script setelah redirect
            
        } else {
            // Jika password salah
            $alert_type    = "danger";
            $alert_message = "Password yang Anda masukkan salah!";
        }
    } else {
        // Jika username tidak ditemukan
        $alert_type    = "danger";
        $alert_message = "Username tidak terdaftar!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Login</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .login-container { max-width: 400px; margin-top: 100px; }
    </style>
</head>
<body>

<div class="container login-container">
    <div class="card shadow-sm">
        <div class="card-header bg-success text-white text-center py-3">
            <h4 class="mb-0">Form Login</h4>
        </div>
        <div class="card-body p-4">
            
            <!-- 3. NOTIFIKASI PESAN -->
            <?php if (!empty($alert_message)): ?>
                <div class="alert alert-<?= $alert_type; ?> alert-dismissible fade show" role="alert">
                    <?= $alert_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <!-- Input Username -->
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan username" required>
                </div>

                <!-- Input Password -->
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password" required>
                </div>

                <!-- Button Submit -->
                <button type="submit" name="login" class="btn btn-success w-100 mt-2">Masuk</button>
            </style>

            <!-- Link ke halaman Register -->
            <div class="text-center mt-3">
                <p class="small mb-0">Belum punya akun? <a href="register.php" class="text-decoration-none">Daftar disini</a></p>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
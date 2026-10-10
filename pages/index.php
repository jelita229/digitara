<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DIGIDAW WISATA BALI - Wisata Bali</title>
    
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <link rel="stylesheet" href="../pages/style.css">
</head>
<body>

<?php 
// Menyertakan file koneksi database
include 'koneksi.php';

// Cek apakah variabel koneksi tersedia
if (!isset($conn)) {
    die("<div class='alert alert-danger m-3'>Error: Variabel koneksi '\$conn' tidak ditemukan. Periksa isi file koneksi.php Anda.</div>");
}

// Mengambil data objek wisata dari database
$result = mysqli_query($conn, "SELECT * FROM objek_wisata");

$wisata_favorit = [];

// Fitur Debugging otomatis jika query gagal
if (!$result) {
    die("<div class='alert alert-danger m-3'><strong>Query Database Gagal!</strong><br>Pesan Error: " . mysqli_error($conn) . "</div>");
} else {
    while ($row = mysqli_fetch_assoc($result)) {
        // Memastikan nama kolom sesuai array, jika tidak ada di-fallback agar tidak crash
        $title = isset($row["title_objek_wisata"]) ? $row["title_objek_wisata"] : "Judul Tidak Tersedia";
        $deskripsi = isset($row["deskripsi_objek_wisata"]) ? $row["deskripsi_objek_wisata"] : "Deskripsi Tidak Tersedia";
        $img = isset($row["img_objek_wisata"]) ? $row["img_objek_wisata"] : "https://via.placeholder.com/600x400";

        $wisata_favorit[] = [
            "title"     => $title,
            "deskripsi" => $deskripsi,
            "img"       => $img
        ];
    }
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col">
            <nav class="navbar navbar-expand-lg bg-body-tertiary">
                <div class="container-fluid">
                    <a class="navbar-brand" href="#">DIGITARA BALI</a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                        aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                            <li class="nav-item">
                                <a class="nav-link active" aria-current="page" href="#">Home</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#">Event</a>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Artikel
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#">Wisata Alam</a></li>
                                    <li><a class="dropdown-item" href="#">Kuliner</a></li>
                                    <li><a class="dropdown-item" href="#">Budaya</a></li>
                                </ul>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#">Kontak</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="kelola_objekwisata.php">Kelola Objek Wisata</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link fw-bold text-primary" href="akomodasi.php">Kelola Akomodasi</a>
                            </li>
                        </ul>
                        
                        <form class="d-flex align-items-center" role="search">
                            <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search" />
                            <button class="btn btn-outline-success me-3" type="submit">Search</button>

                            <?php if (isset($_SESSION['is_login']) && $_SESSION['is_login'] === true): ?>
                                <span class="navbar-text me-3 text-dark fw-bold">
                                    Halo, <?= htmlspecialchars($_SESSION['nama']); ?>
                                </span>
                                <a class="btn btn-danger" href="logout.php">Logout</a>
                            <?php else: ?>
                                <a class="btn btn-outline-primary me-2" href="register.php">Register</a>
                                <a class="btn btn-primary" href="login.php">Login</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </nav>
        </div>
    </div>
    
    <?php if (!empty($wisata_favorit)): ?>
    <div id="carouselExampleCaptions" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
        <div class="carousel-indicators">
            <?php foreach ($wisata_favorit as $index => $wisata) : ?>
                <button type="button" 
                        data-bs-target="#carouselExampleCaptions" 
                        data-bs-slide-to="<?php echo $index; ?>" 
                        class="<?php echo $index === 0 ? 'active' : ''; ?>" 
                        aria-current="<?php echo $index === 0 ? 'true' : 'false'; ?>" 
                        aria-label="Slide <?php echo $index + 1; ?>">
                </button>
            <?php endforeach; ?>
        </div>

        <div class="carousel-inner">
            <?php foreach ($wisata_favorit as $index => $wisata) : ?>
                <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                    <img src="<?php echo $wisata["img"]; ?>" class="d-block w-100" alt="<?php echo $wisata["title"]; ?>">
                    <div class="carousel-caption d-none d-md-block">
                        <h5><?php echo htmlspecialchars($wisata["title"]); ?></h5>
                        <p><?php echo htmlspecialchars($wisata["deskripsi"]); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
    <?php else: ?>
        <div class="alert alert-warning m-4 text-center">
            Belum ada data objek wisata di database atau tabel masih kosong.
        </div>
    <?php endif; ?>

    <div class="container my-5">
        <div class="row justify-content-center g-4">
            <?php foreach ($wisata_favorit as $wisata) : ?>
                <div class="col-auto">
                    <div class="card" style="width: 18rem;">
                        <img src="<?php echo $wisata["img"]; ?>" class="card-img-top" alt="<?php echo $wisata["title"]; ?>">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($wisata["title"]); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($wisata["deskripsi"]); ?></p>
                            <a href="#" class="btn btn-primary mt-auto">Lihat Selengkapnya</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-12">
            <footer class="bg-dark text-white pt-5 pb-4 mt-auto">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-4 col-lg-3 mb-4">
                            <h5 class="text-uppercase mb-4 fw-bold">DIGITARA MERDEKA BALI</h5>
                            <p class="text-secondary small">
                                Platform informasi wisata dan event terbaik di Bali. Temukan keindahan alam, budaya, 
                                dan pengalaman tak terlupakan bersama kami.
                            </p>
                            <div class="mt-3">
                                <a href="#" class="text-white me-2"><i class="bi bi-facebook"></i></a>
                                <a href="#" class="text-white me-2"><i class="bi bi-instagram"></i></a>
                                <a href="#" class="text-white me-2"><i class="bi bi-twitter"></i></a>
                                <a href="#" class="text-white"><i class="bi bi-youtube"></i></a>
                            </div>
                        </div>

                        <div class="col-md-4 col-lg-2 mb-4 offset-lg-1">
                            <h6 class="text-uppercase mb-4 fw-bold">Menu</h6>
                            <ul class="list-unstyled">
                                <li class="mb-2"><a href="#" class="text-secondary text-decoration-none">Home</a></li>
                                <li class="mb-2"><a href="#" class="text-secondary text-decoration-none">Event</a></li>
                                <li class="mb-2"><a href="#" class="text-secondary text-decoration-none">Artikel</a></li>
                                <li class="mb-2"><a href="#" class="text-secondary text-decoration-none">Tentang Kami</a></li>
                            </ul>
                        </div>

                        <div class="col-md-4 col-lg-2 mb-4">
                            <h6 class="text-uppercase mb-4 fw-bold">Kategori</h6>
                            <ul class="list-unstyled">
                                <li class="mb-2"><a href="#" class="text-secondary text-decoration-none">Gunung</a></li>
                                <li class="mb-2"><a href="#" class="text-secondary text-decoration-none">Pantai</a></li>
                                <li class="mb-2"><a href="#" class="text-secondary text-decoration-none">Kebun</a></li>
                                <li class="mb-2"><a href="#" class="text-secondary text-decoration-none">Event</a></li>
                            </ul>
                        </div>

                        <div class="col-md-6 col-lg-3 mb-4 offset-lg-1">
                            <h6 class="text-uppercase mb-4 fw-bold">Hubungi Kami</h6>
                            <ul class="list-unstyled">
                                <li class="mb-3 d-flex">
                                    <span class="me-2">📍</span>
                                    <span class="text-secondary small">Denpasar, Bali, Indonesia</span>
                                </li>
                                <li class="mb-3 d-flex">
                                    <span class="me-2">📧</span>
                                    <span class="text-secondary small">info@digidaw.com</span>
                                </li>
                                <li class="mb-3 d-flex">
                                    <span class="me-2">📞</span>
                                    <span class="text-secondary small">+62 123 4567 890</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="row mt-4 pt-3 border-top border-secondary">
                        <div class="col-12 text-center">
                            <p class="text-secondary small mb-0">
                                &copy; 2026 DIGITARA MERDEKA BALI. All rights reserved. Designed with JNSkuyyy
                            </p>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
</div>

<script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
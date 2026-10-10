<?php
// ==========================================
// 1. KONEKSI DATABASE
// ==========================================
$host = "localhost";
$dbname = "digitara";
$username = "root";
$password = "";

$conn = mysqli_connect($host, $username, $password, $dbname);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Pastikan folder 'img' ada untuk menampung gambar
if (!is_dir('img')) {
    mkdir('img');
}

// Ambil parameter aksi dari URL
$aksi = isset($_GET['aksi']) ? $_GET['aksi'] : '';
$id   = isset($_GET['id']) ? intval($_GET['id']) : 0;

// ==========================================
// 2. PROSES LOGIK (CREATE, UPDATE, DELETE)
// ==========================================

// --- PROSES TAMBAH (CREATE) ---
if (isset($_POST['simpan'])) {
    $title     = mysqli_real_escape_string($conn, $_POST['title_objek_wisata']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi_objek_wisata']);
    
    $gambar    = $_FILES['img_objek_wisata']['name'];
    $tmp_name  = $_FILES['img_objek_wisata']['tmp_name'];
    
    $ekstensi         = pathinfo($gambar, PATHINFO_EXTENSION);
    $nama_gambar_baru = time() . '_' . rand(100, 999) . '.' . $ekstensi;

    if (move_uploaded_file($tmp_name, "img/" . $nama_gambar_baru)) {
        $query = mysqli_query($conn, "INSERT INTO objek_wisata (title_objek_wisata, deskripsi_objek_wisata, img_objek_wisata) VALUES ('$title', '$deskripsi', '$nama_gambar_baru')");
        if ($query) {
            header("Location: " . $_SERVER['PHP_SELF'] . "?status=sukses-tambah");
            exit;
        }
    }
    header("Location: " . $_SERVER['PHP_SELF'] . "?status=gagal");
    exit;
}

// --- PROSES UBAH (UPDATE) ---
if (isset($_POST['ubah'])) {
    $title     = mysqli_real_escape_string($conn, $_POST['title_objek_wisata']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi_objek_wisata']);
    
    $gambar    = $_FILES['img_objek_wisata']['name'];
    $tmp_name  = $_FILES['img_objek_wisata']['tmp_name'];

    // Ambil info gambar lama
    $query_lama = mysqli_query($conn, "SELECT img_objek_wisata FROM objek_wisata WHERE id_objek_wisata = '$id'");
    $data_lama  = mysqli_fetch_assoc($query_lama);

    if ($gambar != "") {
        $ekstensi         = pathinfo($gambar, PATHINFO_EXTENSION);
        $nama_gambar_baru = time() . '_' . rand(100, 999) . '.' . $ekstensi;
        
        if (file_exists("img/" . $data_lama['img_objek_wisata']) && $data_lama['img_objek_wisata'] != "") {
            unlink("img/" . $data_lama['img_objek_wisata']);
        }
        
        move_uploaded_file($tmp_name, "img/" . $nama_gambar_baru);
        $query_update = "UPDATE objek_wisata SET title_objek_wisata='$title', deskripsi_objek_wisata='$deskripsi', img_objek_wisata='$nama_gambar_baru' WHERE id_objek_wisata='$id'";
    } else {
        $query_update = "UPDATE objek_wisata SET title_objek_wisata='$title', deskripsi_objek_wisata='$deskripsi' WHERE id_objek_wisata='$id'";
    }

    if (mysqli_query($conn, $query_update)) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?status=sukses-ubah");
        exit;
    }
    header("Location: " . $_SERVER['PHP_SELF'] . "?status=gagal");
    exit;
}

// --- PROSES HAPUS (DELETE) ---
if ($aksi == 'hapus' && $id > 0) {
    $query_gambar = mysqli_query($conn, "SELECT img_objek_wisata FROM objek_wisata WHERE id_objek_wisata = '$id'");
    $data_gambar  = mysqli_fetch_assoc($query_gambar);
    
    if (file_exists("img/" . $data_gambar['img_objek_wisata']) && $data_gambar['img_objek_wisata'] != "") {
        unlink("img/" . $data_gambar['img_objek_wisata']);
    }

    $query_hapus = mysqli_query($conn, "DELETE FROM objek_wisata WHERE id_objek_wisata = '$id'");
    if ($query_hapus) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?status=sukses-hapus");
        exit;
    }
    header("Location: " . $_SERVER['PHP_SELF'] . "?status=gagal");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Objek Wisata</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5 mb-5">
    
    <?php if (isset($_GET['status'])): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <?php
            if ($_GET['status'] == 'sukses-tambah') echo "Data objek wisata berhasil ditambahkan!";
            if ($_GET['status'] == 'sukses-ubah') echo "Perubahan data berhasil disimpan!";
            if ($_GET['status'] == 'sukses-hapus') echo "Data objek wisata berhasil dihapus!";
            if ($_GET['status'] == 'gagal') echo "Terjadi kesalahan sistem, operasi gagal.";
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($aksi == 'tambah'): ?>
        <div class="card shadow-sm mx-auto" style="max-width: 700px;">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Form Tambah Objek Wisata</h5>
            </div>
            <div class="card-body">
                <form action="<?= $_SERVER['PHP_SELF']; ?>" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Objek Wisata</label>
                        <input type="text" name="title_objek_wisata" class="form-control" required placeholder="Contoh: Pantai Pandawa">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Deskripsi</label>
                        <textarea name="deskripsi_objek_wisata" class="form-control" rows="5" required placeholder="Tuliskan cerita menarik atau informasi tempat ini..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Gambar Wisata</label>
                        <input type="file" name="img_objek_wisata" class="form-control" accept="image/*" required>
                    </div>
                    <div class="mt-4">
                        <button type="submit" name="simpan" class="btn btn-success">Simpan Wisata</button>
                        <a href="<?= $_SERVER['PHP_SELF']; ?>" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>

    <?php elseif ($aksi == 'ubah' && $id > 0): 
        $query_select = mysqli_query($conn, "SELECT * FROM objek_wisata WHERE id_objek_wisata = '$id'");
        $data = mysqli_fetch_assoc($query_select);
        if(!$data) { echo "<div class='alert alert-danger'>Data tidak ditemukan.</div>"; exit; }
    ?>
        <div class="card shadow-sm mx-auto" style="max-width: 700px;">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0">Form Ubah Objek Wisata</h5>
            </div>
            <div class="card-body">
                <form action="<?= $_SERVER['PHP_SELF']; ?>?id=<?= $id; ?>" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Objek Wisata</label>
                        <input type="text" name="title_objek_wisata" class="form-control" value="<?= $data['title_objek_wisata']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Deskripsi</label>
                        <textarea name="deskripsi_objek_wisata" class="form-control" rows="5" required><?= $data['deskripsi_objek_wisata']; ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold d-block">Gambar Saat Ini</label>
                        <img src="img/<?= $data['img_objek_wisata']; ?>" class="img-thumbnail mb-2" style="max-width: 200px;">
                        <input type="file" name="img_objek_wisata" class="form-control" accept="image/*">
                        <small class="text-muted">*Abaikan jika tidak ingin mengganti gambar utama.</small>
                    </div>
                    <div class="mt-4">
                        <button type="submit" name="ubah" class="btn btn-warning text-white">Simpan Perubahan</button>
                        <a href="<?= $_SERVER['PHP_SELF']; ?>" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>

    <?php else: ?>
        <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Daftar Wisata Digitara</h4>
            
            <div class="d-flex gap-2">
                <a href="index.php" class="btn btn-outline-light btn-sm">Kembali</a>
                <a href="<?= $_SERVER['PHP_SELF']; ?>?aksi=tambah" class="btn btn-light btn-sm fw-bold">+ Tambah Wisata</a>
            </div>
        </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th width="5%">No</th>
                                <th width="15%">Gambar</th>
                                <th width="25%">Nama Wisata</th>
                                <th width="40%">Deskripsi</th>
                                <th width="15%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            $query = mysqli_query($conn, "SELECT * FROM objek_wisata ORDER BY id_objek_wisata DESC");
                            if (mysqli_num_rows($query) > 0) {
                                while ($row = mysqli_fetch_assoc($query)) {
                            ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td>
                                    <?php if($row['img_objek_wisata'] != "" && file_exists("img/".$row['img_objek_wisata'])): ?>
                                        <img src="img/<?= $row['img_objek_wisata']; ?>" class="img-thumbnail" style="width: 100px; height: 70px; object-fit: cover;">
                                    <?php else: ?>
                                        <span class="badge bg-secondary">No Photo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold text-secondary"><?= htmlspecialchars($row['title_objek_wisata']); ?></td>
                                <td>
                                    <?= (strlen($row['deskripsi_objek_wisata']) > 120) ? htmlspecialchars(substr($row['deskripsi_objek_wisata'], 0, 120)) . '...' : htmlspecialchars($row['deskripsi_objek_wisata']); ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href="<?= $_SERVER['PHP_SELF']; ?>?aksi=ubah&id=<?= $row['id_objek_wisata']; ?>" class="btn btn-warning btn-sm text-white">Edit</a>
                                        <a href="<?= $_SERVER['PHP_SELF']; ?>?aksi=hapus&id=<?= $row['id_objek_wisata']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus permanen objek wisata ini?')">Hapus</a>
                                    </div>
                                </td>
                            </tr>
                            <?php 
                                }
                            } else {
                                echo "<tr><td colspan='5' class='text-center text-muted py-4'>Belum ada data objek wisata yang terdaftar.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
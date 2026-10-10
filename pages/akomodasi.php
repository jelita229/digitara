<?php
// ==========================================
// 1. KONEKSI DATABASE & INITIALIZATION
// ==========================================
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "localhost";
$dbname = "digitara";
$username = "root";
$password = "";

$conn = mysqli_connect($host, $username, $password, $dbname);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

if (!is_dir('img_akomodasi')) {
    mkdir('img_akomodasi');
}

$aksi = isset($_GET['aksi']) ? $_GET['aksi'] : '';
$id   = isset($_GET['id']) ? intval($_GET['id']) : 0;
$mode_bayar = false;
$data_invoice = [];

// ==========================================
// OTOMATISASI STRUKTUR TABEL PESANAN
// ==========================================
$query_table = "CREATE TABLE IF NOT EXISTS pesanan (
    id_pesanan INT AUTO_INCREMENT PRIMARY KEY,
    id_akomodasi INT NOT NULL,
    nama_tempat VARCHAR(100) NOT NULL,
    tipe_tempat VARCHAR(50) NOT NULL,
    nama_item VARCHAR(100) NOT NULL,
    harga INT NOT NULL,
    tanggal_pesan TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
mysqli_query($conn, $query_table);

$check_jumlah = mysqli_query($conn, "SHOW COLUMNS FROM pesanan LIKE 'jumlah'");
if (mysqli_num_rows($check_jumlah) == 0) {
    mysqli_query($conn, "ALTER TABLE pesanan ADD COLUMN jumlah INT NOT NULL DEFAULT 1 AFTER harga");
}

$check_durasi = mysqli_query($conn, "SHOW COLUMNS FROM pesanan LIKE 'durasi_hari'");
if (mysqli_num_rows($check_durasi) == 0) {
    mysqli_query($conn, "ALTER TABLE pesanan ADD COLUMN durasi_hari INT NOT NULL DEFAULT 1 AFTER jumlah");
}

$check_total = mysqli_query($conn, "SHOW COLUMNS FROM pesanan LIKE 'total_harga'");
if (mysqli_num_rows($check_total) == 0) {
    mysqli_query($conn, "ALTER TABLE pesanan ADD COLUMN total_harga INT NOT NULL DEFAULT 0 AFTER durasi_hari");
}

$check_metode = mysqli_query($conn, "SHOW COLUMNS FROM pesanan LIKE 'metode_pembayaran'");
if (mysqli_num_rows($check_metode) == 0) {
    mysqli_query($conn, "ALTER TABLE pesanan ADD COLUMN metode_pembayaran VARCHAR(50) NOT NULL DEFAULT 'Cash' AFTER total_harga");
}

$check_status = mysqli_query($conn, "SHOW COLUMNS FROM pesanan LIKE 'status_pembayaran'");
if (mysqli_num_rows($check_status) == 0) {
    mysqli_query($conn, "ALTER TABLE pesanan ADD COLUMN status_pembayaran VARCHAR(50) DEFAULT 'Pending' AFTER metode_pembayaran");
}

// ==========================================
// 2. PROSES LOGIK (CRUD DATA)
// ==========================================

if (isset($_POST['proses_pesan'])) {
    $mode_bayar = true;
    
    $harga = intval($_POST['harga_item']);
    $jumlah = isset($_POST['jumlah']) ? intval($_POST['jumlah']) : 1;
    $durasi_hari = isset($_POST['durasi_hari']) ? intval($_POST['durasi_hari']) : 1;
    $tipe_tempat = $_POST['tipe_tempat'];

    if ($jumlah < 1) $jumlah = 1;
    if ($durasi_hari < 1) $durasi_hari = 1;

    if ($tipe_tempat == 'Restoran') {
        $total_harga = $harga * $jumlah;
        $durasi_hari = 0;
    } else {
        $total_harga = $harga * $jumlah * $durasi_hari;
    }

    $data_invoice = [
        'id_akomodasi' => intval($_POST['id_akomodasi']),
        'nama_tempat'  => $_POST['nama_tempat'],
        'tipe_tempat'  => $tipe_tempat,
        'nama_item'    => $_POST['nama_item'],
        'harga'        => $harga,
        'jumlah'       => $jumlah,
        'durasi_hari'  => $durasi_hari,
        'total_harga'  => $total_harga
    ];
}

if (isset($_POST['konfirmasi_pembayaran'])) {
    $id_akomodasi   = intval($_POST['id_akomodasi']);
    $nama_tempat    = mysqli_real_escape_string($conn, $_POST['nama_tempat']);
    $tipe_tempat    = mysqli_real_escape_string($conn, $_POST['tipe_tempat']);
    $nama_item      = mysqli_real_escape_string($conn, $_POST['nama_item']);
    $harga          = intval($_POST['harga']);
    $jumlah         = intval($_POST['jumlah']);
    $durasi_hari    = intval($_POST['durasi_hari']);
    $total_harga    = intval($_POST['total_harga']);
    $metode_bayar   = mysqli_real_escape_string($conn, $_POST['metode_pembayaran']);
    $status_bayar   = 'Pesanan Berhasil'; 

    $query_text = "INSERT INTO pesanan (id_akomodasi, nama_tempat, tipe_tempat, nama_item, harga, jumlah, durasi_hari, total_harga, metode_pembayaran, status_pembayaran) 
                   VALUES ('$id_akomodasi', '$nama_tempat', '$tipe_tempat', '$nama_item', '$harga', '$jumlah', '$durasi_hari', '$total_harga', '$metode_bayar', '$status_bayar')";
    
    $query_pesan = mysqli_query($conn, $query_text);
    
    if ($query_pesan) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?status=pesan-sukses");
        exit;
    } else {
        header("Location: " . $_SERVER['PHP_SELF'] . "?status=gagal");
        exit;
    }
}

if (isset($_POST['simpan'])) {
    $nama      = mysqli_real_escape_string($conn, $_POST['nama_akomodasi']);
    $tipe      = mysqli_real_escape_string($conn, $_POST['tipe_akomodasi']); 
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi_akomodasi']);
    $kontak    = mysqli_real_escape_string($conn, $_POST['kontak_akomodasi']);
    
    $gambar    = $_FILES['img_akomodasi']['name'];
    $tmp_name  = $_FILES['img_akomodasi']['tmp_name'];
    $ekstensi  = pathinfo($gambar, PATHINFO_EXTENSION);
    $nama_gambar_baru = time() . '_' . rand(100, 999) . '.' . $ekstensi;

    if (move_uploaded_file($tmp_name, "img_akomodasi/" . $nama_gambar_baru)) {
        $query = mysqli_query($conn, "INSERT INTO akomodasi (nama_akomodasi, tipe_akomodasi, deskripsi_akomodasi, kontak_akomodasi, img_akomodasi) VALUES ('$nama', '$tipe', '$deskripsi', '$kontak', '$nama_gambar_baru')");
        if ($query) {
            header("Location: " . $_SERVER['PHP_SELF'] . "?status=sukses-tambah");
            exit;
        }
    }
    header("Location: " . $_SERVER['PHP_SELF'] . "?status=gagal");
    exit;
}

if (isset($_POST['simpan_kamar'])) {
    $id_akomodasi = intval($_POST['id_akomodasi']);
    $nama_kmr     = mysqli_real_escape_string($conn, $_POST['nama_kamar']);
    $harga_kmr    = intval($_POST['harga_kamar']);
    
    $gambar_kmr   = $_FILES['img_kamar']['name'];
    $tmp_name_kmr = $_FILES['img_kamar']['tmp_name'];
    $ekstensi_kmr = pathinfo($gambar_kmr, PATHINFO_EXTENSION);
    $nama_kamar_baru = time() . '_sub_' . rand(100, 999) . '.' . $ekstensi_kmr;

    if (move_uploaded_file($tmp_name_kmr, "img_akomodasi/" . $nama_kamar_baru)) {
        $query_kmr = mysqli_query($conn, "INSERT INTO kamar (id_akomodasi, nama_kamar, harga_kamar, img_kamar) VALUES ('$id_akomodasi', '$nama_kmr', '$harga_kmr', '$nama_kamar_baru')");
        if ($query_kmr) {
            header("Location: " . $_SERVER['PHP_SELF'] . "?aksi=detail&id=" . $id_akomodasi . "&status=sub-sukses");
            exit;
        }
    }
    header("Location: " . $_SERVER['PHP_SELF'] . "?aksi=detail&id=" . $id_akomodasi . "&status=gagal");
    exit;
}

if ($aksi == 'hapus_kamar') {
    $id_kamar = isset($_GET['id_kamar']) ? intval($_GET['id_kamar']) : 0;
    $query_gk = mysqli_query($conn, "SELECT img_kamar FROM kamar WHERE id_kamar = '$id_kamar'");
    $data_gk  = mysqli_fetch_assoc($query_gk);
    
    if (file_exists("img_akomodasi/" . $data_gk['img_kamar']) && $data_gk['img_kamar'] != "") {
        unlink("img_akomodasi/" . $data_gk['img_kamar']);
    }
    mysqli_query($conn, "DELETE FROM kamar WHERE id_kamar = '$id_kamar'");
    header("Location: " . $_SERVER['PHP_SELF'] . "?aksi=detail&id=" . $id . "&status=sub-hapus");
    exit;
}

if (isset($_POST['ubah'])) {
    $nama      = mysqli_real_escape_string($conn, $_POST['nama_akomodasi']);
    $tipe      = mysqli_real_escape_string($conn, $_POST['tipe_akomodasi']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi_akomodasi']);
    $kontak    = mysqli_real_escape_string($conn, $_POST['kontak_akomodasi']);

    $query_lama = mysqli_query($conn, "SELECT img_akomodasi FROM akomodasi WHERE id_akomodasi = '$id'");
    $data_lama  = mysqli_fetch_assoc($query_lama);

    if ($_FILES['img_akomodasi']['name'] != "") {
        $gambar   = $_FILES['img_akomodasi']['name'];
        $ekstensi = pathinfo($gambar, PATHINFO_EXTENSION);
        $nama_gambar_baru = time() . '_' . rand(100, 999) . '.' . $ekstensi;
        if (file_exists("img_akomodasi/" . $data_lama['img_akomodasi']) && $data_lama['img_akomodasi'] != "") {
            unlink("img_akomodasi/" . $data_lama['img_akomodasi']);
        }
        move_uploaded_file($_FILES['img_akomodasi']['tmp_name'], "img_akomodasi/" . $nama_gambar_baru);
        $query_update = "UPDATE akomodasi SET nama_akomodasi='$nama', tipe_akomodasi='$tipe', deskripsi_akomodasi='$deskripsi', kontak_akomodasi='$kontak', img_akomodasi='$nama_gambar_baru' WHERE id_akomodasi='$id'";
    } else {
        $query_update = "UPDATE akomodasi SET nama_akomodasi='$nama', tipe_akomodasi='$tipe', deskripsi_akomodasi='$deskripsi', kontak_akomodasi='$kontak' WHERE id_akomodasi='$id'";
    }

    if (mysqli_query($conn, $query_update)) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?status=sukses-ubah");
        exit;
    }
    header("Location: " . $_SERVER['PHP_SELF'] . "?status=gagal");
    exit;
}

if ($aksi == 'hapus' && $id > 0) {
    $qk = mysqli_query($conn, "SELECT img_kamar FROM kamar WHERE id_akomodasi = '$id'");
    while($rk = mysqli_fetch_assoc($qk)) {
        if (file_exists("img_akomodasi/" . $rk['img_kamar'])) unlink("img_akomodasi/" . $rk['img_kamar']);
    }
    $query_gambar = mysqli_query($conn, "SELECT img_akomodasi FROM akomodasi WHERE id_akomodasi = '$id'");
    $data_gambar  = mysqli_fetch_assoc($query_gambar);
    if (file_exists("img_akomodasi/" . $data_gambar['img_akomodasi'])) unlink("img_akomodasi/" . $data_gambar['img_akomodasi']);

    mysqli_query($conn, "DELETE FROM akomodasi WHERE id_akomodasi = '$id'");
    header("Location: " . $_SERVER['PHP_SELF'] . "?status=sukses-hapus");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Akomodasi & Kuliner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5 mb-5">
    
    <?php if (isset($_GET['status'])): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <?php
            if ($_GET['status'] == 'sukses-tambah') echo "Data tempat berhasil ditambahkan!";
            if ($_GET['status'] == 'sukses-ubah') echo "Perubahan data berhasil disimpan!";
            if ($_GET['status'] == 'sukses-hapus') echo "Data berhasil dihapus!";
            if ($_GET['status'] == 'sub-sukses') echo "Item baru berhasil ditambahkan!";
            if ($_GET['status'] == 'sub-hapus') echo "Item berhasil dihapus!";
            if ($_GET['status'] == 'gagal') echo "Terjadi kesalahan sistem.";
            if ($_GET['status'] == 'pesan-batal') echo "Pesanan telah dibatalkan.";
            if ($_GET['status'] == 'pesan-sukses') echo "<strong>Pesanan Anda Berhasil Di-proses!</strong>";
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($mode_bayar): ?>
        <!-- HALAMAN INVOICE PEMBAYARAN -->
        <div class="card shadow border-primary mx-auto" style="max-width: 650px;">
            <div class="card-header bg-primary text-white py-3">
                <h5 class="mb-0 text-center fw-bold">Halaman Pembayaran Digitara</h5>
            </div>
            <div class="card-body">
                <h5 class="fw-bold mb-3 border-bottom pb-2 text-secondary">Ringkasan Pesanan</h5>
                <table class="table table-borderless small mb-4">
                    <tr>
                        <td width="35%">Nama Tempat</td>
                        <td class="fw-bold">: <?= htmlspecialchars($data_invoice['nama_tempat']); ?> (<?= $data_invoice['tipe_tempat']; ?>)</td>
                    </tr>
                    <tr>
                        <td>Item / Menu</td>
                        <td class="fw-bold">: <?= htmlspecialchars($data_invoice['nama_item']); ?></td>
                    </tr>
                    <tr>
                        <td>Harga Satuan</td>
                        <td>: Rp <?= number_format($data_invoice['harga'], 0, ',', '.'); ?></td>
                    </tr>
                    <tr>
                        <td>Kuantitas</td>
                        <td>: <?= $data_invoice['jumlah']; ?> <?= ($data_invoice['tipe_tempat'] == 'Restoran') ? 'Porsi' : 'Kamar'; ?></td>
                    </tr>
                    <?php if($data_invoice['tipe_tempat'] != 'Restoran'): ?>
                    <tr>
                        <td>Durasi</td>
                        <td>: <?= $data_invoice['durasi_hari']; ?> Hari / Malam</td>
                    </tr>
                    <?php endif; ?>
                    <tr class="table-primary fs-5 fw-bold">
                        <td class="text-primary">Total Pembayaran</td>
                        <td class="text-danger">: Rp <?= number_format($data_invoice['total_harga'], 0, ',', '.'); ?></td>
                    </tr>
                </table>

                <form action="<?= $_SERVER['PHP_SELF']; ?>" method="POST">
                    <input type="hidden" name="id_akomodasi" value="<?= $data_invoice['id_akomodasi']; ?>">
                    <input type="hidden" name="nama_tempat" value="<?= $data_invoice['nama_tempat']; ?>">
                    <input type="hidden" name="tipe_tempat" value="<?= $data_invoice['tipe_tempat']; ?>">
                    <input type="hidden" name="nama_item" value="<?= $data_invoice['nama_item']; ?>">
                    <input type="hidden" name="harga" value="<?= $data_invoice['harga']; ?>">
                    <input type="hidden" name="jumlah" value="<?= $data_invoice['jumlah']; ?>">
                    <input type="hidden" name="durasi_hari" value="<?= $data_invoice['durasi_hari']; ?>">
                    <input type="hidden" name="total_harga" value="<?= $data_invoice['total_harga']; ?>">

                    <h5 class="fw-bold mb-3 border-bottom pb-2 text-secondary">Pilih Metode Pembayaran</h5>
                    <div class="row g-3 text-center mb-4">
                        <div class="col-4">
                            <input type="radio" class="btn-check" name="metode_pembayaran" id="pay_cash" value="Cash" checked onclick="switchPayment('box_cash')">
                            <label class="btn btn-outline-primary w-100 py-3 fw-bold" for="pay_cash">CASH</label>
                        </div>
                        <div class="col-4">
                            <input type="radio" class="btn-check" name="metode_pembayaran" id="pay_tf" value="Transfer Bank" onclick="switchPayment('box_tf')">
                            <label class="btn btn-outline-primary w-100 py-3 fw-bold" for="pay_tf">TRANSFER</label>
                        </div>
                        <div class="col-4">
                            <input type="radio" class="btn-check" name="metode_pembayaran" id="pay_qris" value="QRIS" onclick="switchPayment('box_qris')">
                            <label class="btn btn-outline-primary w-100 py-3 fw-bold" for="pay_qris">QRIS</label>
                        </div>
                    </div>

                    <div id="box_cash" class="payment-method-box p-3 bg-light border rounded text-center mb-4">
                        <p class="mb-0 text-muted">Silahkan lakukan pembayaran langsung secara tunai di lokasi sebesar <strong class="text-danger">Rp <?= number_format($data_invoice['total_harga'], 0, ',', '.'); ?></strong>.</p>
                    </div>

                    <div id="box_tf" class="payment-method-box p-3 bg-light border rounded mb-4 d-none">
                        <div class="p-2 bg-white border text-center font-monospace fw-bold fs-5 text-dark mb-2">BANK MANDIRI: 145-0012-9983-21</div>
                        <p class="mb-0 text-center text-secondary small">Atas Nama: <span class="fw-bold">PT. DIGITARA GLOBAL</span></p>
                    </div>

                    <div id="box_qris" class="payment-method-box p-3 bg-light border rounded text-center mb-4 d-none">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=170x170&data=DigitaraPay_<?= $data_invoice['total_harga']; ?>" class="img-thumbnail mx-auto d-block mb-2">
                        <span class="badge bg-danger">QRIS OTOMATIS</span>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <a href="<?= $_SERVER['PHP_SELF']; ?>?status=pesan-batal" class="btn btn-danger w-50 py-2 fw-bold">BATAL</a>
                        <button type="submit" name="konfirmasi_pembayaran" class="btn btn-success w-50 py-2 fw-bold">KONFIRMASI BAYAR</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function switchPayment(targetId) {
                document.querySelectorAll('.payment-method-box').forEach(box => box.classList.add('d-none'));
                document.getElementById(targetId).classList.remove('d-none');
            }
        </script>

    <?php elseif ($aksi == 'tambah'): ?>
        <!-- FORM TAMBAH -->
        <div class="card shadow-sm mx-auto" style="max-width: 700px;">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Form Tambah Tempat Baru</h5>
            </div>
            <div class="card-body">
                <form action="<?= $_SERVER['PHP_SELF']; ?>" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Tempat / Restoran / Hotel</label>
                        <input type="text" name="nama_akomodasi" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipe Tempat</label>
                        <select name="tipe_akomodasi" class="form-select" required>
                            <option value="" disabled selected>-- Pilih Tipe --</option>
                            <option value="Restoran">Restoran</option>
                            <option value="Hotel">Hotel</option>
                            <option value="Villa">Villa</option>
                            <option value="Penginapan">Penginapan/Homestay</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Deskripsi / Fasilitas Utama</label>
                        <textarea name="deskripsi_akomodasi" class="form-control" rows="4" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Hubungi / Alamat</label>
                        <input type="text" name="kontak_akomodasi" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Gambar Utama Tempat</label>
                        <input type="file" name="img_akomodasi" class="form-control" accept="image/*" required>
                    </div>
                    <div class="mt-4">
                        <button type="submit" name="simpan" class="btn btn-success">Simpan Data</button>
                        <a href="<?= $_SERVER['PHP_SELF']; ?>" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>

    <?php elseif ($aksi == 'ubah' && $id > 0): 
        $query_select = mysqli_query($conn, "SELECT * FROM akomodasi WHERE id_akomodasi = '$id'");
        $data = mysqli_fetch_assoc($query_select);
        if(!$data) { echo "<div class='alert alert-danger'>Data tidak ditemukan.</div>"; exit; }
    ?>
        <!-- FORM UBAH -->
        <div class="card shadow-sm mx-auto" style="max-width: 700px;">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0">Form Ubah Data</h5>
            </div>
            <div class="card-body">
                <form action="<?= $_SERVER['PHP_SELF']; ?>?id=<?= $id; ?>" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Tempat</label>
                        <input type="text" name="nama_akomodasi" class="form-control" value="<?= $data['nama_akomodasi']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipe Tempat</label>
                        <select name="tipe_akomodasi" class="form-select" required>
                            <option value="Restoran" <?= ($data['tipe_akomodasi'] == 'Restoran') ? 'selected' : ''; ?>>Restoran</option>
                            <option value="Hotel" <?= ($data['tipe_akomodasi'] == 'Hotel') ? 'selected' : ''; ?>>Hotel</option>
                            <option value="Villa" <?= ($data['tipe_akomodasi'] == 'Villa') ? 'selected' : ''; ?>>Villa</option>
                            <option value="Penginapan" <?= ($data['tipe_akomodasi'] == 'Penginapan') ? 'selected' : ''; ?>>Penginapan/Homestay</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Deskripsi</label>
                        <textarea name="deskripsi_akomodasi" class="form-control" rows="4" required><?= $data['deskripsi_akomodasi']; ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Hubungi / Alamat</label>
                        <input type="text" name="kontak_akomodasi" class="form-control" value="<?= $data['kontak_akomodasi']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold d-block">Gambar Saat Ini</label>
                        <img src="img_akomodasi/<?= $data['img_akomodasi']; ?>" class="img-thumbnail mb-2" style="max-width: 200px;">
                        <input type="file" name="img_akomodasi" class="form-control" accept="image/*">
                    </div>
                    <div class="mt-4">
                        <button type="submit" name="ubah" class="btn btn-warning text-white">Simpan Perubahan</button>
                        <a href="<?= $_SERVER['PHP_SELF']; ?>" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>

    <?php elseif ($aksi == 'detail' && $id > 0): 
        $query_detail = mysqli_query($conn, "SELECT * FROM akomodasi WHERE id_akomodasi = '$id'");
        $detail = mysqli_fetch_assoc($query_detail);
        if(!$detail) { echo "<div class='alert alert-danger'>Data tidak ditemukan.</div>"; exit; }
        
        $is_resto  = ($detail['tipe_akomodasi'] == 'Restoran');
        $label_sub = $is_resto ? 'Menu Kuliner' : 'Jenis Kamar';
        $label_nm  = $is_resto ? 'Nama Menu Makanan / Minuman' : 'Nama / Tipe Kamar';
        $label_hg  = $is_resto ? 'Harga Menu (Rp)' : 'Harga per Malam (Rp)';
        $label_ft  = $is_resto ? 'Foto Sajian Menu' : 'Foto Unit Kamar';
        $btn_label = $is_resto ? 'Pesan Menu' : 'Booking Kamar';
    ?>
        <!-- HALAMAN DETAIL SUB-ITEM -->
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card shadow-sm sticky-top" style="top: 20px;">
                    <img src="img_akomodasi/<?= $detail['img_akomodasi']; ?>" class="card-img-top" style="height: 200px; object-fit: cover;">
                    <div class="card-body">
                        <span class="badge bg-warning text-dark mb-2"><?= $detail['tipe_akomodasi']; ?></span>
                        <h4 class="fw-bold text-dark mb-2"><?= htmlspecialchars($detail['nama_akomodasi']); ?></h4>
                        <p class="text-muted small" style="white-space: pre-line;"><?= htmlspecialchars($detail['deskripsi_akomodasi']); ?></p>
                        <hr>
                        <p class="small text-secondary mb-0"><strong>Hubungi:</strong><br><?= htmlspecialchars($detail['kontak_akomodasi']); ?></p>
                        <div class="mt-3">
                            <a href="<?= $_SERVER['PHP_SELF']; ?>" class="btn btn-sm btn-secondary w-100">Kembali ke Daftar</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card shadow-sm mb-4 border-primary">
                    <div class="card-header bg-primary text-white py-2">
                        <h6 class="mb-0 fw-bold">+ Tambah Pilihan <?= $label_sub; ?> Baru</h6>
                    </div>
                    <div class="card-body py-3">
                        <form action="<?= $_SERVER['PHP_SELF']; ?>?aksi=detail&id=<?= $id; ?>" method="POST" enctype="multipart/form-data" class="row g-2 align-items-end">
                            <input type="hidden" name="id_akomodasi" value="<?= $id; ?>">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold mb-1"><?= $label_nm; ?></label>
                                <input type="text" name="nama_kamar" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold mb-1"><?= $label_hg; ?></label>
                                <input type="number" name="harga_kamar" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold mb-1"><?= $label_ft; ?></label>
                                <input type="file" name="img_kamar" class="form-control form-control-sm" accept="image/*" required>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" name="simpan_kamar" class="btn btn-sm btn-success w-100">Tambah</button>
                            </div>
                        </form>
                    </div>
                </div>

                <h5 class="fw-bold text-secondary mb-3">Daftar <?= $label_sub; ?> Terdaftar</h5>
                <div class="row g-3">
                    <?php 
                    $query_kamar = mysqli_query($conn, "SELECT * FROM kamar WHERE id_akomodasi = '$id' ORDER BY id_kamar DESC");
                    if(mysqli_num_rows($query_kamar) > 0) {
                        while($kamar = mysqli_fetch_assoc($query_kamar)) {
                    ?>
                        <div class="col-12">
                            <div class="card shadow-sm border-0 bg-white">
                                <div class="row g-0 align-items-center">
                                    <div class="col-sm-3">
                                        <img src="img_akomodasi/<?= $kamar['img_kamar']; ?>" class="img-fluid rounded-start" style="width: 100%; height: 120px; object-fit: cover;">
                                    </div>
                                    <div class="col-sm-9">
                                        <div class="card-body py-2">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($kamar['nama_kamar']); ?></h5>
                                                    <p class="text-danger fw-bold fs-5 mb-2">Rp <?= number_format($kamar['harga_kamar'], 0, ',', '.'); ?></p>
                                                </div>
                                                <a href="<?= $_SERVER['PHP_SELF']; ?>?aksi=hapus_kamar&id=<?= $id; ?>&id_kamar=<?= $kamar['id_kamar']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus item ini?')">Hapus</a>
                                            </div>

                                            <form action="<?= $_SERVER['PHP_SELF']; ?>" method="POST" class="row g-2 align-items-center bg-light p-2 rounded border">
                                                <input type="hidden" name="id_akomodasi" value="<?= $id; ?>">
                                                <input type="hidden" name="nama_tempat" value="<?= $detail['nama_akomodasi']; ?>">
                                                <input type="hidden" name="tipe_tempat" value="<?= $detail['tipe_akomodasi']; ?>">
                                                <input type="hidden" name="nama_item" value="<?= $kamar['nama_kamar']; ?>">
                                                <input type="hidden" name="harga_item" value="<?= $kamar['harga_kamar']; ?>">
                                                
                                                <?php if($is_resto): ?>
                                                    <div class="col-auto"><label class="small fw-bold text-muted">Jumlah Porsi:</label></div>
                                                    <div class="col-sm-3"><input type="number" name="jumlah" class="form-control form-control-sm text-center" value="1" min="1" required></div>
                                                <?php else: ?>
                                                    <div class="col-auto"><label class="small fw-bold text-muted">Jml Kamar:</label></div>
                                                    <div class="col-sm-2"><input type="number" name="jumlah" class="form-control form-control-sm text-center" value="1" min="1" required></div>
                                                    <div class="col-auto"><label class="small fw-bold text-muted">Durasi (Hari):</label></div>
                                                    <div class="col-sm-2"><input type="number" name="durasi_hari" class="form-control form-control-sm text-center" value="1" min="1" required></div>
                                                <?php endif; ?>

                                                <div class="col text-end">
                                                    <button type="submit" name="proses_pesan" class="btn btn-sm btn-primary px-3 fw-bold"><?= $btn_label; ?></button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php 
                        }
                    } else {
                        echo "<div class='col-12'><div class='alert alert-secondary text-center small mb-0'>Belum ada sub-item terdaftar untuk tempat ini.</div></div>";
                    }
                    ?>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- ======================================================== -->
        <!-- 3. HALAMAN UTAMA / DEFAULT VIEW (MENCEGAH LAYAR KOSONG)  -->
        <!-- ======================================================== -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-dark">Daftar Wisata, Hotel & Kuliner</h2>
            <a href="<?= $_SERVER['PHP_SELF']; ?>?aksi=tambah" class="btn btn-success fw-bold">+ Tambah Tempat Baru</a>
        </div>

        <div class="row g-4">
            <?php
            $query_tampil = mysqli_query($conn, "SELECT * FROM akomodasi ORDER BY id_akomodasi DESC");
            if (mysqli_num_rows($query_tampil) > 0) {
                while ($row = mysqli_fetch_assoc($query_tampil)) {
            ?>
                <div class="col-md-4">
                    <div class="card shadow-sm h-100">
                        <img src="img_akomodasi/<?= $row['img_akomodasi']; ?>" class="card-img-top" style="height: 190px; object-fit: cover;">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title fw-bold text-dark mb-0"><?= htmlspecialchars($row['nama_akomodasi']); ?></h5>
                                <span class="badge bg-warning text-dark"><?= $row['tipe_akomodasi']; ?></span>
                            </div>
                            <p class="card-text text-muted small flex-grow-1 text-truncate" style="max-height: 50px; overflow: hidden;">
                                <?= htmlspecialchars($row['deskripsi_akomodasi']); ?>
                            </p>
                            <p class="small text-secondary mb-3"><strong>Kontak: </strong><?= htmlspecialchars($row['kontak_akomodasi']); ?></p>
                            <div class="d-flex gap-2 mt-auto">
                                <a href="<?= $_SERVER['PHP_SELF']; ?>?aksi=detail&id=<?= $row['id_akomodasi']; ?>" class="btn btn-sm btn-primary flex-grow-1 fw-bold">Pilih & Kelola</a>
                                <a href="<?= $_SERVER['PHP_SELF']; ?>?aksi=ubah&id=<?= $row['id_akomodasi']; ?>" class="btn btn-sm btn-outline-warning">Ubah</a>
                                <a href="<?= $_SERVER['PHP_SELF']; ?>?aksi=hapus&id=<?= $row['id_akomodasi']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus tempat ini beserta seluruh item?')">Hapus</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php
                }
            } else {
                echo '<div class="col-12"><div class="alert alert-secondary text-center py-5">Belum ada data akomodasi atau kuliner. Silakan klik tombol "Tambah Tempat Baru" di atas.</div></div>';
            }
            ?>
        </div>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
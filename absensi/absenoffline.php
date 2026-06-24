<?php
include 'config.php';
is_login();

$pesan = "";
$warna = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nim = $_POST['nim'];
    $id_sesi = $_POST['id_sesi'];
    $waktu_hadir = $_POST['waktu_hadir'];
    $telat = $_POST['telat'];
    
    $cek = "SELECT * FROM kehadiran WHERE nim='$nim' AND id_sesi='$id_sesi'";
    $hasil_cek = mysqli_query($conn, $cek);
    
    if (mysqli_num_rows($hasil_cek) > 0) {
        $pesan = "Mahasiswa sudah absen di sesi ini!";
        $warna = "error";
    } else {
       
        $sql = "INSERT INTO kehadiran (nim, id_sesi, waktu_hadir, keterlambatan) 
                VALUES ('$nim', '$id_sesi', '$waktu_hadir', '$telat')";
        
        if (mysqli_query($conn, $sql)) {
            $pesan = "Absen berhasil disimpan!";
            $warna = "success";
        } else {
            $pesan = "Gagal menyimpan: " . mysqli_error($conn);
            $warna = "error";
        }
    }
}

$query_mhs = "SELECT * FROM mahasiswa ORDER BY nama";
$result_mhs = mysqli_query($conn, $query_mhs);

$query_sesi = "SELECT * FROM sesi ORDER BY hari_ke, id_sesi";
$result_sesi = mysqli_query($conn, $query_sesi);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Absen Offline - Manual Panitia</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <div class="header">
        <h1>📝 Absen Offline</h1>
        <p>Digunakan saat internet mati / mahasiswa tidak bisa akses link online</p>
        <p><a href="index.php">← Kembali ke Dashboard</a></p>
    </div>
    
    <div class="card">
        <h2>Form Absen Manual</h2>
        
        <?php if ($pesan != "") { ?>
            <div class="alert <?php echo $warna; ?>">
                <?php echo $pesan; ?>
            </div>
        <?php } ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Masukkan NIM Mahasiswa</label>
                <input type="text" name="nim" placeholder="Contoh: 220101001" required autocomplete="off" style="font-size: 18px; padding: 12px;">
            </div>
            
            <div class="form-group">
                <label>Pilih Sesi</label>
                <select name="id_sesi" required>
                    <option value="">-- Pilih Sesi --</option>
                    <?php while($sesi = mysqli_fetch_assoc($result_sesi)) { ?>
                        <option value="<?php echo $sesi['id_sesi']; ?>">
                            <?php echo $sesi['nama_sesi'] . " (Jam: " . $sesi['jam_mulai'] . ")"; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Waktu Kehadiran</label>
                <input type="time" name="waktu_hadir" value="<?php echo date('H:i'); ?>" >
                <small style="font-size: 11px; color: #666;">* ga wajib kalau tepat waktu</small>
            </div>
            
            <div class="form-group">
                <label>Keterlambatan (menit)</label>
                <select name="telat">
                    <option value="0">Tepat waktu (0 menit)</option>
                    <option value="1">Telat (diitung 1x)</option>
                </select>
                <small style="font-size: 11px; color: #666;">* Telat berapa menit pun tetap diitung 1x</small>
            </div>
            
            <button type="submit">💾 Simpan Absensi</button>
        </form>
    </div>
</div>
</body>
</html>
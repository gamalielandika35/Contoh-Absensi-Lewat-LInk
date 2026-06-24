<?php
include 'config.php';

$token = isset($_GET['token']) ? $_GET['token'] : '';
$kode_sesi = isset($_GET['sesi']) ? $_GET['sesi'] : '';

$sesiMap = [
    'H1' => 1, 
    'H2' => 3, 
    'H3' => 5,
    'H4' => 7, 
    'OUTBOND' => 9,
];

$status = 'info';
$pesan = 'Sistem Absen Otomatis';

if ($token != "" && $kode_sesi != "" && isset($sesiMap[$kode_sesi])) {
    $id_sesi = $sesiMap[$kode_sesi];
    
    $cari_mhs = "SELECT * FROM mahasiswa WHERE nim = '$token'";
    $hasil_mhs = mysqli_query($conn, $cari_mhs);
    $data_mhs = mysqli_fetch_assoc($hasil_mhs);
    
    if ($data_mhs) {
        $cek_absen = "SELECT * FROM kehadiran WHERE nim = '$token' AND id_sesi = '$id_sesi'";
        $hasil_cek = mysqli_query($conn, $cek_absen);
        
        if (mysqli_num_rows($hasil_cek) == 0) {
            $waktu_sekarang = date('Y-m-d H:i:s');
            
            $jam_sekarang = date('H:i:s');
            $jam_mulai = ($id_sesi % 2 == 1) ? '07:00:00' : '13:00:00';
            
            $status_telat = 0;
            if ($jam_sekarang > $jam_mulai) {
                $status_telat = 1;
            }
            
            $simpan = "INSERT INTO kehadiran (nim, id_sesi, waktu_hadir, keterlambatan) 
                       VALUES ('$token', '$id_sesi', '$waktu_sekarang', '$status_telat')";
            mysqli_query($conn, $simpan);
            
            $status = "success";
            $pesan = "✅ Halo " . $data_mhs['nama'] . "! Absen berhasil!";
            if ($status_telat == 1) {
                $pesan .= " (Terhitung telat 1x)";
            } else {
                $pesan .= " (Tepat waktu)";
            }
        } else {
            $status = "warning";
            $pesan = "⚠️ " . $data_mhs['nama'] . ", Anda sudah absen untuk sesi ini!";
        }
    } else {
        $status = "error";
        $pesan = "❌ NIM tidak valid! Hubungi panitia.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Absen Online</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <div class="card" style="text-align: center; max-width: 500px; margin: 50px auto;">
        <div class="alert <?php echo $status; ?>" style="font-size: 18px;">
            <?php echo $pesan; ?>
        </div>
        <p style="margin-top: 20px;">Terima kasih telah berpartisipasi dalam NSOP 2026</p>
    </div>
</div>
</body>
</html>
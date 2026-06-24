<?php
include 'config.php';
is_login();

$pesan = "";
$warna = "";

// cek apakah ada yang absen via link
$token = isset($_GET['token']) ? $_GET['token'] : '';
$kode_sesi = isset($_GET['sesi']) ? $_GET['sesi'] : '';

if ($token != "" && $kode_sesi != "") {
    $cari_sesi = "SELECT id_sesi, jam_mulai, nama_sesi FROM sesi WHERE kode_sesi = '$kode_sesi'";
    $hasil_sesi = mysqli_query($conn, $cari_sesi);
    $data_sesi = mysqli_fetch_assoc($hasil_sesi);
    
    if ($data_sesi) {
        $id_sesi = $data_sesi['id_sesi'];
        $jam_mulai = $data_sesi['jam_mulai'];
        $nama_sesi = $data_sesi['nama_sesi'];
        
        $cari_mhs = "SELECT * FROM mahasiswa WHERE nim = '$token'";
        $hasil_mhs = mysqli_query($conn, $cari_mhs);
        $data_mhs = mysqli_fetch_assoc($hasil_mhs);
        
        if ($data_mhs) {
            $cek_absen = "SELECT * FROM kehadiran WHERE nim = '$token' AND id_sesi = '$id_sesi'";
            $hasil_cek = mysqli_query($conn, $cek_absen);
            
            if (mysqli_num_rows($hasil_cek) == 0) {
                $waktu_sekarang = date('H:i:s');
                $telat = 0;
                
                if ($waktu_sekarang > $jam_mulai) {
                    $jam1 = strtotime($waktu_sekarang);
                    $jam2 = strtotime($jam_mulai);
                    $telat = floor(($jam1 - $jam2) / 60);
                    if ($telat > 60) $telat = 60;
                }
                
                $simpan = "INSERT INTO kehadiran (nim, id_sesi, waktu_hadir, keterlambatan_menit) 
                           VALUES ('$token', '$id_sesi', '$waktu_sekarang', '$telat')";
                mysqli_query($conn, $simpan);
                
                $pesan = "✅ Halo " . $data_mhs['nama'] . "! Absen " . $nama_sesi . " berhasil!";
                if ($telat > 0) {
                    $pesan .= " (Telat " . $telat . " menit)";
                } else {
                    $pesan .= " (Tepat waktu)";
                }
                $warna = "success";
            } else {
                $pesan = "⚠️ Anda sudah absen untuk sesi ini!";
                $warna = "error";
            }
        } else {
            $pesan = "❌ Token/NIM tidak valid!";
            $warna = "error";
        }
    } else {
        $pesan = "❌ Kode sesi tidak valid!";
        $warna = "error";
    }
}


$query_sesi = "SELECT * FROM sesi ORDER BY hari_ke, id_sesi";
$result_sesi = mysqli_query($conn, $query_sesi);

$query_mhs = "SELECT * FROM mahasiswa ORDER BY nama";
$result_mhs = mysqli_query($conn, $query_mhs);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Absen Online - Link Absensi</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <div class="header">
        <h1>🔗 Absen Online</h1>
        <p>Panitia copy link, share ke mahasiswa. Mahasiswa klik link langsung absen.</p>
        <p><a href="index.php">← Kembali ke Dashboard</a></p>
    </div>
    
    <?php if ($pesan != "") { ?>
        <div class="alert <?php echo $warna; ?>">
            <?php echo $pesan; ?>
        </div>
    <?php } ?>
    
    <div class="row">
        <div class="col">
            <div class="card">
                <h2>📌 Link per Sesi</h2>
                <p style="margin-bottom: 10px; color: #666;">Copy link template, ganti TOKEN_NIM dengan NIM mahasiswa</p>
                
                <?php 
                mysqli_data_seek($result_sesi, 0);
                while($sesi = mysqli_fetch_assoc($result_sesi)) { 
                    $link_template = "http://172.16.112.77/absensi/absenpublic.php?token=TOKEN_NIM&sesi=" . $sesi['kode_sesi'];
                ?>
                    <div class="link-item">
                        <strong><?php echo $sesi['nama_sesi']; ?></strong> (Jam: <?php echo $sesi['jam_mulai']; ?>)<br>
                        <code><?php echo $link_template; ?></code>
                        <button class="btn-copy" onclick="copyText('<?php echo $link_template; ?>')">Copy Template</button>
                    </div>
                <?php } ?>
            </div>
        </div>
        
        <div class="col">
            <div class="card">
                <h2>👥 Link per Mahasiswa</h2>
                <p style="margin-bottom: 10px; color: #666;">Pilih sesi, generate link khusus untuk mahasiswa</p>
                
                <div style="max-height: 500px; overflow-y: auto;">
                    <?php 
                    mysqli_data_seek($result_mhs, 0);
                    while($mhs = mysqli_fetch_assoc($result_mhs)) { 
                    ?>
                        <div class="link-item">
                            <strong><?php echo $mhs['nama']; ?></strong> (<?php echo $mhs['nim']; ?>)<br>
                            <select id="sesi_<?php echo $mhs['nim']; ?>" style="margin: 5px 0; width: 100%;">
                                <?php 
                                mysqli_data_seek($result_sesi, 0);
                                while($sesi = mysqli_fetch_assoc($result_sesi)) { ?>
                                    <option value="<?php echo $sesi['kode_sesi']; ?>">
                                        <?php echo $sesi['nama_sesi']; ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <button class="btn-copy" onclick="generateLink('<?php echo $mhs['nim']; ?>')">Generate & Copy Link</button>
                            <div id="hasil_<?php echo $mhs['nim']; ?>" style="margin-top: 5px; font-size: 11px;"></div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyText(text) {
    navigator.clipboard.writeText(text);
    alert("Link template disalin! Ganti TOKEN_NIM dengan NIM mahasiswa");
}

function generateLink(nim) {
    var sesiSelect = document.getElementById('sesi_' + nim);
    var kodeSesi = sesiSelect.value;
    var link = "http://172.16.112.77/absensi/absenpublic.php?token=" + nim + "&sesi=" + kodeSesi;
  
    prompt("Copy link ini:", link);
}
</script>
</body>
</html>
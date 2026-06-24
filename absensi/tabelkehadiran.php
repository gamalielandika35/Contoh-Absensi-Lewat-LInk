<?php
include 'config.php';
is_login();

$sesi_terpilih = isset($_GET['id_sesi']) ? $_GET['id_sesi'] : '';
$daftar_hadir = array();
$info_sesi = null;

if ($sesi_terpilih != "") {
    $query_info = "SELECT * FROM sesi WHERE id_sesi = '$sesi_terpilih'";
    $result_info = mysqli_query($conn, $query_info);
    $info_sesi = mysqli_fetch_assoc($result_info);
    $query_hadir = "SELECT k.*, m.nama, m.nim, m.prodi 
                    FROM kehadiran k
                    JOIN mahasiswa m ON k.nim = m.nim
                    WHERE k.id_sesi = '$sesi_terpilih'
                    ORDER BY m.nama";
    $result_hadir = mysqli_query($conn, $query_hadir);
    
    while ($row = mysqli_fetch_assoc($result_hadir)) {
        $daftar_hadir[] = $row;
    }
}
$query_sesi = "SELECT * FROM sesi ORDER BY hari_ke, id_sesi";
$result_sesi = mysqli_query($conn, $query_sesi);
$semua_sesi = array();
while ($row = mysqli_fetch_assoc($result_sesi)) {
    $semua_sesi[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tabel Kehadiran per Sesi</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <div class="header">
        <h1>📊 Tabel Kehadiran Per Sesi</h1>
        <p>Lihat siapa saja yang sudah dan belum absen di setiap sesi</p>
        <p><a href="index.php">← Kembali ke Dashboard</a></p>
    </div>
    
    <div class="card">
        <h2>Pilih Sesi</h2>
        <form method="GET">
            <div class="row">
                <div class="col">
                    <select name="id_sesi" class="form-group" style="width: 100%;">
                        <option value="">-- Pilih Sesi --</option>
                        <?php foreach($semua_sesi as $sesi) { ?>
                            <option value="<?php echo $sesi['id_sesi']; ?>" <?php echo ($sesi_terpilih == $sesi['id_sesi']) ? 'selected' : ''; ?>>
                                <?php echo $sesi['nama_sesi']; ?> (Jam: <?php echo $sesi['jam_mulai']; ?>)
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col">
                    <button type="submit">Lihat Tabel</button>
                </div>
            </div>
        </form>
    </div>
    
    <?php if ($sesi_terpilih != "" && $info_sesi != null) { ?>
        <div class="card">
            <h2>✅ Sudah Absen - <?php echo $info_sesi['nama_sesi']; ?></h2>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>NIM</th>
                            <th>Nama</th>
                            <th>Prodi</th>
                            <th>Jam Hadir</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if (count($daftar_hadir) > 0) {
                            $no = 1;
                            foreach($daftar_hadir as $row) { 
                                $status = ($row['keterlambatan'] == 0) 
                                    ? '<span class="badge-hadir">Tepat waktu</span>' 
                                    : '<span class="badge-telat">Telat ' . $row['keterlambatan'] . ' kali</span>';
                        ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo $row['nim']; ?></td>
                                <td><?php echo $row['nama']; ?></td>
                                <td><?php echo $row['prodi']; ?></td>
                                <td><?php echo $row['waktu_hadir']; ?></td>
                                <td><?php echo $status; ?></td>
                            </tr>
                        <?php } 
                        } else { ?>
                            <tr><td colspan="6" style="text-align: center;">Belum ada yang absen</td></tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
        

        <div class="card">
            <h2>⏳ Belum Absen - <?php echo $info_sesi['nama_sesi']; ?></h2>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>NIM</th>
                            <th>Nama</th>
                            <th>Prodi</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                  
                        $nim_hadir = array();
                        foreach($daftar_hadir as $row) {
                            $nim_hadir[] = $row['nim'];
                        }
                        
                      
                        $query_all_mhs = "SELECT * FROM mahasiswa ORDER BY nama";
                        $result_all_mhs = mysqli_query($conn, $query_all_mhs);
                        
                        $no = 1;
                        $ada_belum = false;
                        while($mhs = mysqli_fetch_assoc($result_all_mhs)) {
                            if (!in_array($mhs['nim'], $nim_hadir)) {
                                $ada_belum = true;
                        ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo $mhs['nim']; ?></td>
                                <td><?php echo $mhs['nama']; ?></td>
                                <td><?php echo $mhs['prodi']; ?></td>
                                <td><span class="badge-belum">Belum absen</span></td>
                            </tr>
                        <?php } 
                        }
                        
                        if (!$ada_belum) { ?>
                            <tr><td colspan="5" style="text-align: center;">✅ Semua mahasiswa sudah absen!</td></tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
        
    <?php } elseif ($sesi_terpilih != "") { ?>
        <div class="alert error">Sesi tidak ditemukan!</div>
    <?php } ?>
</div>
</body>
</html>
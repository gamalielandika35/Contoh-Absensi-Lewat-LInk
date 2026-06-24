<?php
include 'config.php';
is_login();
?>
<?php
$query_mhs = "SELECT * FROM mahasiswa ORDER BY nama";
$result_mhs = mysqli_query($conn, $query_mhs);
$semua_mahasiswa = array();
while($row = mysqli_fetch_assoc($result_mhs)) {
    $semua_mahasiswa[] = $row;
}

$query_sesi = "SELECT * FROM sesi ORDER BY hari_ke, id_sesi";
$result_sesi = mysqli_query($conn, $query_sesi);
$semua_sesi = array();
while($row = mysqli_fetch_assoc($result_sesi)) {
    $semua_sesi[] = $row;
}

$query_hadir = "SELECT * FROM kehadiran";
$result_hadir = mysqli_query($conn, $query_hadir);
$semua_hadir = array();
while($row = mysqli_fetch_assoc($result_hadir)) {
    $semua_hadir[] = $row;
}




$hadir_per_hari = array(1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0);
$mahasiswa_yang_hadir = array(1 => array(), 2 => array(), 3 => array(), 4 => array(), 5 => array());

foreach($semua_hadir as $hadir) {
  
    $hari = 0;
    foreach($semua_sesi as $sesi) {
        if($sesi['id_sesi'] == $hadir['id_sesi']) {
            $hari = $sesi['hari_ke'];
            break;
        }
    }
    
    if($hari > 0) {
        if(!in_array($hadir['nim'], $mahasiswa_yang_hadir[$hari])) {
            $mahasiswa_yang_hadir[$hari][] = $hadir['nim'];
            $hadir_per_hari[$hari]++;
        }
    }
}


$total = $hadir_per_hari[1] + $hadir_per_hari[2] + $hadir_per_hari[3] + $hadir_per_hari[4] + $hadir_per_hari[5];
$rata = round($total / 5, 1);


$max_hari = 1;
$min_hari = 1;
for($i = 2; $i <= 5; $i++) {
    if($hadir_per_hari[$i] > $hadir_per_hari[$max_hari]) $max_hari = $i;
    if($hadir_per_hari[$i] < $hadir_per_hari[$min_hari]) $min_hari = $i;
}

$telat_per_mhs = array();
foreach($semua_mahasiswa as $mhs) {
    $telat_per_mhs[$mhs['nim']] = array(
        'nama' => $mhs['nama'],
        'prodi' => $mhs['prodi'],
        'total_hadir' => 0,
        'total_telat' => 0
    );
}

foreach($semua_hadir as $hadir) {
    $telat_per_mhs[$hadir['nim']]['total_hadir']++;
    if($hadir['keterlambatan'] > 0) {
        $telat_per_mhs[$hadir['nim']]['total_telat']++;
    }
}
$mahasiswa_rajin = array();
$mahasiswa_telat = array();

foreach($telat_per_mhs as $nim => $data) {
    if($data['total_telat'] == 0) {
        $mahasiswa_rajin[] = $data;
    } else {
        $mahasiswa_telat[] = $data;
    }
}

for($i = 0; $i < count($mahasiswa_telat) - 1; $i++) {
    for($j = $i + 1; $j < count($mahasiswa_telat); $j++) {
        if($mahasiswa_telat[$i]['total_telat'] < $mahasiswa_telat[$j]['total_telat']) {
            $temp = $mahasiswa_telat[$i];
            $mahasiswa_telat[$i] = $mahasiswa_telat[$j];
            $mahasiswa_telat[$j] = $temp;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Absensi Event</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <div class="header">
        <h1>📋 Absensi NSOP</h1>
        <p>NSOP (4 hari) + Outbond (1 hari) | 20 Mahasiswa</p>
        <p style="margin-top: 10px;">Halo, <?php echo $_SESSION['username']; ?> | <a href="logout.php">Logout</a></p>
    </div>
    
    <div class="nav">
        <a href="absenonline.php">🔗 Absen Online (Link)</a>
        <a href="tabelkehadiran.php">📊 Tabel Kehadiran</a>
    </div>
    
    <div class="row">
        <div class="col">
            <div class="card">
                <h2>📌 Cara Absen Online</h2>
                <ol style="margin-left: 20px;">
                    <li>Masuk ke halaman <strong>Absen Online</strong></li>
                    <li>Panitia copy link sesuai sesi yang berlangsung</li>
                    <li>Share link ke WhatsApp mahasiswa</li>
                    <li>Mahasiswa klik link → otomatis absen</li>
                </ol>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <h2>📌 Lihat Tabel Kehadiran</h2>
                <ol style="margin-left: 20px;">
                    <li>Masuk ke halaman <strong>Tabel Kehadiran</strong></li>
                    <li>Pilih sesi yang ingin dilihat</li>
                    <li>Sistem menampilkan siapa saja yang sudah/belum absen</li>
                </ol>
            </div>
        </div>
    </div>
     <div class="row">
        <div class="col">
            <div class="card">
                <h2>📊 Rata-rata Kehadiran per Hari</h2>
                <div class="stat-box">
                    <div class="stat-angka"><?php echo $rata; ?> / 20</div>
                    <p>rata-rata mahasiswa per hari</p>
                </div>
                
                <table>
                    <thead>
                        <tr><th>Hari</th><th>Jumlah Hadir</th><th>Persen</th></tr>
                    </thead>
                    <tbody>
                        <tr><td>Hari 1</td><td><?php echo $hadir_per_hari[1]; ?> / 20</td><td><?php echo round(($hadir_per_hari[1]/20)*100,1); ?>%</td></tr>
                        <tr><td>Hari 2</td><td><?php echo $hadir_per_hari[2]; ?> / 20</td><td><?php echo round(($hadir_per_hari[2]/20)*100,1); ?>%</td></tr>
                        <tr><td>Hari 3</td><td><?php echo $hadir_per_hari[3]; ?> / 20</td><td><?php echo round(($hadir_per_hari[3]/20)*100,1); ?>%</td></tr>
                        <tr><td>Hari 4</td><td><?php echo $hadir_per_hari[4]; ?> / 20</td><td><?php echo round(($hadir_per_hari[4]/20)*100,1); ?>%</td></tr>
                        <tr><td>Hari 5 (Outbond)</td><td><?php echo $hadir_per_hari[5]; ?> / 20</td><td><?php echo round(($hadir_per_hari[5]/20)*100,1); ?>%</td></tr>
                    </tbody>
                </table>
                
                <br>
                <p><strong>✅ Paling banyak:</strong> Hari <?php echo $max_hari; ?> (<?php echo $hadir_per_hari[$max_hari]; ?> mahasiswa)</p>
                <p><strong>⚠️ Paling sedikit:</strong> Hari <?php echo $min_hari; ?> (<?php echo $hadir_per_hari[$min_hari]; ?> mahasiswa)</p>
            </div>
        </div>
         <div class="card">
                <h2>⚠️ Mahasiswa Sering Telat</h2>
                <p><em>Dihitung dari berapa kali telat</em></p>
                <table>
                    <thead>
                        <tr><th>No</th><th>Nama</th><th>Prodi</th><th>Hadir</th><th>Total Telat</th></tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        foreach($mahasiswa_telat as $m) {
                            echo "<tr>";
                            echo "<td>" . $no . "</td>";
                            echo "<td>" . $m['nama'] . "</td>";
                            echo "<td>" . $m['prodi'] . "</td>";
                            echo "<td>" . $m['total_hadir'] . "x</td>";
                            echo "<td><span class='badge-telat'>" . $m['total_telat'] . "x</span></td>";
                            echo "</tr>";
                            $no++;
                        }
                        if(count($mahasiswa_telat) == 0) {
                            echo "<tr><td colspan='5'>✅ Tidak ada mahasiswa yang telat</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        
        <div class="col">
            <div class="card">
                <h2>⭐ Mahasiswa Paling Rajin</h2>
                <p><em>Tidak pernah telat (0x)</em></p>
                <table>
                    <thead>
                        <tr><th>No</th><th>Nama</th><th>Prodi</th><th>Hadir</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        foreach($mahasiswa_rajin as $m) {
                            echo "<tr>";
                            echo "<td>" . $no . "</td>";
                            echo "<td>" . $m['nama'] . "</td>";
                            echo "<td>" . $m['prodi'] . "</td>";
                            echo "<td>" . $m['total_hadir'] . "x</td>";
                            echo "<td><span class='badge-hadir'>0x telat</span></td>";
                            echo "</tr>";
                            $no++;
                        }
                        if(count($mahasiswa_rajin) == 0) {
                            echo "<tr><td colspan='5'>Belum ada data</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            
           
        </div>
    </div>
</div>
</body>
</html>
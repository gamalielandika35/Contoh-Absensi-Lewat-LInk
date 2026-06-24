<?php

header("Content-Type: application/json");

include "config.php";

$data = json_decode(file_get_contents("php://input"), true);

if(!$data){
    echo json_encode([
        "success" => false,
        "message" => "Data kosong"
    ]);
    exit;
}

$nim = $data['nim'] ?? '';
$nama = $data['nama'] ?? '';
$prodi = $data['prodi'] ?? '';

$stmt = $conn->prepare("
INSERT INTO mahasiswa
(nim,nama,prodi)

VALUES(?,?,?)

ON DUPLICATE KEY UPDATE
nama = VALUES(nama),
prodi = VALUES(prodi)

");

$stmt->bind_param(
    "sss",
    $nim,
    $nama,
    $prodi
);

if($stmt->execute()){

    echo json_encode([
        "success" => true,
        "message" => "Sinkronisasi berhasil"
    ]);

}else{

    echo json_encode([
        "success" => false,
        "message" => "Sinkronisasi gagal"
    ]);

}
?>
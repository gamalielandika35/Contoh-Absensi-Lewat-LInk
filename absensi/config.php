<?php
session_start();

$host = "localhost";
$user = "root";
$pass = "";
$db = "absen_event";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

function is_login() {
    if (!isset($_SESSION['login']) || $_SESSION['login'] != true) {
        header("Location: login.php");
        exit();
    }
}
?>
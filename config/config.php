<?php
// Koneksi ke database 'foto'
$servername_foto = "localhost";
$username_foto = "root";
$password_foto = "";
$dbname_foto = "foto";

// Membuat koneksi
$conn_foto = mysqli_connect($servername_foto, $username_foto, $password_foto, $dbname_foto);

// Cek koneksi
if (!$conn_foto) {
  die("Connection failed: " . mysqli_connect_error());
}

// Koneksi ke database 'khitanumum'
$servername_ku = "localhost";
$username_ku = "root";
$password_ku = "";
$dbname_ku = "khitanumum";

// Membuat koneksi kedua
$conn_ku = mysqli_connect($servername_ku, $username_ku, $password_ku, $dbname_ku);

// Cek koneksi kedua
if (!$conn_ku) {
  die("Connection failed: " . mysqli_connect_error());
}

// Fungsi login untuk database 'foto'
function check_login()
{
  global $conn_foto;

  session_start();

  // Cek apakah user sudah login dengan session
  if (isset($_SESSION['user'])) {
    return true;
  }

  // Cek apakah user sudah login dengan cookie
  if (isset($_COOKIE['user_login'])) {
    list($username, $password) = explode(':', base64_decode($_COOKIE['user_login']));
    $username = mysqli_real_escape_string($conn_foto, $username);
    $password = mysqli_real_escape_string($conn_foto, $password);

    $stmt = $conn_foto->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
      $_SESSION['user'] = $user;
      return true;
    }
  }

  return false;
}

// Fungsi untuk mengambil nomor peserta dari database 'khitanumum'
function getNomorPeserta($conn_ku)
{
  $query = "SELECT no_peserta FROM pendaftar WHERE status_pendaftaran_id = 2";
  $result = mysqli_query($conn_ku, $query);

  $nomor_peserta = [];
  while ($row = mysqli_fetch_assoc($result)) {
    $nomor_peserta[] = $row['no_peserta'];
  }
  return $nomor_peserta;
}

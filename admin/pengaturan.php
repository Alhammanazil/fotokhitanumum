<?php
require '../config/config.php';

if (!check_login()) {
  header("Location: ../index.php");
  exit();
}

// Cek role
// if ($_SESSION['user']['role'] !== 'master') {
//   header("Location: dashboard.php");
//   exit();
// }

// Ambil data enum dari kolom role
$query = "SHOW COLUMNS FROM users LIKE 'role'";
$result = $conn_foto->query($query);
$row = $result->fetch_assoc();
preg_match("/^enum\(\'(.*)\'\)$/", $row['Type'], $matches);
$enum_values = explode("','", $matches[1]);

// Menangani pengiriman formulir untuk memperbarui tanggal
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['pendaftaran'])) {
    if (isset($_POST['dibuka']) && isset($_POST['ditutup'])) {
      $dibuka = date('Y-m-d H:i:s', strtotime($_POST['dibuka']));
      $ditutup = date('Y-m-d H:i:s', strtotime($_POST['ditutup']));

      // Menulis tanggal baru ke file dates_config.php
      $config_content = "<?php\n";
      $config_content .= "\$dibuka = \"$dibuka\";\n";
      $config_content .= "\$ditutup = \"$ditutup\";\n";
      $config_content .= "\$pelaksanaan = \"$pelaksanaan\";\n"; // Pastikan ini tetap ada
      $config_content .= "?>";

      file_put_contents('../config/dates_config.php', $config_content);
      echo "<script>alert('Tanggal pendaftaran telah diperbarui');</script>";
      // Muat ulang untuk memuat tanggal baru
      header("Refresh:0");
    }
  } elseif (isset($_POST['pelaksanaan'])) {
    if (isset($_POST['pelaksanaan'])) {
      $pelaksanaan = date('Y-m-d H:i:s', strtotime($_POST['pelaksanaan']));

      // Menulis tanggal pelaksanaan baru ke file dates_config.php
      $config_content = "<?php\n";
      $config_content .= "\$dibuka = \"$dibuka\";\n"; // Pastikan ini tetap ada
      $config_content .= "\$ditutup = \"$ditutup\";\n"; // Pastikan ini tetap ada
      $config_content .= "\$pelaksanaan = \"$pelaksanaan\";\n";

      file_put_contents('../config/dates_config.php', $config_content);
      echo "<script>alert('Tanggal pelaksanaan telah diperbarui');</script>";
      // Muat ulang untuk memuat tanggal baru
      header("Refresh:0");
    }
  }
}

// Mengambil semua pengguna
$sql = "SELECT * FROM users";
$hasil = $conn_foto->query($sql);

setlocale(LC_TIME, 'id_ID.UTF-8'); // Mengatur locale ke bahasa Indonesia

require_once 'header.php';
?>


<style>
  .date-range {
    background-color: black;
    color: white;
    padding: 5px;
    border-radius: 5px;
  }
</style>

<!-- Header -->
<div class="row justify-content-center bg-dark">
  <div class="col-ml text-center text-white my-2">
    <h3>Pengaturan Khitan Umum</h3>
    <h5>1446 H / 2024 TU</h5>
  </div>
</div>
<!-- Akhir Header -->

<div class="container">
  <div class="row justify-content-center my-4">
    <div class="col-12 col-md-4 mb-4 mb-md-0">
      <div class="card mx-auto" style="width: 75%;">
        <img src="../assets/profile.png" class="card-img-top" alt="gambar pengguna">
        <div class="card-body text-center">
          <h5 class="card-title">
            <?php
            if (isset($_SESSION['user'])) {
              echo $_SESSION['user']['nama_lengkap'];
            }
            ?>
          </h5>
        </div>
      </div>
    </div>
  </div>

    <!-- Data Admin -->
    <div class="table-responsive mt-3 mb-5">
      <h2 class="text-left">Data Admin</h2>
      <table id="usersTable" class="table table-bordered table-hover table-striped">
        <thead class="table-dark">
          <tr>
            <th class="text-center align-middle">No</th>
            <th class="text-center align-middle">Nama Lengkap</th>
            <th class="text-center align-middle">Username</th>
            <th class="text-center align-middle" style="min-width: 130px">Role</th>
            <th class="text-center align-middle">Akses</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $no = 1;
          while ($user = $hasil->fetch_assoc()) : ?>
            <tr>
              <td class="text-center align-middle"><?= $no; ?></td>
              <td class="text-center align-middle"><?= $user['nama_lengkap']; ?></td>
              <td class="text-center align-middle"><?= $user['username']; ?></td>
              <td class="text-center align-middle">
                <select name="role" class="form-select role-dropdown" data-id="<?= $user['id'] ?>">
                  <?php foreach ($enum_values as $value) : ?>
                    <option value="<?= $value ?>" <?= $user['role'] == $value ? 'selected' : '' ?>><?= ucfirst($value) ?></option>
                  <?php endforeach; ?>
                </select>
              </td>
              <td class="text-center align-middle">
                <div class="form-check form-switch">
                  <input class="form-check-input akses-toggle" type="checkbox" role="switch" id="akses-<?= $user['id'] ?>" <?= $user['akses'] ? 'checked' : '' ?> data-id="<?= $user['id'] ?>">
                </div>
              </td>
            </tr>
          <?php $no++;
          endwhile; ?>
        </tbody>
      </table>
    </div>
    <!-- Akhir Data Admin -->
  </div>



  <?php
  require_once 'footer.php';
  ?>
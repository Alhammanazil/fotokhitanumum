<?php
// Menentukan halaman aktif
$current_page = basename($_SERVER['PHP_SELF']);
$user_role = $_SESSION['user']['role'] ?? null;
?>

</div>
</div>
</div>

<nav class="navbar navbar-expand navbar-light navbar-bottom">
  <div class="container-fluid">
    <ul class="navbar-nav mx-auto">
      <!-- Home -->
      <li class="nav-item">
        <a class="nav-link <?= ($current_page == 'dashboard.php') ? 'active' : ''; ?>" href="dashboard.php">
          <i class="fas fa-home text-center d-block"></i>
          <span>Home</span>
        </a>
      </li>

      <!-- Pengaturan (Hanya untuk master) -->
      <li class="nav-item">
        <a class="nav-link <?= ($current_page == 'pengaturan.php') ? 'active' : ''; ?>" href="pengaturan.php">
          <i class="fas fa-cogs text-center d-block"></i>
          <span>Setting</span>
        </a>
      </li>

      <!-- Logout (Tampil untuk semua role) -->
      <li class="nav-item">
        <a class="nav-link" href="#" id="logout-link">
          <i class="fas fa-sign-out-alt text-center d-block"></i>
          <span>Logout</span>
        </a>
      </li>
    </ul>
  </div>
</nav>


<!-- jQuery Library -->
<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.12.4/dist/sweetalert2.all.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/2.1.2/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.1.2/js/dataTables.bootstrap5.js"></script>

<!-- Include DataTables JS -->
<script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
<!-- Include FixedColumns JS -->
<script src="https://cdn.datatables.net/fixedcolumns/3.3.0/js/dataTables.fixedColumns.min.js"></script>

<!-- Tambahan untuk modal -->
<!-- <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script> -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>

<!-- Font Awesome JS -->
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

<!-- JsBarcode -->
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

<!-- Halaman Dashboard -->
<script>
  $('#foto').DataTable({
    fixedColumns: true
  });
</script>

<script>
  function confirmDelete(id) {
    Swal.fire({
      title: 'Apakah Anda yakin?',
      text: "Anda tidak akan dapat mengembalikan data ini!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Ya, hapus!',
      cancelButtonText: 'Batal'
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = '../config/delete_foto.php?id=' + id;
      }
    })
  }
</script>

<script>
  document.addEventListener("DOMContentLoaded", function() {
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');

    if (status === 'deleted') {
      Swal.fire({
        icon: 'success',
        title: 'Data berhasil dihapus!',
        showConfirmButton: false,
        timer: 1500
      });
    } else if (status === 'failed') {
      Swal.fire({
        icon: 'error',
        title: 'Gagal menghapus data!',
        text: 'Silakan coba lagi.',
      });
    }
  });
</script>

<script>
  // Fungsi untuk generate pratinjau gambar dengan barcode
  function generatePreview(noPeserta, namaOperator, imagePath) {
    const canvas = document.getElementById('cardCanvas');
    const context = canvas.getContext('2d');

    // Gambar template ID Card
    const templateImg = new Image();
    templateImg.src = imagePath; // Gunakan path gambar yang dikirimkan dari PHP

    templateImg.onload = () => {
      canvas.width = templateImg.width;
      canvas.height = templateImg.height;
      context.drawImage(templateImg, 0, 0);

      // Nomor Peserta
      context.font = 'bold 14px Arial';
      context.fillStyle = '#000';
      context.fillText(noPeserta, 86, 263); // Posisi x, y yang sudah Anda sesuaikan

      // Nama Peserta
      context.font = 'bold 14px Arial';
      // Mengukur lebar teks dan menghitung posisi x agar berada di tengah
      const textWidth = context.measureText(namaOperator).width;
      const centerX = (canvas.width / 2) - (textWidth / 2);

      context.fillText(namaOperator, centerX, 280); // Menggambar nama di posisi tengah

      // Barcode
      const barcodeCanvas = document.createElement('canvas');
      JsBarcode(barcodeCanvas, noPeserta, {
        format: "CODE128",
        displayValue: false,
        width: 1,
        height: 10,
        margin: 0
      });
      context.drawImage(barcodeCanvas, 41.5, 296, 130, 30); // Sesuaikan posisi x, y, width, height

      // Tampilkan modal
      $('#previewModal').modal('show');
    }
  }

  // Reset canvas ketika modal ditutup
  $('#previewModal').on('hidden.bs.modal', function() {
    const canvas = document.getElementById('cardCanvas');
    const context = canvas.getContext('2d');
    context.clearRect(0, 0, canvas.width, canvas.height);
  });
</script>

<!-- Akhir Halaman Dashboard -->


<!-- Halaman Pengaturan -->
<script>
  $(document).ready(function() {
    // Mengubah role
    $('.role-dropdown').change(function() {
      var userId = $(this).data('id');
      var newRole = $(this).val();

      $.ajax({
        url: '../config/update_user.php',
        type: 'POST',
        data: {
          id: userId,
          type: 'role',
          value: newRole
        },
        success: function(response) {
          var result = JSON.parse(response);
          if (result.status === 'success') {
            Swal.fire({
              icon: 'success',
              title: 'Berhasil!',
              text: 'Role berhasil diperbarui.',
              timer: 2000,
              showConfirmButton: false
            });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Gagal!',
              text: 'Gagal memperbarui role: ' + result.message,
            });
          }
        },
        error: function() {
          Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Terjadi kesalahan saat memperbarui role.',
          });
        }
      });
    });

    // Mengubah akses
    $('.akses-toggle').change(function() {
      var userId = $(this).data('id');
      var newAkses = $(this).is(':checked') ? 1 : 0;

      $.ajax({
        url: '../config/update_user.php',
        type: 'POST',
        data: {
          id: userId,
          type: 'akses',
          value: newAkses
        },
        success: function(response) {
          var result = JSON.parse(response);
          if (result.status === 'success') {
            Swal.fire({
              icon: 'success',
              title: 'Berhasil!',
              text: 'Akses berhasil diperbarui.',
              timer: 2000,
              showConfirmButton: false
            });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Gagal!',
              text: 'Gagal memperbarui akses: ' + result.message,
            });
          }
        },
        error: function() {
          Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Terjadi kesalahan saat memperbarui akses.',
          });
        }
      });
    });
  });
</script>
<!-- Akhir Halaman Pengaturan -->

<!-- Logout -->
<script>
  document.getElementById('logout-link').addEventListener('click', function(event) {
    event.preventDefault(); // Mencegah link melakukan aksi default

    Swal.fire({
      title: 'Logout',
      text: "Anda akan keluar dari sesi saat ini.",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Ya, keluar',
      cancelButtonText: 'Batal'
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = '../config/logout.php';
      }
    });
  });
</script>

</body>

</html>
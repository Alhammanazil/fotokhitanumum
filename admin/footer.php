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
        <a class="nav-link" href="../config/logout.php" id="logout-link" onclick="return confirm('Apakah Anda yakin ingin keluar?')">
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

<!-- Include jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
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

<!-- Akhir Halaman Dashboard -->

</body>

</html>
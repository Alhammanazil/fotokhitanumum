<?php
require '../config/config.php';

if (!check_login()) {
    header("Location: ../index.php");
    exit();
}

// Cek role
if ($_SESSION['user']['role'] !== 'master' && $_SESSION['user']['role'] !== 'admin') {
    header("Location: dashboard.php"); // atau halaman lain yang sesuai
    exit();
}

// Ambil data foto dari database
$stmt = $conn_foto->prepare("SELECT id, no_peserta, nama_operator, file FROM foto ORDER BY created DESC");
$stmt->execute();
$result = $stmt->get_result();
$fotos = $result->fetch_all(MYSQLI_ASSOC);

require_once 'header.php';
?>

<!-- HTML untuk tampilan tabel -->
<div class="row justify-content-center bg-dark">
    <div class="col-ml text-center text-white my-2">
        <h3>DASHBOARD FOTO PESERTA</h3>
        <h5>Khitan Umum 1446 H / 2024 TU</h5>
    </div>
</div>

<div class="container">
    <div class="row justify-content-center align-middle">
        <div class="col">
            <a href="foto.php" class="btn btn-success my-4">+ Foto Peserta</a>
            <div class="table-responsive mb-5">
                <table id="foto" class="table table-striped table-bordered table-hover table-responsive" style="width:100%">
                    <thead class="table-dark">
                        <tr>
                            <th class="text-center align-middle">No</th>
                            <th class="text-center align-middle">Nomor Peserta</th>
                            <th class="text-center align-middle">Preview Foto</th>
                            <th class="text-center align-middle">Operator</th>
                            <th class="text-center align-middle">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        foreach ($fotos as $foto) :
                            // Path file gambar
                            $imagePath = "../foto/" . $foto['file'];
                            // Path placeholder jika file tidak ditemukan
                            $placeholderPath = "../assets/foto_tidak_tersedia.jpg";
                            // Gunakan path gambar atau placeholder jika file tidak ada
                            $displayImage = file_exists($imagePath) ? $imagePath : $placeholderPath;
                        ?>
                            <tr>
                                <td class="text-center align-middle"><?= $no; ?></td>
                                <td class="text-center align-middle"><?= $foto['no_peserta']; ?></td>
                                <td class="text-center align-middle">
                                    <a href="#" data-toggle="modal" data-target="#fotoModal<?= $foto['id']; ?>">
                                        <i class="fas fa-image fa-lg"></i>
                                    </a>
                                </td>
                                <td class="text-center align-middle"><?= $foto['nama_operator']; ?></td>
                                <td class="text-center align-middle">
                                    <a href="edit_foto.php?id=<?= $foto['id']; ?>" class="btn btn-warning">
                                        <i class="fas fa-edit icon-small"></i>
                                    </a>
                                    <a href="javascript:void(0);" class="btn btn-danger"
                                        onclick="confirmDelete('<?= $foto['id']; ?>')">
                                        <i class="fas fa-trash-alt icon-small"></i>
                                    </a>
                                </td>
                            </tr>

                            <!-- Modal untuk menampilkan gambar -->
                            <div class="modal fade" id="fotoModal<?= $foto['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="fotoModalLabel<?= $foto['id']; ?>" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="fotoModalLabel<?= $foto['id']; ?>">Preview Foto</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body text-center">
                                            <img src="<?= $displayImage; ?>" alt="Foto" class="img-fluid">
                                        </div>
                                    </div>
                                </div>
                            </div>

                        <?php
                            $no++;
                        endforeach;
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'footer.php';
?>
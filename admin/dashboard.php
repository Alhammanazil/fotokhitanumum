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
                                    <!-- Preview -->
                                    <a href="javascript:void(0);" class="btn btn-secondary"
                                        onclick="generatePreview('<?= $foto['no_peserta']; ?>', '<?= $foto['nama_operator']; ?>', '<?= $displayImage; ?>')">
                                        <i class="fa-solid fa-square-poll-horizontal"></i>
                                    </a>

                                    <!-- Edit -->
                                    <a href="edit_foto.php?id=<?= $foto['id']; ?>" class="btn btn-warning">
                                        <i class="fas fa-edit icon-small"></i>
                                    </a>

                                    <!-- Hapus -->
                                    <a href="javascript:void(0);" class="btn btn-danger"
                                        onclick="confirmDelete('<?= $foto['id']; ?>')">
                                        <i class="fas fa-trash-alt icon-small"></i>
                                    </a>
                                </td>
                            </tr>

                            <!-- Modal untuk pratinjau gambar -->
                            <div class="modal fade" id="previewModal" tabindex="-1" role="dialog" aria-labelledby="previewModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-lg" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="previewModalLabel">Pratinjau Kartu Peserta</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body text-center">
                                            <canvas id="cardCanvas" style="width: 100%; max-width: 400px;"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>

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

<!-- JsBarcode -->
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

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

<?php
require_once 'footer.php';
?>
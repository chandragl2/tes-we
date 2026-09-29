<?php
require_once __DIR__ . '/config/kuis_db.php';
include __DIR__ . '/views/header.php';

// 1. Update Nilai Peserta Ujian (Fitur Tambahan)
if (isset($_POST['update_nilai'])) {
    $id_peserta = (int)$_POST['id_peserta'];
    $nilai_baru = (float)$_POST['nilai'];
    
    mysqli_query($conn, "UPDATE PESERTA SET NILAI = $nilai_baru WHERE ID_PESERTA = $id_peserta");
    $pesan = "Nilai ujian berhasil diperbarui!";
}

// 2. Evaluasi Kelulusan Massal Berdasarkan KKM
if (isset($_POST['proses_kelulusan'])) {
    $kkm = (float)$_POST['kkm'];
    
    $query = "UPDATE PESERTA SET STATUS_LULUS = CASE 
                WHEN NILAI >= $kkm THEN 'LULUS' 
                ELSE 'TIDAK LULUS' 
              END";
    mysqli_query($conn, $query);
    $pesan = "Status kelulusan berhasil dievaluasi ulang berdasarkan KKM $kkm!";
}

// Tampil Data Peserta + Nama Siswa + Nama Ujian
$peserta = mysqli_query($conn, "
    SELECT p.ID_PESERTA, s.Nama AS NAMA_SISWA, u.NAMA_UJIAN, p.NILAI, p.STATUS_LULUS 
    FROM PESERTA p
    JOIN SISWA s ON p.NIS = s.NIS
    JOIN UJIAN u ON p.ID_UJIAN = u.ID_UJIAN
");
?>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white"><strong>Proses Penentuan Kelulusan & Update Nilai Peserta</strong></div>
    <div class="card-body">
        <?php if (isset($pesan)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $pesan ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Form Evaluasi KKM Massal -->
        <form method="POST" class="row g-3 align-items-center mb-4">
            <div class="col-auto">
                <label for="kkm" class="col-form-label">Patokan KKM Nilai Minimal:</label>
            </div>
            <div class="col-auto">
                <input type="number" step="0.1" name="kkm" value="70" class="form-control" required>
            </div>
            <div class="col-auto">
                <button type="submit" name="proses_kelulusan" class="btn btn-success">Jalankan Evaluasi Kelulusan</button>
            </div>
        </form>

        <!-- Tabel Peserta -->
        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>Nama Siswa</th>
                    <th>Nama Ujian</th>
                    <th>Nilai</th>
                    <th>Status Kelulusan</th>
                    <th width="120">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($p = mysqli_fetch_assoc($peserta)): ?>
                <tr>
                    <td><?= $p['NAMA_SISWA'] ?></td>
                    <td><?= $p['NAMA_UJIAN'] ?></td>
                    <td><strong><?= $p['NILAI'] ?></strong></td>
                    <td>
                        <span class="badge <?= $p['STATUS_LULUS'] == 'LULUS' ? 'bg-success' : ($p['STATUS_LULUS'] == 'TIDAK LULUS' ? 'bg-danger' : 'bg-warning') ?>">
                            <?= $p['STATUS_LULUS'] ?>
                        </span>
                    </td>
                    <td>
                        <!-- Tombol Edit Nilai -->
                        <button type="button" class="btn btn-warning btn-sm w-100" data-bs-toggle="modal" data-bs-target="#editNilaiModal<?= $p['ID_PESERTA'] ?>">
                            Edit Nilai
                        </button>
                    </td>
                </tr>

                <!-- Modal Edit Nilai -->
                <div class="modal fade" id="editNilaiModal<?= $p['ID_PESERTA'] ?>" tabindex="-1" aria-hidden="true">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title">Update Nilai Ujian</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <form method="POST">
                          <div class="modal-body">
                              <input type="hidden" name="id_peserta" value="<?= $p['ID_PESERTA'] ?>">
                              <div class="mb-3">
                                  <label class="form-label">Nama Siswa</label>
                                  <input type="text" class="form-control" value="<?= $p['NAMA_SISWA'] ?>" readonly>
                              </div>
                              <div class="mb-3">
                                  <label class="form-label">Nama Ujian</label>
                                  <input type="text" class="form-control" value="<?= $p['NAMA_UJIAN'] ?>" readonly>
                              </div>
                              <div class="mb-3">
                                  <label class="form-label">Nilai Baru</label>
                                  <input type="number" step="0.01" name="nilai" value="<?= $p['NILAI'] ?>" class="form-control" min="0" max="100" required>
                              </div>
                          </div>
                          <div class="modal-footer">
                              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                              <button type="submit" name="update_nilai" class="btn btn-warning">Simpan Nilai</button>
                          </div>
                      </form>
                    </div>
                  </div>
                </div>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/views/footer.php'; ?>
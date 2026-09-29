<?php
require_once __DIR__ . '/config/kuis_db.php';
include __DIR__ . '/views/header.php';

// 1. Create / Tambah Siswa (Otomatis Enroll ke Semua Ujian)
if (isset($_POST['tambah'])) {
    $nis    = mysqli_real_escape_string($conn, $_POST['nis']);
    $nama   = mysqli_real_escape_string($conn, $_POST['nama']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    
    // Cek duplikasi NIS
    $cek_nis = mysqli_query($conn, "SELECT NIS FROM SISWA WHERE NIS = '$nis'");
    if (mysqli_num_rows($cek_nis) > 0) {
        echo "<script>alert('Error: NIS $nis sudah terdaftar!'); window.location='siswa_crud.php';</script>";
        exit;
    }

    // A. Simpan data siswa baru ke tabel SISWA
    $query_siswa = "INSERT INTO SISWA (NIS, Nama, Alamat) VALUES ('$nis', '$nama', '$alamat')";
    if (mysqli_query($conn, $query_siswa)) {
        
        // B. Ambil semua ID_UJIAN yang ada di tabel UJIAN
        $get_ujian = mysqli_query($conn, "SELECT ID_UJIAN FROM UJIAN");
        
        // C. Daftarkan siswa baru ini ke tiap-tiap ujian dengan nilai default 0
        while ($u = mysqli_fetch_assoc($get_ujian)) {
            $id_ujian = $u['ID_UJIAN'];
            mysqli_query($conn, "INSERT INTO PESERTA (ID_UJIAN, NIS, NILAI, STATUS_LULUS) 
                                 VALUES ('$id_ujian', '$nis', 0.00, 'BELUM DIPROSES')");
        }
    }

    header("Location: siswa_crud.php");
    exit;
}

// 2. Create / Tambah Siswa
if (isset($_POST['tambah'])) {
    $nis    = mysqli_real_escape_string($conn, $_POST['nis']);
    $nama   = mysqli_real_escape_string($conn, $_POST['nama']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    
    mysqli_query($conn, "INSERT INTO SISWA (NIS, Nama, Alamat) VALUES ('$nis', '$nama', '$alamat')");
    header("Location: siswa_crud.php");
    exit;
}

// 3. Update / Ubah Biodata Siswa
if (isset($_POST['update_siswa'])) {
    $nis    = mysqli_real_escape_string($conn, $_POST['nis']);
    $nama   = mysqli_real_escape_string($conn, $_POST['nama']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    
    mysqli_query($conn, "UPDATE SISWA SET Nama='$nama', Alamat='$alamat' WHERE NIS='$nis'");
    header("Location: siswa_crud.php");
    exit;
}

// 4. Delete / Hapus Siswa
if (isset($_GET['hapus'])) {
    $nis = mysqli_real_escape_string($conn, $_GET['hapus']);
    mysqli_query($conn, "DELETE FROM SISWA WHERE NIS='$nis'");
    header("Location: siswa_crud.php");
    exit;
}

// 5. Read / Tampil Siswa
$result = mysqli_query($conn, "SELECT * FROM SISWA");
?>

<div class="row">
    <!-- Form Tambah Siswa -->
    <div class="col-md-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white"><strong>Tambah Siswa</strong></div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label>NIS</label>
                        <input type="text" name="nis" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Nama Siswa</label>
                        <input type="text" name="nama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Alamat</label>
                        <textarea name="alamat" class="form-control" required></textarea>
                    </div>
                    <button type="submit" name="tambah" class="btn btn-primary w-100">Simpan Data</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Tabel Daftar Siswa -->
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white"><strong>Daftar Siswa</strong></div>
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>NIS</th>
                            <th>Nama</th>
                            <th>Alamat</th>
                            <th width="150">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= $row['NIS'] ?></td>
                            <td><?= $row['Nama'] ?></td>
                            <td><?= $row['Alamat'] ?></td>
                            <td>
                                <!-- Tombol Edit Biodata -->
                                <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editSiswaModal<?= $row['NIS'] ?>">
                                    Edit
                                </button>
                                <!-- Tombol Hapus -->
                                <a href="siswa_crud.php?hapus=<?= $row['NIS'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus data siswa ini?')">Hapus</a>
                            </td>
                        </tr>

                        <!-- Modal Edit Biodata Siswa -->
                        <div class="modal fade" id="editSiswaModal<?= $row['NIS'] ?>" tabindex="-1" aria-hidden="true">
                          <div class="modal-dialog">
                            <div class="modal-content">
                              <div class="modal-header">
                                <h5 class="modal-title">Edit Biodata Siswa (NIS: <?= $row['NIS'] ?>)</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                              </div>
                              <form method="POST">
                                  <div class="modal-body">
                                      <input type="hidden" name="nis" value="<?= $row['NIS'] ?>">
                                      <div class="mb-3">
                                          <label class="form-label">Nama Siswa</label>
                                          <input type="text" name="nama" value="<?= $row['Nama'] ?>" class="form-control" required>
                                      </div>
                                      <div class="mb-3">
                                          <label class="form-label">Alamat</label>
                                          <textarea name="alamat" class="form-control" required><?= $row['Alamat'] ?></textarea>
                                      </div>
                                  </div>
                                  <div class="modal-footer">
                                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                      <button type="submit" name="update_siswa" class="btn btn-warning">Simpan Perubahan</button>
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
    </div>
</div>

<?php include __DIR__ . '/views/footer.php'; ?>
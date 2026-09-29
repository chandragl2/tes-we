<?php
// 1. Panggil file koneksi database agar variabel $conn tersedia
require_once 'config/kuis_db.php';

// 2. Panggil header layout
include 'views/header.php';

// 3a & 3b. Filter Ujian berdasarkan TANGGAL + Detail & Jumlah Peserta[cite: 1]
$filter_tgl = isset($_GET['tgl']) ? $_GET['tgl'] : '';
$query_ujian = "
    SELECT u.NAMA_UJIAN, mp.Nama_Matpel AS NAMA_MATPEL, u.TANGGAL, COUNT(p.NIS) AS JUMLAH_PESERTA
    FROM UJIAN u
    JOIN MATA_PELAJARAN mp ON u.ID_MATPEL = mp.ID_MATPEL
    LEFT JOIN PESERTA p ON u.ID_UJIAN = p.ID_UJIAN
";
if ($filter_tgl != '') {
    $query_ujian .= " WHERE DATE(u.TANGGAL) = '$filter_tgl'";
}
$query_ujian .= " GROUP BY u.ID_UJIAN, u.NAMA_UJIAN, mp.Nama_Matpel, u.TANGGAL";
$res_ujian = mysqli_query($conn, $query_ujian);

// 3c. Menampilkan Jumlah SISWA yang Lulus dalam SEMUA Ujian[cite: 1]
$res_lulus_semua = mysqli_query($conn, "
    SELECT COUNT(DISTINCT p.NIS) AS JUMLAH 
    FROM PESERTA p 
    WHERE p.NIS NOT IN (
        SELECT NIS FROM PESERTA WHERE STATUS_LULUS = 'TIDAK LULUS' OR STATUS_LULUS = 'BELUM DIPROSES'
    )
");
$jumlah_lulus_semua = mysqli_fetch_assoc($res_lulus_semua)['JUMLAH'];

// 3d. Menampilkan siswa yang tidak lulus dan gagal pada Mata pelajaran apa[cite: 1]
$res_tidak_lulus = mysqli_query($conn, "
    SELECT s.NIS, s.Nama AS NAMA_SISWA, mp.Nama_Matpel AS MATA_PELAJARAN, u.NAMA_UJIAN, p.NILAI
    FROM PESERTA p
    JOIN SISWA s ON p.NIS = s.NIS
    JOIN UJIAN u ON p.ID_UJIAN = u.ID_UJIAN
    JOIN MATA_PELAJARAN mp ON u.ID_MATPEL = mp.ID_MATPEL
    WHERE p.STATUS_LULUS = 'TIDAK LULUS'
");
?>

<div class="row mb-4">
    <!-- Poin 3c: Ringkasan Total Siswa Lulus Semua Ujian -->
    <div class="col-md-12 mb-4">
        <div class="card bg-success text-white shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Jumlah Siswa Lulus Dalam Semua Ujian (Soal 3c)</h5>
                <h2 class="display-5 fw-bold mb-0"><?= $jumlah_lulus_semua ?> Siswa</h2>
            </div>
        </div>
    </div>

    <!-- Poin 3a & 3b: Laporan Ujian & Peserta -->
    <div class="col-md-12 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>Data Ujian & Jumlah Peserta (Soal 3a & 3b)</strong>
                <form method="GET" class="d-flex align-items-center gap-2">
                    <input type="date" name="tgl" value="<?= $filter_tgl ?>" class="form-control form-control-sm">
                    <button type="submit" class="btn btn-sm btn-primary">Filter Tanggal</button>
                    <a href="laporan.php" class="btn btn-sm btn-secondary">Reset</a>
                </form>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Nama Ujian</th>
                            <th>Mata Pelajaran</th>
                            <th>Tanggal Ujian</th>
                            <th>Jumlah Peserta</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($u = mysqli_fetch_assoc($res_ujian)): ?>
                        <tr>
                            <td><?= $u['NAMA_UJIAN'] ?></td>
                            <td><?= $u['NAMA_MATPEL'] ?></td>
                            <td><?= $u['TANGGAL'] ?></td>
                            <td><span class="badge bg-info text-dark"><?= $u['JUMLAH_PESERTA'] ?> Orang</span></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Poin 3d: Siswa Tidak Lulus -->
    <div class="col-md-12">
        <div class="card shadow-sm border-danger">
            <div class="card-header bg-danger text-white">
                <strong>Daftar Siswa Tidak Lulus & Mata Pelajaran yang Gagal (Soal 3d)</strong>
            </div>
            <div class="card-body p-0">
                <table class="table table-bordered mb-0">
                    <thead>
                        <tr>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Mata Pelajaran Gagal</th>
                            <th>Nama Ujian</th>
                            <th>Nilai Ujian</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($res_tidak_lulus) > 0): ?>
                            <?php while ($tl = mysqli_fetch_assoc($res_tidak_lulus)): ?>
                            <tr>
                                <td><?= $tl['NIS'] ?></td>
                                <td><?= $tl['NAMA_SISWA'] ?></td>
                                <td class="text-danger fw-bold"><?= $tl['MATA_PELAJARAN'] ?></td>
                                <td><?= $tl['NAMA_UJIAN'] ?></td>
                                <td><span class="badge bg-danger"><?= $tl['NILAI'] ?></span></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center text-muted p-3">Tidak ada data siswa tidak lulus.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'views/footer.php'; ?>
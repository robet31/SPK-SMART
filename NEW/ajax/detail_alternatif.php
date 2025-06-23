<?php
require_once '../config/database.php';
require_once '../classes/SMART.php';

$database = new Database();
$db = $database->getConnection();
$smart = new SMART($db);

$id_alternatif = $_GET['id'] ?? '';
$metode = $_GET['metode'] ?? 'manual';

if (empty($id_alternatif)) {
    echo "ID Alternatif tidak valid";
    exit;
}

// Ambil data alternatif
$query = "SELECT * FROM alternatif WHERE id_alternatif = ?";
$stmt = $db->prepare($query);
$stmt->execute([$id_alternatif]);
$alternatif = $stmt->fetch(PDO::FETCH_ASSOC);

// Ambil nilai kriteria
$query = "SELECT na.*, k.nama_kriteria, k.jenis_kriteria, k.satuan 
          FROM nilai_alternatif na 
          JOIN kriteria k ON na.id_kriteria = k.id_kriteria 
          WHERE na.id_alternatif = ? 
          ORDER BY na.id_kriteria";
$stmt = $db->prepare($query);
$stmt->execute([$id_alternatif]);
$nilai_kriteria = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil hasil perhitungan
$query = "SELECT * FROM hasil_smart WHERE id_alternatif = ? AND metode_pembobotan = ?";
$stmt = $db->prepare($query);
$stmt->execute([$id_alternatif, $metode]);
$hasil = $stmt->fetch(PDO::FETCH_ASSOC);

// Ambil detail perhitungan
$detail = $smart->getDetailPerhitungan($metode);
$data_normalisasi = $detail['data_normalisasi'];
$bobot = $detail['bobot'];
?>

<div class="row">
    <div class="col-md-6">
        <h5 class="text-primary"><?= $alternatif['nama_alternatif'] ?></h5>
        <p class="text-muted"><?= $alternatif['deskripsi'] ?></p>
        
        <?php if ($hasil): ?>
        <div class="alert alert-info">
            <strong>Ranking:</strong> <?= $hasil['ranking'] ?><br>
            <strong>Skor Akhir:</strong> <?= number_format($hasil['skor_akhir'], 6) ?><br>
            <strong>Metode:</strong> <?= ucfirst($hasil['metode_pembobotan']) ?>
        </div>
        <?php endif; ?>
    </div>
    <div class="col-md-6">
        <canvas id="detailChart" width="300" height="300"></canvas>
    </div>
</div>

<div class="table-responsive mt-3">
    <table class="table table-striped table-sm">
        <thead>
            <tr>
                <th>Kriteria</th>
                <th>Nilai Asli</th>
                <th>Nilai Normalisasi</th>
                <th>Bobot</th>
                <th>Kontribusi Skor</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($nilai_kriteria as $nk): ?>
                <tr>
                    <td>
                        <strong><?= $nk['id_kriteria'] ?></strong><br>
                        <small class="text-muted"><?= $nk['nama_kriteria'] ?></small>
                    </td>
                    <td>
                        <?= number_format($nk['nilai'], 2) ?> <?= $nk['satuan'] ?>
                        <?php if($nk['jenis_kriteria'] == 'benefit'): ?>
                            <i class="fas fa-arrow-up text-success ms-1"></i>
                        <?php else: ?>
                            <i class="fas fa-arrow-down text-danger ms-1"></i>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?= isset($data_normalisasi[$id_alternatif][$nk['id_kriteria']]) ? 
                            number_format($data_normalisasi[$id_alternatif][$nk['id_kriteria']], 4) : '-' ?>
                    </td>
                    <td>
                        <?= isset($bobot[$nk['id_kriteria']]) ? 
                            number_format($bobot[$nk['id_kriteria']], 6) : '-' ?>
                    </td>
                    <td>
                        <?php 
                        if (isset($data_normalisasi[$id_alternatif][$nk['id_kriteria']]) && isset($bobot[$nk['id_kriteria']])) {
                            $kontribusi = $data_normalisasi[$id_alternatif][$nk['id_kriteria']] * $bobot[$nk['id_kriteria']];
                            echo number_format($kontribusi, 6);
                        } else {
                            echo '-';
                        }
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
// Chart kontribusi kriteria
const detailCtx = document.getElementById('detailChart').getContext('2d');
const detailChart = new Chart(detailCtx, {
    type: 'doughnut',
    data: {
        labels: [<?php foreach($nilai_kriteria as $nk): ?>'<?= $nk['id_kriteria'] ?>',<?php endforeach; ?>],
        datasets: [{
            data: [
                <?php foreach($nilai_kriteria as $nk): ?>
                    <?php 
                    if (isset($data_normalisasi[$id_alternatif][$nk['id_kriteria']]) && isset($bobot[$nk['id_kriteria']])) {
                        echo $data_normalisasi[$id_alternatif][$nk['id_kriteria']] * $bobot[$nk['id_kriteria']];
                    } else {
                        echo 0;
                    }
                    ?>,
                <?php endforeach; ?>
            ],
            backgroundColor: [
                '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40',
                '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0'
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom',
                labels: { font: { size: 10 } }
            },
            title: {
                display: true,
                text: 'Kontribusi Setiap Kriteria'
            }
        }
    }
});
</script>

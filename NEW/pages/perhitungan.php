<?php
$kriteria = $smart->getKriteria();
$alternatif = $smart->getAlternatif();

// Ambil metode yang dipilih
$metode_terpilih = isset($_GET['metode']) ? $_GET['metode'] : 'manual';

// Ambil detail perhitungan
$detail = $smart->getDetailPerhitungan($metode_terpilih);
$data_asli = $detail['data_asli'];
$data_normalisasi = $detail['data_normalisasi'];
$bobot = $detail['bobot'];

// Hitung skor akhir jika ada bobot
$hasil_perhitungan = [];
if (!empty($bobot)) {
    $hasil_perhitungan = $smart->hitungSkorSMART($metode_terpilih);
}
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="row">
    <div class="col-12">
        <h1 class="mb-4">
            <i class="fas fa-chart-line text-primary me-3"></i>
            Perhitungan SMART
        </h1>
        <p class="lead text-muted">Proses step-by-step perhitungan metode SMART untuk ranking alternatif</p>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="mb-0">Pilih Metode Pembobotan:</h5>
                    </div>
                    <div class="col-md-6">
                        <div class="btn-group w-100" role="group">
                            <a href="index.php?page=perhitungan&metode=manual" 
                               class="btn <?= $metode_terpilih == 'manual' ? 'btn-primary' : 'btn-outline-primary' ?>">
                                <i class="fas fa-edit me-1"></i>Bobot Manual
                            </a>
                            <a href="index.php?page=perhitungan&metode=roc" 
                               class="btn <?= $metode_terpilih == 'roc' ? 'btn-primary' : 'btn-outline-primary' ?>">
                                <i class="fas fa-calculator me-1"></i>Bobot ROC
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (empty($bobot)): ?>
<div class="row">
    <div class="col-12">
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Peringatan!</strong> Bobot untuk metode <?= ucfirst($metode_terpilih) ?> belum diatur. 
            Silakan atur bobot terlebih dahulu di menu 
            <?php if ($metode_terpilih == 'manual'): ?>
                <a href="index.php?page=bobot" class="alert-link">Input Bobot</a>
            <?php else: ?>
                <a href="index.php?page=roc" class="alert-link">Bobot ROC</a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php else: ?>

<!-- Step 1: Data Asli -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <span class="step-indicator">1</span>
                    Data Asli (Matrix Keputusan)
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>SMD</th>
                                <?php foreach($kriteria as $k): ?>
                                    <th class="text-center"><?= $k['id_kriteria'] ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach(array_slice($alternatif, 0, 10) as $alt): ?>
                                <tr>
                                    <td><strong><?= $alt['id_alternatif'] ?></strong></td>
                                    <?php foreach($kriteria as $k): ?>
                                        <td class="text-center">
                                            <?= isset($data_asli[$alt['id_alternatif']][$k['id_kriteria']]) ? 
                                                number_format($data_asli[$alt['id_alternatif']][$k['id_kriteria']], 2) : '-' ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <small class="text-muted">Menampilkan 10 data pertama dari <?= count($alternatif) ?> total alternatif</small>
            </div>
        </div>
    </div>
</div>

<!-- Step 2: Bobot Kriteria -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <span class="step-indicator">2</span>
                    Bobot Kriteria (Metode: <?= ucfirst($metode_terpilih) ?>)
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Kriteria</th>
                                        <th>Nama Kriteria</th>
                                        <th>Jenis</th>
                                        <th>Bobot</th>
                                        <th>Persentase</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($kriteria as $k): ?>
                                        <tr>
                                            <td><strong><?= $k['id_kriteria'] ?></strong></td>
                                            <td><?= $k['nama_kriteria'] ?></td>
                                            <td>
                                                <?php if($k['jenis_kriteria'] == 'benefit'): ?>
                                                    <span class="badge bg-success">Benefit</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Cost</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= number_format($bobot[$k['id_kriteria']], 6) ?></td>
                                            <td><?= number_format($bobot[$k['id_kriteria']] * 100, 2) ?>%</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="table-info">
                                        <th colspan="3">Total</th>
                                        <th><?= number_format(array_sum($bobot), 6) ?></th>
                                        <th>100.00%</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <canvas id="bobotChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Step 3: Normalisasi -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <span class="step-indicator">3</span>
                    Normalisasi Data
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <strong>Formula Normalisasi SMART:</strong><br>
                    • Benefit: (x - min) / (max - min)<br>
                    • Cost: (max - x) / (max - min)
                </div>
                
                <div class="table-responsive">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>SMD</th>
                                <?php foreach($kriteria as $k): ?>
                                    <th class="text-center">
                                        <?= $k['id_kriteria'] ?>
                                        <?php if($k['jenis_kriteria'] == 'benefit'): ?>
                                            <i class="fas fa-arrow-up text-success"></i>
                                        <?php else: ?>
                                            <i class="fas fa-arrow-down text-danger"></i>
                                        <?php endif; ?>
                                    </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach(array_slice($alternatif, 0, 10) as $alt): ?>
                                <tr>
                                    <td><strong><?= $alt['id_alternatif'] ?></strong></td>
                                    <?php foreach($kriteria as $k): ?>
                                        <td class="text-center">
                                            <?= isset($data_normalisasi[$alt['id_alternatif']][$k['id_kriteria']]) ? 
                                                number_format($data_normalisasi[$alt['id_alternatif']][$k['id_kriteria']], 4) : '-' ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <small class="text-muted">Menampilkan 10 data pertama dari <?= count($alternatif) ?> total alternatif</small>
            </div>
        </div>
    </div>
</div>

<!-- Step 4: Perhitungan Skor Akhir -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <span class="step-indicator">4</span>
                    Perhitungan Skor Akhir
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <strong>Formula Skor Akhir:</strong> S<sub>i</sub> = Σ(w<sub>j</sub> × n<sub>ij</sub>)<br>
                    dimana: w<sub>j</sub> = bobot kriteria j, n<sub>ij</sub> = nilai normalisasi alternatif i pada kriteria j
                </div>
                
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Ranking</th>
                                <th>SMD</th>
                                <th>Perhitungan Detail</th>
                                <th>Skor Akhir</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach(array_slice($hasil_perhitungan, 0, 10) as $hasil): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-primary fs-6"><?= $hasil['ranking'] ?></span>
                                    </td>
                                    <td><strong><?= $hasil['id_alternatif'] ?></strong></td>
                                    <td class="small">
                                        <?php 
                                        $detail_perhitungan = [];
                                        foreach(array_slice($kriteria, 0, 3) as $k) {
                                            $nilai_norm = $data_normalisasi[$hasil['id_alternatif']][$k['id_kriteria']];
                                            $bobot_k = $bobot[$k['id_kriteria']];
                                            $detail_perhitungan[] = number_format($bobot_k, 3) . "×" . number_format($nilai_norm, 3);
                                        }
                                        echo implode(" + ", $detail_perhitungan) . " + ...";
                                        ?>
                                    </td>
                                    <td>
                                        <strong class="text-primary"><?= number_format($hasil['skor_akhir'], 6) ?></strong>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <small class="text-muted">Menampilkan 10 ranking teratas dari <?= count($hasil_perhitungan) ?> total alternatif</small>
            </div>
        </div>
    </div>
</div>
<?php
$kriteria = $smart->getKriteria();
$alternatif = $smart->getAlternatif();
$metode_terpilih = isset($_GET['metode']) ? $_GET['metode'] : 'manual';
$detail = $smart->getDetailPerhitungan($metode_terpilih);

$data_asli = $detail['data_asli'];
$data_normalisasi = $detail['data_normalisasi'];
$bobot = $detail['bobot'];

$hasil_perhitungan = [];
if (!empty($bobot)) {
    $hasil_perhitungan = $smart->hitungSkorSMART($metode_terpilih);
}
?>
<!-- Step 5: Ranking Final -->
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <span class="step-indicator">5</span>
                    Ranking Final
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Ranking</th>
                                <th>SMD</th>
                                <th>Skor Akhir</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach(array_slice($hasil_perhitungan, 0, 10) as $hasil): ?>
                                <tr>
                                    <td>
                                        <?php if($hasil['ranking'] <= 3): ?>
                                            <span class="badge bg-warning fs-6">
                                                <i class="fas fa-trophy"></i> <?= $hasil['ranking'] ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-primary fs-6"><?= $hasil['ranking'] ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?= $hasil['id_alternatif'] ?></strong></td>
                                    <td><?= number_format($hasil['skor_akhir'], 6) ?></td>
                                    <td>
                                        <?php if($hasil['ranking'] == 1): ?>
                                            <span class="badge bg-success">Terbaik</span>
                                        <?php elseif($hasil['ranking'] <= 5): ?>
                                            <span class="badge bg-info">Sangat Baik</span>
                                        <?php elseif($hasil['ranking'] <= 10): ?>
                                            <span class="badge bg-warning">Baik</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Cukup</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3">
                    <a href="index.php?page=hasil&metode=<?= $metode_terpilih ?>" class="btn btn-primary">
                        <i class="fas fa-eye me-1"></i>Lihat Hasil Lengkap
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Visualisasi Top 10</h5>
            </div>
            <div class="card-body">
                <canvas id="rankingChart" width="400" height="300"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
    const ctx = document.getElementById('bobotChart').getContext('2d');
    const bobotChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($kriteria, 'id_kriteria')) ?>,
            datasets: [{
                label: 'Bobot Kriteria',
                data: <?= json_encode(array_map(function($k) use ($bobot) {
                    return $bobot[$k['id_kriteria']] ?? 0;
                }, $kriteria)) ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 1
                }
            }
        }
    });
</script>





<script>
$(document).ready(function() {
    console.log('Perhitungan page loaded');
    
    // Chart bobot kriteria
    const bobotCtx = document.getElementById('bobotChart');
    if (bobotCtx) {
        console.log('Creating bobot chart...');
        try {
            const bobotChart = new Chart(bobotCtx, {
                type: 'doughnut',
                data: {
                    labels: [<?php foreach($kriteria as $k): ?>'<?= $k['id_kriteria'] ?>',<?php endforeach; ?>],
                    datasets: [{
                        data: [<?php foreach($kriteria as $k): ?><?= $bobot[$k['id_kriteria']] ?>,<?php endforeach; ?>],
                        backgroundColor: [
                            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', 
                            '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0'
                        ],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { 
                                font: { size: 10 },
                                padding: 10
                            }
                        },
                        title: {
                            display: true,
                            text: 'Distribusi Bobot Kriteria'
                        }
                    }
                }
            });
            console.log('Bobot chart created successfully');
        } catch (error) {
            console.error('Error creating bobot chart:', error);
        }
    }

    // Chart ranking top 10
    const rankingCtx = document.getElementById('rankingChart');
    if (rankingCtx) {
        console.log('Creating ranking chart...');
        try {
            const top10Data = <?= json_encode(array_slice($hasil_perhitungan, 0, 10)) ?>;
            console.log('Top 10 data:', top10Data);
            
            const rankingChart = new Chart(rankingCtx, {
                type: 'bar',
                data: {
                    labels: top10Data.map(h => h.id_alternatif),
                    datasets: [{
                        label: 'Skor SMART',
                        data: top10Data.map(h => parseFloat(h.skor_akhir)),
                        backgroundColor: 'rgba(52, 152, 219, 0.8)',
                        borderColor: '#3498db',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: 'Skor Top 10 Alternatif'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Skor SMART'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Smart Mobile Device'
                            }
                        }
                    }
                }
            });
            console.log('Ranking chart created successfully');
        } catch (error) {
            console.error('Error creating ranking chart:', error);
        }
    }
});
</script>

<?php endif; ?>

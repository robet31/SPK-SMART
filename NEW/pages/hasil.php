<?php
// Ambil metode yang dipilih
$metode_terpilih = isset($_GET['metode']) ? $_GET['metode'] : 'manual';

// Ambil hasil perhitungan
$hasil = $smart->getHasil($metode_terpilih);

// Cek apakah ada hasil
if (empty($hasil)) {
    echo "<div class='alert alert-warning'>
            <i class='fas fa-exclamation-triangle me-2'></i>
            <strong>Peringatan!</strong> Belum ada hasil perhitungan untuk metode " . ucfirst($metode_terpilih) . ". 
            Silakan lakukan perhitungan terlebih dahulu di menu 
            <a href='index.php?page=perhitungan&metode=" . $metode_terpilih . "' class='alert-link'>Perhitungan SMART</a>
          </div>";
    return;
}

// Kategorisasi hasil
$kategori = [
    'excellent' => [],
    'very_good' => [],
    'good' => [],
    'fair' => []
];

foreach ($hasil as $h) {
    if ($h['skor_akhir'] >= 0.8) {
        $kategori['excellent'][] = $h;
    } elseif ($h['skor_akhir'] >= 0.6) {
        $kategori['very_good'][] = $h;
    } elseif ($h['skor_akhir'] >= 0.4) {
        $kategori['good'][] = $h;
    } else {
        $kategori['fair'][] = $h;
    }
}
?>

<div class="row">
    <div class="col-12">
        <h1 class="mb-4">
            <i class="fas fa-trophy text-primary me-3"></i>
            Hasil & Ranking
        </h1>
        <p class="lead text-muted">Hasil akhir ranking Smart Mobile Device menggunakan metode <?= ucfirst($metode_terpilih) ?></p>
    </div>
</div>

<!-- Pilihan Metode -->
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
                            <a href="index.php?page=hasil&metode=manual" 
                               class="btn <?= $metode_terpilih == 'manual' ? 'btn-primary' : 'btn-outline-primary' ?>">
                                <i class="fas fa-edit me-1"></i>Bobot Manual
                            </a>
                            <a href="index.php?page=hasil&metode=roc" 
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

<!-- Top 3 Winners -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-medal me-2"></i>Top 3 Winners</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php for($i = 0; $i < 3 && $i < count($hasil); $i++): ?>
                        <div class="col-md-4">
                            <div class="card text-center <?= $i == 0 ? 'border-warning' : ($i == 1 ? 'border-secondary' : 'border-warning') ?>">
                                <div class="card-body">
                                    <div class="mb-3">
                                        <?php if($i == 0): ?>
                                            <i class="fas fa-trophy text-warning" style="font-size: 3rem;"></i>
                                        <?php elseif($i == 1): ?>
                                            <i class="fas fa-medal text-secondary" style="font-size: 3rem;"></i>
                                        <?php else: ?>
                                            <i class="fas fa-award text-warning" style="font-size: 3rem;"></i>
                                        <?php endif; ?>
                                    </div>
                                    <h4 class="card-title"><?= $hasil[$i]['id_alternatif'] ?></h4>
                                    <h5 class="text-primary"><?= number_format($hasil[$i]['skor_akhir'], 6) ?></h5>
                                    <p class="card-text">
                                        <span class="badge bg-primary fs-6">Ranking <?= $hasil[$i]['ranking'] ?></span>
                                    </p>
                                    <small class="text-muted"><?= $hasil[$i]['nama_alternatif'] ?></small>
                                </div>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistik -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-success"><?= count($kategori['excellent']) ?></h3>
                <p class="mb-0">Terbaik</p>
                <small class="text-muted">Skor ≥ 0.8</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-info"><?= count($kategori['very_good']) ?></h3>
                <p class="mb-0">Sangat Baik</p>
                <small class="text-muted">0.6 ≤ Skor < 0.8</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-warning"><?= count($kategori['good']) ?></h3>
                <p class="mb-0">Baik</p>
                <small class="text-muted">0.4 ≤ Skor < 0.6</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-secondary"><?= count($kategori['fair']) ?></h3>
                <p class="mb-0">Cukup</p>
                <small class="text-muted">Skor < 0.4</small>
            </div>
        </div>
    </div>
</div>

<!-- Grafik -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Distribusi Kategori</h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="kategoriChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Grafik Skor Top 10</h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="skorChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabel Hasil Lengkap -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-table me-2"></i>Tabel Hasil Lengkap</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="hasilTable">
                        <thead>
                            <tr>
                                <th>Rangking</th>
                                <th>SMD</th>
                                <th>Nama Alternatif</th>
                                <th>Skor Akhir</th>
                                <th>Kategori</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($hasil as $h): ?>
                                <tr>
                                    <td>
                                        <?php if($h['ranking'] <= 3): ?>
                                            <span class="badge bg-warning fs-6">
                                                <i class="fas fa-trophy"></i> <?= $h['ranking'] ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-primary fs-6"><?= $h['ranking'] ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?= $h['id_alternatif'] ?></strong></td>
                                    <td><?= $h['nama_alternatif'] ?></td>
                                    <td><?= number_format($h['skor_akhir'], 6) ?></td>
                                    <td>
                                        <?php if($h['skor_akhir'] >= 0.8): ?>
                                            <span class="badge bg-success">Terbaik</span>
                                        <?php elseif($h['skor_akhir'] >= 0.6): ?>
                                            <span class="badge bg-info">Sangat Baik</span>
                                        <?php elseif($h['skor_akhir'] >= 0.4): ?>
                                            <span class="badge bg-warning">Baik</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Cukup</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($h['ranking'] == 1): ?>
                                            <span class="badge bg-success">Winner</span>
                                        <?php elseif($h['ranking'] <= 5): ?>
                                            <span class="badge bg-info">Top 5</span>
                                        <?php elseif($h['ranking'] <= 10): ?>
                                            <span class="badge bg-warning">Top 10</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">lainnya</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Chart distribusi kategori
    const kategoriCtx = document.getElementById('kategoriChart');
    if (kategoriCtx) {
        const kategoriChart = new Chart(kategoriCtx, {
            type: 'doughnut',
            data: {
                labels: ['Excellent', 'Very Good', 'Good', 'Fair'],
                datasets: [{
                    data: [
                        <?= count($kategori['excellent']) ?>,
                        <?= count($kategori['very_good']) ?>,
                        <?= count($kategori['good']) ?>,
                        <?= count($kategori['fair']) ?>
                    ],
                    backgroundColor: [
                        '#28a745',
                        '#17a2b8',
                        '#ffc107',
                        '#6c757d'
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
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.parsed / total) * 100).toFixed(1);
                                return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    // Chart skor top 10
    const skorCtx = document.getElementById('skorChart');
    if (skorCtx) {
        const top10 = <?= json_encode(array_slice($hasil, 0, 10)) ?>;
        
        const skorChart = new Chart(skorCtx, {
            type: 'bar',
            data: {
                labels: top10.map(h => h.id_alternatif),
                datasets: [{
                    label: 'Skor SMART',
                    data: top10.map(h => h.skor_akhir),
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
    }

    // DataTable
    $('#hasilTable').DataTable({
        pageLength: 25,
        order: [[0, 'asc']], // Urutkan berdasarkan ranking
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
        }
    });
});
</script>

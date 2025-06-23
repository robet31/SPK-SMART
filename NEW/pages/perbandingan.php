<?php
$kriteria = $smart->getKriteria();

// Ambil hasil untuk kedua metode
$hasil_manual = $smart->getHasil('manual');
$hasil_roc = $smart->getHasil('roc');

// Cek apakah kedua metode sudah ada hasil
$ada_manual = !empty($hasil_manual);
$ada_roc = !empty($hasil_roc);

// Organisir data untuk perbandingan
$perbandingan = [];
if ($ada_manual && $ada_roc) {
    foreach ($hasil_manual as $manual) {
        $id_alternatif = $manual['id_alternatif'];
        
        // Cari data ROC yang sesuai
        $roc_data = array_filter($hasil_roc, function($r) use ($id_alternatif) {
            return $r['id_alternatif'] == $id_alternatif;
        });
        $roc_data = reset($roc_data);
        
        if ($roc_data) {
            $perbandingan[$id_alternatif] = [
                'nama' => $manual['nama_alternatif'],
                'manual' => $manual,
                'roc' => $roc_data,
                'selisih_ranking' => abs($manual['ranking'] - $roc_data['ranking']),
                'selisih_skor' => abs($manual['skor_akhir'] - $roc_data['skor_akhir'])
            ];
        }
    }
}

// Hitung korelasi Spearman jika ada data
$korelasi_spearman = null;
if (!empty($perbandingan)) {
    $ranking_manual = [];
    $ranking_roc = [];
    
    foreach ($perbandingan as $data) {
        $ranking_manual[] = $data['manual']['ranking'];
        $ranking_roc[] = $data['roc']['ranking'];
    }
    
    // Hitung korelasi Spearman sederhana
    $n = count($ranking_manual);
    $sum_d_squared = 0;
    
    for ($i = 0; $i < $n; $i++) {
        $d = $ranking_manual[$i] - $ranking_roc[$i];
        $sum_d_squared += $d * $d;
    }
    
    $korelasi_spearman = 1 - (6 * $sum_d_squared) / ($n * ($n * $n - 1));
}

// Ambil bobot kriteria dengan query database langsung
$bobot_manual = [];
$bobot_roc = [];

// Query untuk bobot manual
$query_manual = "SELECT * FROM bobot_kriteria WHERE metode_pembobotan = 'manual'";
$stmt_manual = $db->prepare($query_manual);
$stmt_manual->execute();
$bobot_manual_data = $stmt_manual->fetchAll(PDO::FETCH_ASSOC);

// Query untuk bobot ROC
$query_roc = "SELECT * FROM bobot_kriteria WHERE metode_pembobotan = 'roc'";
$stmt_roc = $db->prepare($query_roc);
$stmt_roc->execute();
$bobot_roc_data = $stmt_roc->fetchAll(PDO::FETCH_ASSOC);

// Organisir data bobot
foreach ($bobot_manual_data as $b) {
    $bobot_manual[$b['id_kriteria']] = $b['bobot'];
}

foreach ($bobot_roc_data as $b) {
    $bobot_roc[$b['id_kriteria']] = $b['bobot'];
}
?>

<div class="row">
    <div class="col-12">
        <h1 class="mb-4">
            <i class="fas fa-balance-scale text-primary me-3"></i>
            Perbandingan Metode Pembobotan
        </h1>
        <p class="lead text-muted">Analisis perbandingan hasil antara metode manual dan ROC</p>
    </div>
</div>

<?php if (!$ada_manual || !$ada_roc): ?>
<div class="row">
    <div class="col-12">
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Peringatan!</strong> Untuk melakukan perbandingan, pastikan kedua metode sudah dihitung:
            <ul class="mb-0 mt-2">
                <?php if (!$ada_manual): ?>
                    <li>Metode Manual belum dihitung - <a href="index.php?page=bobot" class="alert-link">Set Bobot Manual</a></li>
                <?php endif; ?>
                <?php if (!$ada_roc): ?>
                    <li>Metode ROC belum dihitung - <a href="index.php?page=roc" class="alert-link">Hitung ROC</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>
<?php else: ?>

<!-- Statistik Perbandingan -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-primary"><?= number_format($korelasi_spearman, 4) ?></h3>
                <p class="mb-0">Korelasi Spearman</p>
                <small class="text-muted">Tingkat kemiripan ranking</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-success"><?= count(array_filter($perbandingan, function($p) { return $p['selisih_ranking'] == 0; })) ?></h3>
                <p class="mb-0">Ranking Sama</p>
                <small class="text-muted">Alternatif dengan ranking identik</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <?php 
                $avg_selisih = count($perbandingan) > 0 ? array_sum(array_column($perbandingan, 'selisih_ranking')) / count($perbandingan) : 0;
                ?>
                <h3 class="text-warning"><?= number_format($avg_selisih, 2) ?></h3>
                <p class="mb-0">Rata-rata Selisih</p>
                <small class="text-muted">Perbedaan ranking rata-rata</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-info"><?= count($perbandingan) ?></h3>
                <p class="mb-0">Total Alternatif</p>
                <small class="text-muted">Yang dibandingkan</small>
            </div>
        </div>
    </div>
</div>

<!-- Perbandingan Bobot -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-weight-hanging me-2"></i>Perbandingan Bobot Kriteria</h5>
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
                                        <th>Bobot Manual</th>
                                        <th>Bobot ROC</th>
                                        <th>Selisih</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($kriteria as $k): 
                                        $manual = isset($bobot_manual[$k['id_kriteria']]) ? $bobot_manual[$k['id_kriteria']] : 0;
                                        $roc = isset($bobot_roc[$k['id_kriteria']]) ? $bobot_roc[$k['id_kriteria']] : 0;
                                        $selisih = abs($manual - $roc);
                                        $persen_selisih = $manual > 0 ? ($selisih / $manual) * 100 : 0;
                                    ?>
                                        <tr>
                                            <td><strong><?= $k['id_kriteria'] ?></strong></td>
                                            <td><?= $k['nama_kriteria'] ?></td>
                                            <td><?= number_format($manual, 6) ?></td>
                                            <td><?= number_format($roc, 6) ?></td>
                                            <td><?= number_format($selisih, 6) ?></td>
                                            <td>
                                                <span class="badge <?= $persen_selisih > 50 ? 'bg-danger' : ($persen_selisih > 25 ? 'bg-warning' : 'bg-success') ?>">
                                                    <?= number_format($persen_selisih, 1) ?>%
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <canvas id="bobotComparisonChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabel Perbandingan -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-table me-2"></i>Tabel Perbandingan Ranking</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="perbandinganTable">
                        <thead>
                            <tr>
                                <th>SMD</th>
                                <th>Manual Rank</th>
                                <th>Manual Skor</th>
                                <th>ROC Rank</th>
                                <th>ROC Skor</th>
                                <th>Selisih Rank</th>
                                <th>Selisih Skor</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($perbandingan as $id => $data): ?>
                                <tr>
                                    <td><strong><?= $id ?></strong></td>
                                    <td><?= $data['manual']['ranking'] ?></td>
                                    <td><?= number_format($data['manual']['skor_akhir'], 6) ?></td>
                                    <td><?= $data['roc']['ranking'] ?></td>
                                    <td><?= number_format($data['roc']['skor_akhir'], 6) ?></td>
                                    <td>
                                        <?php if ($data['selisih_ranking'] == 0): ?>
                                            <span class="badge bg-success">0</span>
                                        <?php elseif ($data['selisih_ranking'] <= 2): ?>
                                            <span class="badge bg-warning"><?= $data['selisih_ranking'] ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-danger"><?= $data['selisih_ranking'] ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= number_format($data['selisih_skor'], 6) ?></td>
                                    <td>
                                        <?php if ($data['selisih_ranking'] == 0): ?>
                                            <span class="badge bg-success">Konsisten</span>
                                        <?php elseif ($data['selisih_ranking'] <= 2): ?>
                                            <span class="badge bg-warning">Mirip</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Berbeda</span>
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

<!-- Visualisasi -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-scatter me-2"></i>Scatter Plot Skor</h5>
            </div>
            <div class="card-body">
                <canvas id="scatterChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Distribusi Perubahan Ranking</h5>
            </div>
            <div class="card-body">
                <canvas id="perubahanChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Analisis -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Analisis Korelasi</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h6 class="text-primary">Interpretasi Korelasi Spearman:</h6>
                        <?php 
                        $interpretasi = '';
                        $class = '';
                        if ($korelasi_spearman >= 0.9) {
                            $interpretasi = 'Sangat Kuat - Kedua metode menghasilkan ranking yang sangat mirip';
                            $class = 'success';
                        } elseif ($korelasi_spearman >= 0.7) {
                            $interpretasi = 'Kuat - Kedua metode cukup konsisten dalam ranking';
                            $class = 'info';
                        } elseif ($korelasi_spearman >= 0.5) {
                            $interpretasi = 'Sedang - Ada kemiripan ranking namun tidak terlalu kuat';
                            $class = 'warning';
                        } else {
                            $interpretasi = 'Lemah - Perbedaan ranking cukup signifikan';
                            $class = 'danger';
                        }
                        ?>
                        <div class="alert alert-<?= $class ?>">
                            <strong>Korelasi <?= number_format($korelasi_spearman, 4) ?>:</strong> <?= $interpretasi ?>
                        </div>
                        
                        <h6 class="text-primary">Kesimpulan:</h6>
                        <ul>
                            <li><strong>Konsistensi:</strong> <?= count($perbandingan) > 0 ? number_format((count(array_filter($perbandingan, function($p) { return $p['selisih_ranking'] <= 2; })) / count($perbandingan)) * 100, 1) : 0 ?>% alternatif memiliki selisih ranking ≤ 2</li>
                            <li><strong>Stabilitas:</strong> <?= count($perbandingan) > 0 ? number_format((count(array_filter($perbandingan, function($p) { return $p['selisih_ranking'] == 0; })) / count($perbandingan)) * 100, 1) : 0 ?>% alternatif memiliki ranking yang sama persis</li>
                            <li><strong>Rekomendasi:</strong> 
                                <?php if ($korelasi_spearman >= 0.7): ?>
                                    Kedua metode dapat digunakan dengan hasil yang relatif konsisten
                                <?php else: ?>
                                    Disarankan untuk mempertimbangkan konteks dan preferensi dalam pemilihan metode
                                <?php endif; ?>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <canvas id="korelasiChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    console.log('Perbandingan page loaded');
    
    // Prepare data for charts
    <?php
    // Prepare scatter plot data
    $scatter_data = [];
    foreach ($perbandingan as $id => $data) {
        $scatter_data[] = [
            'x' => $data['manual']['skor_akhir'],
            'y' => $data['roc']['skor_akhir'],
            'label' => $id
        ];
    }

    // Prepare weight comparison data
    $manual_weights = [];
    $roc_weights = [];
    $kriteria_labels = [];
    foreach($kriteria as $k) {
        $kriteria_labels[] = $k['id_kriteria'];
        $manual_weights[] = isset($bobot_manual[$k['id_kriteria']]) ? $bobot_manual[$k['id_kriteria']] : 0;
        $roc_weights[] = isset($bobot_roc[$k['id_kriteria']]) ? $bobot_roc[$k['id_kriteria']] : 0;
    }

    // Calculate ranking changes
    $tetap = 0;
    $naik = 0;
    $turun = 0;

    foreach ($perbandingan as $id => $data) {
        $perubahan = $data['manual']['ranking'] - $data['roc']['ranking'];
        if ($perubahan == 0) $tetap++;
        elseif ($perubahan > 0) $naik++;
        else $turun++;
    }

    // Prevent division by zero
    $max_weight = max(array_merge($manual_weights, $roc_weights));
    if ($max_weight == 0) $max_weight = 1;
    ?>

    // Chart perbandingan bobot
    const bobotCompCtx = document.getElementById('bobotComparisonChart');
    if (bobotCompCtx) {
        console.log('Creating bobot comparison chart...');
        try {
            const bobotCompChart = new Chart(bobotCompCtx, {
                type: 'radar',
                data: {
                    labels: <?= json_encode($kriteria_labels) ?>,
                    datasets: [{
                        label: 'Bobot Manual',
                        data: <?= json_encode($manual_weights) ?>,
                        borderColor: '#007bff',
                        backgroundColor: 'rgba(0, 123, 255, 0.2)',
                        pointBackgroundColor: '#007bff'
                    }, {
                        label: 'Bobot ROC',
                        data: <?= json_encode($roc_weights) ?>,
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.2)',
                        pointBackgroundColor: '#28a745'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    scales: {
                        r: {
                            beginAtZero: true,
                            max: <?= $max_weight * 1.1 ?>
                        }
                    }
                }
            });
            console.log('Bobot comparison chart created successfully');
        } catch (error) {
            console.error('Error creating bobot comparison chart:', error);
        }
    }

    // Scatter plot skor
    const scatterCtx = document.getElementById('scatterChart');
    if (scatterCtx) {
        console.log('Creating scatter chart...');
        try {
            const scatterData = <?= json_encode($scatter_data) ?>;

            const scatterChart = new Chart(scatterCtx, {
                type: 'scatter',
                data: {
                    datasets: [{
                        label: 'Skor Manual vs ROC',
                        data: scatterData,
                        backgroundColor: 'rgba(0, 123, 255, 0.6)',
                        borderColor: '#007bff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.raw.label + ': Manual=' + context.raw.x.toFixed(4) + ', ROC=' + context.raw.y.toFixed(4);
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Skor Manual'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Skor ROC'
                            }
                        }
                    }
                }
            });
            console.log('Scatter chart created successfully');
        } catch (error) {
            console.error('Error creating scatter chart:', error);
        }
    }

    // Chart distribusi perubahan ranking
    const perubahanCtx = document.getElementById('perubahanChart');
    if (perubahanCtx) {
        console.log('Creating perubahan chart...');
        try {
            const perubahanChart = new Chart(perubahanCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Ranking Naik', 'Ranking Tetap', 'Ranking Turun'],
                    datasets: [{
                        data: [<?= $naik ?>, <?= $tetap ?>, <?= $turun ?>],
                        backgroundColor: [
                            '#28a745',
                            '#6c757d',
                            '#dc3545'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
            console.log('Perubahan chart created successfully');
        } catch (error) {
            console.error('Error creating perubahan chart:', error);
        }
    }

    <?php
  // Hitung values dalam persen
  $kor_pct  = abs($korelasi_spearman) * 100;
  $sisa_pct = (1 - abs($korelasi_spearman)) * 100;

  // Tentukan warna sesuai threshold
  if ($korelasi_spearman >= 0.7) {
    $warna = '#28a745';
  } elseif ($korelasi_spearman >= 0.5) {
    $warna = '#ffc107';
  } else {
    $warna = '#dc3545';
  }

  // Buat array JS untuk data & warna
  $kor_data   = [$kor_pct, $sisa_pct];
  $kor_colors = [$warna, '#e9ecef'];
?>

    // Chart korelasi
    // Chart korelasi
const korelasiCtx = document.getElementById('korelasiChart');
if (korelasiCtx) {
  new Chart(korelasiCtx, {
    type: 'doughnut',
    data: {
      labels: ['Korelasi', 'Sisa'],
      datasets: [{
        data: <?= json_encode($kor_data) ?>,
        backgroundColor: <?= json_encode($kor_colors) ?>
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        title: {
          display: true,
          text: 'Tingkat Korelasi'
        }
      }
    }
  });
}


    // DataTable untuk tabel perbandingan
    $('#perbandinganTable').DataTable({
        pageLength: 25,
        order: [[5, 'asc']], // Urutkan berdasarkan selisih ranking
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
        }
    });
});
</script>

<?php endif; ?>

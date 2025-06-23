<?php
$kriteria = $smart->getKriteria();

// Handle form submission untuk urutan kriteria
if ($_POST && isset($_POST['hitung_roc'])) {
    $urutan_kriteria = [];
    foreach ($kriteria as $k) {
        $urutan = intval($_POST['urutan_' . $k['id_kriteria']]);
        $urutan_kriteria[$urutan] = $k['id_kriteria'];
    }
    
    // Urutkan berdasarkan urutan
    ksort($urutan_kriteria);
    
    // Hitung bobot ROC
    $n = count($kriteria);
    $bobot_roc = [];
    $j = 1;
    
    foreach ($urutan_kriteria as $id_kriteria) {
        $sum = 0;
        for ($k = $j; $k <= $n; $k++) {
            $sum += 1 / $k;
        }
        $bobot_roc[$id_kriteria] = $sum / $n;
        $j++;
    }
    
    // Simpan bobot ROC
    if ($smart->simpanBobot($bobot_roc, 'roc')) {
        echo "<script>
            Swal.fire({
                title: 'Berhasil!',
                text: 'Bobot ROC berhasil dihitung dan disimpan',
                icon: 'success',
                confirmButtonText: 'OK'
            });
        </script>";
    }
}

// Ambil bobot ROC yang sudah tersimpan
$query = "SELECT * FROM bobot_kriteria WHERE metode_pembobotan = 'roc'";
$stmt = $db->prepare($query);
$stmt->execute();
$bobot_tersimpan = $stmt->fetchAll(PDO::FETCH_ASSOC);

$bobot_roc = [];
foreach ($bobot_tersimpan as $b) {
    $bobot_roc[$b['id_kriteria']] = $b['bobot'];
}
?>

<div class="row">
    <div class="col-12">
        <h1 class="mb-4">
            <i class="fas fa-calculator text-primary me-3"></i>
            Pembobotan ROC (Rank Order Centroid)
        </h1>
        <p class="lead text-muted">Metode objektif untuk menentukan bobot berdasarkan urutan kepentingan kriteria</p>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-sort-numeric-down me-2"></i>Tentukan Urutan Kepentingan Kriteria</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Petunjuk:</strong> Urutkan kriteria dari yang paling penting (urutan 1) hingga yang kurang penting (urutan 13)
                </div>

                <form method="POST" id="rocForm">
                    <div class="row">
                        <?php foreach($kriteria as $index => $k): ?>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <strong><?= $k['id_kriteria'] ?></strong> - <?= $k['nama_kriteria'] ?>
                                    <?php if($k['jenis_kriteria'] == 'benefit'): ?>
                                        <span class="badge bg-success ms-1">Benefit</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger ms-1">Cost</span>
                                    <?php endif; ?>
                                </label>
                                <select class="form-select urutan-select" name="urutan_<?= $k['id_kriteria'] ?>" required>
                                    <option value="">Pilih urutan...</option>
                                    <?php for($i = 1; $i <= count($kriteria); $i++): ?>
                                        <option value="<?= $i ?>" <?= ($i == $index + 1) ? 'selected' : '' ?>><?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                                <small class="text-muted"><?= $k['deskripsi'] ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-12">
                            <button type="submit" name="hitung_roc" class="btn btn-primary me-2">
                                <i class="fas fa-calculator me-1"></i>Hitung Bobot ROC
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="resetUrutan()">
                                <i class="fas fa-undo me-1"></i>Reset Urutan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <?php if (!empty($bobot_roc)): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Hasil Perhitungan ROC</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Urutan</th>
                                <th>Kriteria</th>
                                <th>Nama Kriteria</th>
                                <th>Bobot ROC</th>
                                <th>Persentase</th>
                                <th>Perhitungan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // Urutkan bobot berdasarkan nilai (descending)
                            arsort($bobot_roc);
                            $urutan = 1;
                            $n = count($kriteria);
                            foreach($bobot_roc as $id_kriteria => $bobot): 
                                $kriteria_info = array_filter($kriteria, function($k) use ($id_kriteria) {
                                    return $k['id_kriteria'] == $id_kriteria;
                                });
                                $kriteria_info = reset($kriteria_info);
                                
                                // Hitung detail perhitungan
                                $detail_calc = "(1/$n) × (";
                                $sum_parts = [];
                                for ($k = $urutan; $k <= $n; $k++) {
                                    $sum_parts[] = "1/$k";
                                }
                                $detail_calc .= implode(" + ", $sum_parts) . ")";
                            ?>
                                <tr>
                                    <td><span class="badge bg-primary"><?= $urutan ?></span></td>
                                    <td><strong><?= $id_kriteria ?></strong></td>
                                    <td><?= $kriteria_info['nama_kriteria'] ?></td>
                                    <td><?= number_format($bobot, 6) ?></td>
                                    <td><?= number_format($bobot * 100, 2) ?>%</td>
                                    <td class="small"><?= $detail_calc ?></td>
                                </tr>
                            <?php 
                                $urutan++;
                            endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-info">
                                <th colspan="3">Total</th>
                                <th><?= number_format(array_sum($bobot_roc), 6) ?></th>
                                <th>100.00%</th>
                                <th>-</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Tentang Metode ROC</h5>
            </div>
            <div class="card-body">
                <h6 class="text-primary">Rank Order Centroid (ROC)</h6>
                <p class="small">Metode objektif untuk menentukan bobot kriteria berdasarkan urutan kepentingan relatif tanpa memerlukan nilai numerik spesifik.</p>

                <div class="formula-box">
                    <strong>Formula ROC:</strong><br>
                    w<sub>j</sub> = (1/n) × Σ<sub>k=j</sub><sup>n</sup> (1/k)<br><br>
                    dimana:<br>
                    - w<sub>j</sub> = bobot kriteria urutan ke-j<br>
                    - n = jumlah kriteria (<?= count($kriteria) ?>)<br>
                    - k = indeks penjumlahan dari j sampai n
                </div>

                <h6 class="text-primary mt-3">Keunggulan ROC:</h6>
                <ul class="small">
                    <li>Objektif dan konsisten</li>
                    <li>Mudah dipahami dan diimplementasikan</li>
                    <li>Tidak memerlukan perbandingan berpasangan</li>
                    <li>Mengurangi bias subjektif</li>
                    <li>Hasil yang stabil dan dapat direproduksi</li>
                </ul>

                <h6 class="text-primary mt-3">Contoh Perhitungan Detail:</h6>
                <div class="small">
                    Untuk n = <?= count($kriteria) ?> kriteria:<br><br>
                    <strong>Kriteria urutan 1 (paling penting):</strong><br>
                    w₁ = (1/<?= count($kriteria) ?>) × (1/1 + 1/2 + 1/3 + ... + 1/<?= count($kriteria) ?>)<br>
                    w₁ ≈ <?= number_format((1/count($kriteria)) * array_sum(array_map(function($i) { return 1/$i; }, range(1, count($kriteria)))), 3) ?><br><br>
                    
                    <strong>Kriteria urutan 2:</strong><br>
                    w₂ = (1/<?= count($kriteria) ?>) × (1/2 + 1/3 + ... + 1/<?= count($kriteria) ?>)<br>
                    w₂ ≈ <?= number_format((1/count($kriteria)) * array_sum(array_map(function($i) { return 1/$i; }, range(2, count($kriteria)))), 3) ?><br><br>
                    
                    <strong>Kriteria urutan <?= count($kriteria) ?> (kurang penting):</strong><br>
                    w₁₃ = (1/<?= count($kriteria) ?>) × (1/<?= count($kriteria) ?>)<br>
                    w₁₃ = <?= number_format(1/count($kriteria)/count($kriteria), 3) ?>
                </div>
            </div>
        </div>

        <?php if (!empty($bobot_roc)): ?>
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Visualisasi Bobot ROC</h5>
            </div>
            <div class="card-body">
                <div class="chart-container chart-small">
                    <canvas id="rocChart"></canvas>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function resetUrutan() {
    document.querySelectorAll('.urutan-select').forEach(function(select, index) {
        select.value = index + 1;
    });
}

// Validasi urutan tidak boleh duplikat
document.getElementById('rocForm').addEventListener('submit', function(e) {
    const urutan = [];
    let valid = true;
    
    document.querySelectorAll('.urutan-select').forEach(function(select) {
        const nilai = select.value;
        if (urutan.includes(nilai)) {
            valid = false;
        }
        urutan.push(nilai);
    });
    
    if (!valid) {
        e.preventDefault();
        Swal.fire({
            title: 'Error!',
            text: 'Urutan kriteria tidak boleh sama/duplikat',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    }
});

<?php if (!empty($bobot_roc)): ?>
$(document).ready(function() {
    console.log('ROC page loaded, creating chart...');
    
    // Chart untuk visualisasi bobot ROC
    const ctx = document.getElementById('rocChart');
    if (ctx) {
        console.log('ROC canvas found, creating chart...');
        try {
            const rocChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: [<?php foreach($bobot_roc as $id_kriteria => $bobot): ?>'<?= $id_kriteria ?>',<?php endforeach; ?>],
                    datasets: [{
                        data: [<?php foreach($bobot_roc as $id_kriteria => $bobot): ?><?= $bobot ?>,<?php endforeach; ?>],
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
                                font: {
                                    size: 10
                                },
                                padding: 10
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const percentage = (context.parsed * 100).toFixed(2);
                                    return context.label + ': ' + context.parsed.toFixed(4) + ' (' + percentage + '%)';
                                }
                            }
                        },
                        title: {
                            display: true,
                            text: 'Distribusi Bobot ROC'
                        }
                    }
                }
            });
            console.log('ROC chart created successfully');
        } catch (error) {
            console.error('Error creating ROC chart:', error);
        }
    } else {
        console.error('Canvas rocChart not found');
    }
});
<?php endif; ?>
</script>

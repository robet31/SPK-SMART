<?php
$kriteria = $smart->getKriteria();

// Handle form submission
if ($_POST && isset($_POST['simpan_bobot'])) {
    $bobot_data = [];
    $total_bobot = 0;
    
    foreach ($kriteria as $k) {
        $bobot = floatval($_POST['bobot_' . $k['id_kriteria']]);
        $bobot_data[$k['id_kriteria']] = $bobot;
        $total_bobot += $bobot;
    }
    
    // Validasi total bobot = 1
    if (abs($total_bobot - 1.0) < 0.001) {
        if ($smart->simpanBobot($bobot_data, 'manual')) {
            echo "<script>
                Swal.fire({
                    title: 'Berhasil!',
                    text: 'Bobot kriteria berhasil disimpan',
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            </script>";
        }
    } else {
        echo "<script>
            Swal.fire({
                title: 'Error!',
                text: 'Total bobot harus sama dengan 1.0 (saat ini: " . number_format($total_bobot, 3) . ")',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        </script>";
    }
}

// Ambil bobot yang sudah tersimpan
$query = "SELECT * FROM bobot_kriteria WHERE metode_pembobotan = 'manual'";
$stmt = $db->prepare($query);
$stmt->execute();
$bobot_tersimpan = $stmt->fetchAll(PDO::FETCH_ASSOC);

$bobot_manual = [];
foreach ($bobot_tersimpan as $b) {
    $bobot_manual[$b['id_kriteria']] = $b['bobot'];
}
?>

<div class="row">
    <div class="col-12">
        <h1 class="mb-4">
            <i class="fas fa-weight-hanging text-primary me-3"></i>
            Input Bobot Manual
        </h1>
        <p class="lead text-muted">Tentukan bobot kepentingan untuk setiap kriteria berdasarkan preferensi subjektif</p>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Form Input Bobot Kriteria</h5>
            </div>
            <div class="card-body">
                <form method="POST" id="bobotForm">
                    <div class="row">
                        <?php foreach($kriteria as $k): ?>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <strong><?= $k['id_kriteria'] ?></strong> - <?= $k['nama_kriteria'] ?>
                                    <?php if($k['jenis_kriteria'] == 'benefit'): ?>
                                        <span class="badge bg-success ms-1">Benefit</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger ms-1">Cost</span>
                                    <?php endif; ?>
                                </label>
                                <input type="number" 
                                       class="form-control bobot-input" 
                                       name="bobot_<?= $k['id_kriteria'] ?>" 
                                       step="0.001" 
                                       min="0" 
                                       max="1" 
                                       value="<?= isset($bobot_manual[$k['id_kriteria']]) ? $bobot_manual[$k['id_kriteria']] : '0.077' ?>"
                                       required>
                                <small class="text-muted"><?= $k['deskripsi'] ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Total Bobot:</strong> <span id="totalBobot">0.000</span>
                                <span id="statusBobot" class="ms-2"></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" name="simpan_bobot" class="btn btn-primary me-2">
                                <i class="fas fa-save me-1"></i>Simpan Bobot
                            </button>
                            <button type="button" class="btn btn-secondary me-2" onclick="setBobotSama()">
                                <i class="fas fa-equals me-1"></i>Set Bobot Sama
                            </button>
                            <button type="button" class="btn btn-info" onclick="contohBobot()">
                                <i class="fas fa-lightbulb me-1"></i>Contoh Bobot MCC
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Bobot Tersimpan - Moved below form -->
        <?php if (!empty($bobot_manual)): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-check-circle text-success me-2"></i>Bobot Tersimpan</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Kriteria</th>
                                <th>Nama Kriteria</th>
                                <th>Bobot</th>
                                <th>Persentase</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($kriteria as $k): ?>
                                <tr>
                                    <td><strong><?= $k['id_kriteria'] ?></strong></td>
                                    <td><?= $k['nama_kriteria'] ?></td>
                                    <td><?= isset($bobot_manual[$k['id_kriteria']]) ? number_format($bobot_manual[$k['id_kriteria']], 3) : '0.000' ?></td>
                                    <td><?= isset($bobot_manual[$k['id_kriteria']]) ? number_format($bobot_manual[$k['id_kriteria']] * 100, 1) : '0.0' ?>%</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-info">
                                <th colspan="2">Total</th>
                                <th><?= number_format(array_sum($bobot_manual), 3) ?></th>
                                <th>100.0%</th>
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
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Panduan Input Bobot</h5>
            </div>
            <div class="card-body">
                <h6 class="text-primary">Aturan Pembobotan:</h6>
                <ul>
                    <li>Total semua bobot harus = 1.0</li>
                    <li>Setiap bobot bernilai 0 - 1</li>
                    <li>Semakin besar bobot, semakin penting kriteria</li>
                </ul>

                <div class="formula-box">
                    <strong>Formula Pembobotan Manual:</strong><br><br>
                    
                    <strong>1. Equal Weighting (Bobot Sama):</strong><br>
                    w<sub>i</sub> = 1/n<br>
                    w<sub>i</sub> = 1/<?= count($kriteria) ?> = <?= number_format(1/count($kriteria), 3) ?><br><br>
                    
                    <strong>2. Subjective Weighting:</strong><br>
                    Σw<sub>i</sub> = 1<br>
                    0 ≤ w<sub>i</sub> ≤ 1<br><br>
                    
                    <strong>3. Percentage Method:</strong><br>
                    w<sub>i</sub> = P<sub>i</sub>/100<br>
                    dimana P<sub>i</sub> = persentase kepentingan<br><br>
                    
                    <strong>4. Point Allocation (100 poin):</strong><br>
                    w<sub>i</sub> = Point<sub>i</sub>/100<br>
                    Σ Point<sub>i</sub> = 100
                </div>

                <h6 class="text-primary mt-3">Contoh Perhitungan:</h6>
                <div class="small">
                    <strong>Metode Percentage:</strong><br>
                    - C1 (CPU freq): 15% → w = 0.15<br>
                    - C2 (CPU cores): 10% → w = 0.10<br>
                    - C3 (GPU freq): 12% → w = 0.12<br>
                    - ... (total = 100%)<br><br>
                    
                    <strong>Metode Point Allocation:</strong><br>
                    Total 100 poin dibagi ke 13 kriteria:<br>
                    - Kriteria penting: 12-15 poin<br>
                    - Kriteria sedang: 6-8 poin<br>
                    - Kriteria kurang: 3-5 poin<br>
                </div>

                <h6 class="text-primary mt-3">Interpretasi Bobot:</h6>
                <ul class="small">
                    <li><strong>0.15-0.20:</strong> Sangat penting (15-20%)</li>
                    <li><strong>0.10-0.14:</strong> Penting (10-14%)</li>
                    <li><strong>0.05-0.09:</strong> Cukup penting (5-9%)</li>
                    <li><strong>0.01-0.04:</strong> Kurang penting (1-4%)</li>
                </ul>

                <div class="mt-3">
                    <h6 class="text-primary">Visualisasi Bobot:</h6>
                    <div class="chart-container chart-small">
                        <canvas id="bobotChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let bobotChart;

function hitungTotalBobot() {
    let total = 0;
    document.querySelectorAll('.bobot-input').forEach(function(input) {
        total += parseFloat(input.value) || 0;
    });
    
    document.getElementById('totalBobot').textContent = total.toFixed(3);
    
    const statusElement = document.getElementById('statusBobot');
    if (Math.abs(total - 1.0) < 0.001) {
        statusElement.innerHTML = '<span class="badge bg-success">✓ Valid</span>';
    } else {
        statusElement.innerHTML = '<span class="badge bg-danger">✗ Harus = 1.0</span>';
    }
    
    updateChart();
}

function setBobotSama() {
    const jumlahKriteria = document.querySelectorAll('.bobot-input').length;
    const bobotSama = (1.0 / jumlahKriteria).toFixed(3);
    
    document.querySelectorAll('.bobot-input').forEach(function(input) {
        input.value = bobotSama;
    });
    
    hitungTotalBobot();
}

function contohBobot() {
    // Contoh bobot berdasarkan kepentingan kriteria dalam MCC
    const contohBobotData = {
        'C1': 0.144,  // CPU frequency - penting untuk komputasi
        'C2': 0.133,  // CPU cores - penting untuk multitasking  
        'C3': 0.094,  // GPU frequency - cukup penting untuk grafis
        'C4': 0.105,  // Total RAM - penting untuk performa
        'C5': 0.050,  // Available memory - cukup penting
        'C6': 0.092,  // Battery capacity - cukup penting
        'C7': 0.076,  // Battery available - cukup penting untuk mobilitas
        'C8': 0.080,  // Wi-Fi strength - kurang penting
        'C9': 0.046,  // CPU load - cukup penting untuk efisiensi
        'C10': 0.041, // GPU load - cukup penting
        'C11': 0.017, // CPU temperature - kurang penting
        'C12': 0.028, // Battery temperature - kurang penting
        'C13': 0.094  // GPU architecture - kurang penting
    };
    
    document.querySelectorAll('.bobot-input').forEach(function(input) {
        const kriteria = input.name.replace('bobot_', '');
        if (contohBobotData[kriteria]) {
            input.value = contohBobotData[kriteria];
        }
    });
    
    hitungTotalBobot();
}

function updateChart() {
    const labels = [];
    const data = [];
    const colors = [];
    
    document.querySelectorAll('.bobot-input').forEach(function(input, index) {
        const kriteria = input.name.replace('bobot_', '');
        labels.push(kriteria);
        data.push(parseFloat(input.value) || 0);
        colors.push(`hsl(${index * 360 / 13}, 70%, 60%)`);
    });
    
    if (bobotChart) {
        bobotChart.destroy();
    }
    
    const ctx = document.getElementById('bobotChart');
    if (ctx) {
        try {
            bobotChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: colors,
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
                                    const percentage = (context.parsed * 100).toFixed(1);
                                    return context.label + ': ' + context.parsed.toFixed(3) + ' (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });
            console.log('Bobot chart created successfully');
        } catch (error) {
            console.error('Error creating bobot chart:', error);
        }
    } else {
        console.error('Canvas bobotChart not found');
    }
}

// Event listeners
$(document).ready(function() {
    console.log('Bobot page loaded');
    
    document.querySelectorAll('.bobot-input').forEach(function(input) {
        input.addEventListener('input', hitungTotalBobot);
    });
    
    hitungTotalBobot();
});

// Validasi form sebelum submit
document.getElementById('bobotForm').addEventListener('submit', function(e) {
    let total = 0;
    document.querySelectorAll('.bobot-input').forEach(function(input) {
        total += parseFloat(input.value) || 0;
    });
    
    if (Math.abs(total - 1.0) >= 0.001) {
        e.preventDefault();
        Swal.fire({
            title: 'Error!',
            text: 'Total bobot harus sama dengan 1.0 (saat ini: ' + total.toFixed(3) + ')',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    }
});
</script>

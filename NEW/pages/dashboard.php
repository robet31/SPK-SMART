<?php
$kriteria = $smart->getKriteria();
$alternatif = $smart->getAlternatif();

// Hitung distribusi kriteria
$benefit_count = 0;
$cost_count = 0;
foreach($kriteria as $k) {
    if($k['jenis_kriteria'] == 'benefit') {
        $benefit_count++;
    } else {
        $cost_count++;
    }
}
?>

<div class="row">
    <div class="col-12">
        <h1 class="mb-4">
            <i class="fas fa-tachometer-alt text-primary me-3"></i>
            Dashboard SPK SMART
        </h1>
        <p class="lead text-muted">Sistem Pendukung Keputusan untuk Seleksi Resource dalam Mobile Crowd Computing menggunakan metode SMART</p>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="stats-card">
            <h3><?= count($alternatif) ?></h3>
            <p class="mb-0"><i class="fas fa-mobile-alt me-2"></i>Total Alternatif (SMD)</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stats-card">
            <h3><?= count($kriteria) ?></h3>
            <p class="mb-0"><i class="fas fa-list me-2"></i>Total Kriteria</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stats-card">
            <h3>2</h3>
            <p class="mb-0"><i class="fas fa-calculator me-2"></i>Metode Pembobotan</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card method-card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Tentang Metode SMART</h5>
            </div>
            <div class="card-body">
                <p><strong>Simple Multi Attribute Rating Technique (SMART)</strong> adalah metode pengambilan keputusan multi-kriteria yang dikembangkan oleh Edwards pada tahun 1971. Metode ini menggunakan pendekatan linear additive untuk mengevaluasi alternatif berdasarkan beberapa kriteria.</p>
                
                <h6 class="text-primary mt-3">Karakteristik SMART:</h6>
                <ul>
                    <li><strong>Sederhana:</strong> Mudah dipahami dan diimplementasikan</li>
                    <li><strong>Fleksibel:</strong> Dapat menangani kriteria benefit dan cost</li>
                    <li><strong>Transparan:</strong> Proses perhitungan dapat diverifikasi</li>
                    <li><strong>Konsisten:</strong> Hasil yang stabil dan dapat diandalkan</li>
                </ul>
                
                <h6 class="text-primary mt-3">Langkah-langkah SMART:</h6>
                <ol>
                    <li><span class="step-indicator">1</span><strong>Identifikasi Kriteria:</strong> Tentukan kriteria evaluasi dan jenis (benefit/cost)</li>
                    <li><span class="step-indicator">2</span><strong>Penentuan Bobot:</strong> Tetapkan bobot kepentingan setiap kriteria (Σw = 1)</li>
                    <li><span class="step-indicator">3</span><strong>Normalisasi:</strong> Konversi nilai ke skala 0-1</li>
                    <li><span class="step-indicator">4</span><strong>Perhitungan Skor:</strong> Hitung skor akhir dengan weighted sum</li>
                    <li><span class="step-indicator">5</span><strong>Ranking:</strong> Urutkan alternatif berdasarkan skor tertinggi</li>
                </ol>

                <div class="formula-box">
                    <strong>Formula Normalisasi SMART:</strong><br><br>
                    <strong>Kriteria Benefit (semakin besar semakin baik):</strong><br>
                    n<sub>ij</sub> = (x<sub>ij</sub> - min<sub>j</sub>) / (max<sub>j</sub> - min<sub>j</sub>)<br><br>
                    
                    <strong>Kriteria Cost (semakin kecil semakin baik):</strong><br>
                    n<sub>ij</sub> = (max<sub>j</sub> - x<sub>ij</sub>) / (max<sub>j</sub> - min<sub>j</sub>)<br><br>
                    
                    <strong>Skor Akhir (Weighted Sum):</strong><br>
                    S<sub>i</sub> = Σ(w<sub>j</sub> × n<sub>ij</sub>)<br><br>
                    
                    <strong>Dimana:</strong><br>
                    - n<sub>ij</sub> = nilai normalisasi alternatif i pada kriteria j<br>
                    - x<sub>ij</sub> = nilai asli alternatif i pada kriteria j<br>
                    - w<sub>j</sub> = bobot kriteria j<br>
                    - S<sub>i</sub> = skor akhir alternatif i
                </div>

                <h6 class="text-primary mt-3">Contoh Perhitungan:</h6>
                <div class="small">
                    <strong>Misal:</strong> Kriteria C1 (Benefit), nilai: A1=2.2, A2=1.5, A3=2.5<br>
                    min = 1.5, max = 2.5<br>
                    <strong>Normalisasi:</strong><br>
                    - A1: (2.2-1.5)/(2.5-1.5) = 0.7<br>
                    - A2: (1.5-1.5)/(2.5-1.5) = 0.0<br>
                    - A3: (2.5-1.5)/(2.5-1.5) = 1.0<br>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card method-card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-weight-hanging me-2"></i>Metode Pembobotan</h5>
            </div>
            <div class="card-body">
                <h6 class="text-primary">1. Pembobotan Manual (Subjective Weighting)</h6>
                <p>Penentuan bobot berdasarkan preferensi dan kepentingan subjektif dari pengambil keputusan atau expert judgment.</p>
                
                <div class="formula-box small">
                    <strong>Metode Pembobotan Manual:</strong><br>
                    • <strong>Direct Assignment:</strong> w<sub>j</sub> langsung diberikan<br>
                    • <strong>Point Allocation:</strong> Bagi 100 poin ke n kriteria<br>
                    • <strong>Percentage Method:</strong> w<sub>j</sub> = P<sub>j</sub>/100<br>
                    • <strong>Ranking Method:</strong> Urutkan lalu beri bobot<br><br>
                    <strong>Constraint:</strong> Σw<sub>j</sub> = 1, 0 ≤ w<sub>j</sub> ≤ 1
                </div>
                
                <h6 class="text-primary mt-3">2. Rank Order Centroid (ROC)</h6>
                <p>Metode objektif untuk menentukan bobot berdasarkan urutan kepentingan kriteria tanpa memerlukan perbandingan berpasangan.</p>
                
                <div class="formula-box small">
                    <strong>Formula ROC:</strong><br>
                    w<sub>j</sub> = (1/n) × Σ<sub>k=j</sub><sup>n</sup> (1/k)<br><br>
                    
                    <strong>Langkah Perhitungan:</strong><br>
                    1. Urutkan kriteria berdasarkan kepentingan<br>
                    2. Hitung bobot untuk setiap urutan j:<br>
                    &nbsp;&nbsp;&nbsp;w<sub>1</sub> = (1/n) × (1/1 + 1/2 + ... + 1/n)<br>
                    &nbsp;&nbsp;&nbsp;w<sub>2</sub> = (1/n) × (1/2 + 1/3 + ... + 1/n)<br>
                    &nbsp;&nbsp;&nbsp;...<br>
                    &nbsp;&nbsp;&nbsp;w<sub>n</sub> = (1/n) × (1/n)<br><br>
                    
                    <strong>Contoh untuk n=3:</strong><br>
                    w<sub>1</sub> = (1/3) × (1/1 + 1/2 + 1/3) = 0.611<br>
                    w<sub>2</sub> = (1/3) × (1/2 + 1/3) = 0.278<br>
                    w<sub>3</sub> = (1/3) × (1/3) = 0.111
                </div>

                <h6 class="text-primary mt-3">Perbandingan Metode:</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Aspek</th>
                                <th>Manual</th>
                                <th>ROC</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Subjektivitas</td>
                                <td>Tinggi</td>
                                <td>Rendah</td>
                            </tr>
                            <tr>
                                <td>Konsistensi</td>
                                <td>Bervariasi</td>
                                <td>Konsisten</td>
                            </tr>
                            <tr>
                                <td>Kemudahan</td>
                                <td>Mudah</td>
                                <td>Sangat Mudah</td>
                            </tr>
                            <tr>
                                <td>Fleksibilitas</td>
                                <td>Tinggi</td>
                                <td>Sedang</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    <a href="index.php?page=bobot" class="btn btn-primary me-2">
                        <i class="fas fa-edit me-1"></i>Input Bobot Manual
                    </a>
                    <a href="index.php?page=roc" class="btn btn-outline-primary">
                        <i class="fas fa-calculator me-1"></i>Hitung ROC
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Distribusi Kriteria</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="chart-container chart-small">
                            <canvas id="kriteriaChart" width="400" height="300"></canvas>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-primary">Kriteria Benefit (Semakin Besar Semakin Baik):</h6>
                        <ul class="list-group list-group-flush">
                            <?php foreach($kriteria as $k): ?>
                                <?php if($k['jenis_kriteria'] == 'benefit'): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?= $k['nama_kriteria'] ?>
                                        <span class="badge bg-success rounded-pill"><?= $k['id_kriteria'] ?></span>
                                    </li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>

                        <h6 class="text-primary mt-3">Kriteria Cost (Semakin Kecil Semakin Baik):</h6>
                        <ul class="list-group list-group-flush">
                            <?php foreach($kriteria as $k): ?>
                                <?php if($k['jenis_kriteria'] == 'cost'): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?= $k['nama_kriteria'] ?>
                                        <span class="badge bg-danger rounded-pill"><?= $k['id_kriteria'] ?></span>
                                    </li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    console.log('Dashboard loaded, creating chart...');
    console.log('Benefit count:', <?= $benefit_count ?>);
    console.log('Cost count:', <?= $cost_count ?>);
    
    // Chart untuk distribusi kriteria
    const ctx = document.getElementById('kriteriaChart');
    console.log('Canvas element:', ctx);
    
    if (ctx) {
        try {
            const kriteriaChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Benefit', 'Cost'],
                    datasets: [{
                        data: [<?= $benefit_count ?>, <?= $cost_count ?>],
                        backgroundColor: [
                            '#27ae60',
                            '#e74c3c'
                        ],
                        borderWidth: 3,
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
                                padding: 20,
                                font: {
                                    size: 14
                                }
                            }
                        },
                        title: {
                            display: true,
                            text: 'Distribusi Jenis Kriteria',
                            font: {
                                size: 16,
                                weight: 'bold'
                            }
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
            console.log('Chart created successfully');
        } catch (error) {
            console.error('Error creating chart:', error);
        }
    } else {
        console.error('Canvas element not found');
    }
});
</script>

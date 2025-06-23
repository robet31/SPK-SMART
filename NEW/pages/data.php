<?php
$alternatif = $smart->getAlternatif();
$kriteria = $smart->getKriteria();
$data_nilai = $smart->getNilaiAlternatif();

// Organisir data dalam bentuk matrix
$matrix = [];
foreach ($data_nilai as $nilai) {
    $matrix[$nilai['id_alternatif']][$nilai['id_kriteria']] = $nilai['nilai'];
}
?>

<div class="row">
    <div class="col-12">
        <h1 class="mb-4">
            <i class="fas fa-database text-primary me-3"></i>
            Data Alternatif (Smart Mobile Devices)
        </h1>
        <p class="lead text-muted">Data resource dari 50 Smart Mobile Device berdasarkan 13 kriteria dari artikel MCC</p>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-table me-2"></i>Matrix Keputusan</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>SMD</th>
                                <?php foreach($kriteria as $k): ?>
                                    <th class="text-center" title="<?= $k['nama_kriteria'] ?>">
                                        <?= $k['id_kriteria'] ?>
                                        <?php if($k['jenis_kriteria'] == 'benefit'): ?>
                                            <i class="fas fa-arrow-up text-success ms-1"></i>
                                        <?php else: ?>
                                            <i class="fas fa-arrow-down text-danger ms-1"></i>
                                        <?php endif; ?>
                                    </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($alternatif as $alt): ?>
                                <tr>
                                    <td><strong><?= $alt['id_alternatif'] ?></strong></td>
                                    <?php foreach($kriteria as $k): ?>
                                        <td class="text-center">
                                            <?= isset($matrix[$alt['id_alternatif']][$k['id_kriteria']]) ? 
                                                number_format($matrix[$alt['id_alternatif']][$k['id_kriteria']], 2) : '-' ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Statistik Data</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Kriteria</th>
                                <th>Min</th>
                                <th>Max</th>
                                <th>Rata-rata</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($kriteria as $k): ?>
                                <?php
                                $values = [];
                                foreach($alternatif as $alt) {
                                    if(isset($matrix[$alt['id_alternatif']][$k['id_kriteria']])) {
                                        $values[] = $matrix[$alt['id_alternatif']][$k['id_kriteria']];
                                    }
                                }
                                $min = min($values);
                                $max = max($values);
                                $avg = array_sum($values) / count($values);
                                ?>
                                <tr>
                                    <td><strong><?= $k['id_kriteria'] ?></strong></td>
                                    <td><?= number_format($min, 2) ?></td>
                                    <td><?= number_format($max, 2) ?></td>
                                    <td><?= number_format($avg, 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Dataset</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <div class="text-center p-3 bg-light rounded">
                            <h3 class="text-primary"><?= count($alternatif) ?></h3>
                            <p class="mb-0">Total SMD</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center p-3 bg-light rounded">
                            <h3 class="text-primary"><?= count($kriteria) ?></h3>
                            <p class="mb-0">Total Kriteria</p>
                        </div>
                    </div>
                </div>

                <hr>

                <h6 class="text-primary">Sumber Data:</h6>
                <p class="small text-muted">
                    Data diambil dari artikel penelitian:<br>
                    <em>"A Comparative Analysis of Multi-Criteria Decision-Making Methods for Resource Selection in Mobile Crowd Computing"</em><br>
                    Tabel 7: Case 1 - Decision Matrix
                </p>

                <h6 class="text-primary">Keterangan:</h6>
                <ul class="small">
                    <li><i class="fas fa-arrow-up text-success"></i> Benefit: Semakin besar semakin baik</li>
                    <li><i class="fas fa-arrow-down text-danger"></i> Cost: Semakin kecil semakin baik</li>
                </ul>
            </div>
        </div>
    </div>
</div>

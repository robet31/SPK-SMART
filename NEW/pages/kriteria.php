<?php
$kriteria = $smart->getKriteria();
?>

<div class="row">
    <div class="col-12">
        <h1 class="mb-4">
            <i class="fas fa-list text-primary me-3"></i>
            Data Kriteria
        </h1>
        <p class="lead text-muted">13 Kriteria untuk evaluasi Smart Mobile Device dalam Mobile Crowd Computing</p>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-table me-2"></i>Daftar Kriteria</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama Kriteria</th>
                                <th>Jenis</th>
                                <th>Satuan</th>
                                <th>Deskripsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($kriteria as $k): ?>
                                <tr>
                                    <td><strong><?= $k['id_kriteria'] ?></strong></td>
                                    <td><?= $k['nama_kriteria'] ?></td>
                                    <td>
                                        <?php if($k['jenis_kriteria'] == 'benefit'): ?>
                                            <span class="badge bg-success">
                                                <i class="fas fa-arrow-up me-1"></i>Benefit
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">
                                                <i class="fas fa-arrow-down me-1"></i>Cost
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $k['satuan'] ?></td>
                                    <td><?= $k['deskripsi'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-arrow-up text-success me-2"></i>Kriteria Benefit</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Kriteria yang semakin besar nilainya semakin baik</p>
                <?php foreach($kriteria as $k): ?>
                    <?php if($k['jenis_kriteria'] == 'benefit'): ?>
                        <div class="border-start border-success border-3 ps-3 mb-3">
                            <h6 class="mb-1"><?= $k['id_kriteria'] ?> - <?= $k['nama_kriteria'] ?></h6>
                            <small class="text-muted"><?= $k['deskripsi'] ?></small>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-arrow-down text-danger me-2"></i>Kriteria Cost</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Kriteria yang semakin kecil nilainya semakin baik</p>
                <?php foreach($kriteria as $k): ?>
                    <?php if($k['jenis_kriteria'] == 'cost'): ?>
                        <div class="border-start border-danger border-3 ps-3 mb-3">
                            <h6 class="mb-1"><?= $k['id_kriteria'] ?> - <?= $k['nama_kriteria'] ?></h6>
                            <small class="text-muted"><?= $k['deskripsi'] ?></small>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Penjelasan Kriteria</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary">Kriteria Hardware (Fixed):</h6>
                        <ul>
                            <li><strong>C1 - CPU frequency:</strong> Kecepatan prosesor dalam GHz</li>
                            <li><strong>C2 - CPU cores:</strong> Jumlah core prosesor</li>
                            <li><strong>C3 - GPU frequency:</strong> Kecepatan GPU dalam GHz</li>
                            <li><strong>C4 - Total RAM:</strong> Kapasitas memori utama dalam GB</li>
                            <li><strong>C6 - Battery capacity:</strong> Kapasitas baterai dalam mAh</li>
                            <li><strong>C13 - GPU architecture:</strong> Teknologi fabrikasi GPU dalam nm</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-primary">Kriteria Status (Variable):</h6>
                        <ul>
                            <li><strong>C5 - Available memory:</strong> Memori yang tersedia saat ini</li>
                            <li><strong>C7 - Battery available:</strong> Persentase baterai tersisa</li>
                            <li><strong>C8 - Wi-Fi strength:</strong> Kekuatan sinyal Wi-Fi (1-5)</li>
                            <li><strong>C9 - CPU load:</strong> Beban CPU saat ini dalam persen</li>
                            <li><strong>C10 - GPU load:</strong> Beban GPU saat ini dalam persen</li>
                            <li><strong>C11 - CPU temperature:</strong> Suhu CPU dalam Celsius</li>
                            <li><strong>C12 - Battery temperature:</strong> Suhu baterai dalam Celsius</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

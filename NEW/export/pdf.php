<?php
require_once '../config/database.php';
require_once '../classes/SMART.php';

$database = new Database();
$db = $database->getConnection();
$smart = new SMART($db);

$metode = $_GET['metode'] ?? 'manual';
$hasil = $smart->getHasil($metode);

if (empty($hasil)) {
    die("Data tidak tersedia untuk metode: " . $metode);
}

// Set headers untuk PDF
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="Hasil_SMART_' . ucfirst($metode) . '_' . date('Y-m-d') . '.pdf"');

// Simple PDF generation (basic HTML to PDF)
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Hasil SMART - <?= ucfirst($metode) ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .info { margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .ranking { text-align: center; font-weight: bold; }
        .skor { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h2>HASIL PERHITUNGAN SMART</h2>
        <h3>Mobile Crowd Computing Resource Selection</h3>
    </div>
    
    <div class="info">
        <strong>Metode Pembobotan:</strong> <?= ucfirst($metode) ?><br>
        <strong>Tanggal:</strong> <?= date('d/m/Y H:i:s') ?><br>
        <strong>Total Alternatif:</strong> <?= count($hasil) ?> Smart Mobile Device
    </div>
    
    <table>
        <thead>
            <tr>
                <th width="10%">Ranking</th>
                <th width="15%">ID SMD</th>
                <th width="35%">Nama Alternatif</th>
                <th width="20%">Skor Akhir</th>
                <th width="20%">Kategori</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($hasil as $h): ?>
            <tr>
                <td class="ranking"><?= $h['ranking'] ?></td>
                <td><?= $h['id_alternatif'] ?></td>
                <td><?= $h['nama_alternatif'] ?></td>
                <td class="skor"><?= number_format($h['skor_akhir'], 6) ?></td>
                <td>
                    <?php 
                    if($h['ranking'] == 1) echo 'Terbaik';
                    elseif($h['ranking'] <= 5) echo 'Sangat Baik';
                    elseif($h['ranking'] <= 10) echo 'Baik';
                    elseif($h['ranking'] <= 20) echo 'Cukup';
                    else echo 'Kurang';
                    ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <div style="margin-top: 30px; font-size: 10px; color: #666;">
        <p><strong>Keterangan:</strong></p>
        <ul>
            <li>Metode SMART (Simple Multi Attribute Rating Technique)</li>
            <li>Normalisasi: Benefit (x-min)/(max-min), Cost (max-x)/(max-min)</li>
            <li>Skor Akhir: Σ(wi × ni)</li>
            <li>Data berdasarkan artikel MCC Resource Selection</li>
        </ul>
    </div>
</body>
</html>

<script>
window.print();
</script>

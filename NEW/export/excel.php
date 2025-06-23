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

// Set headers untuk download Excel
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="Hasil_SMART_' . ucfirst($metode) . '_' . date('Y-m-d') . '.xls"');
header('Pragma: no-cache');
header('Expires: 0');

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:html="http://www.w3.org/TR/REC-html40">

<Worksheet ss:Name="Hasil SMART <?= ucfirst($metode) ?>">
<Table>
    <Row>
        <Cell><Data ss:Type="String">HASIL PERHITUNGAN SMART</Data></Cell>
    </Row>
    <Row>
        <Cell><Data ss:Type="String">Metode Pembobotan: <?= ucfirst($metode) ?></Data></Cell>
    </Row>
    <Row>
        <Cell><Data ss:Type="String">Tanggal: <?= date('d/m/Y H:i:s') ?></Data></Cell>
    </Row>
    <Row></Row>
    
    <Row>
        <Cell><Data ss:Type="String">Ranking</Data></Cell>
        <Cell><Data ss:Type="String">ID Alternatif</Data></Cell>
        <Cell><Data ss:Type="String">Nama Alternatif</Data></Cell>
        <Cell><Data ss:Type="String">Skor Akhir</Data></Cell>
        <Cell><Data ss:Type="String">Kategori</Data></Cell>
    </Row>
    
    <?php foreach($hasil as $h): ?>
    <Row>
        <Cell><Data ss:Type="Number"><?= $h['ranking'] ?></Data></Cell>
        <Cell><Data ss:Type="String"><?= $h['id_alternatif'] ?></Data></Cell>
        <Cell><Data ss:Type="String"><?= $h['nama_alternatif'] ?></Data></Cell>
        <Cell><Data ss:Type="Number"><?= $h['skor_akhir'] ?></Data></Cell>
        <Cell><Data ss:Type="String">
            <?php 
            if($h['ranking'] == 1) echo 'Terbaik';
            elseif($h['ranking'] <= 5) echo 'Sangat Baik';
            elseif($h['ranking'] <= 10) echo 'Baik';
            elseif($h['ranking'] <= 20) echo 'Cukup';
            else echo 'Kurang';
            ?>
        </Data></Cell>
    </Row>
    <?php endforeach; ?>
</Table>
</Worksheet>
</Workbook>

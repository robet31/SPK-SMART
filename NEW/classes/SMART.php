<?php
require_once __DIR__ . '/../config/database.php';

class SMART {
    private $conn;
    private $table_kriteria = "kriteria";
    private $table_alternatif = "alternatif";
    private $table_nilai = "nilai_alternatif";
    private $table_bobot = "bobot_kriteria";
    private $table_hasil = "hasil_smart";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Mendapatkan semua kriteria
    public function getKriteria() {
        $query = "SELECT * FROM " . $this->table_kriteria . " ORDER BY id_kriteria";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Mendapatkan semua alternatif
    public function getAlternatif() {
        $query = "SELECT * FROM " . $this->table_alternatif . " ORDER BY id_alternatif";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Mendapatkan nilai alternatif
    public function getNilaiAlternatif() {
        $query = "SELECT na.*, k.jenis_kriteria 
                  FROM " . $this->table_nilai . " na 
                  JOIN " . $this->table_kriteria . " k ON na.id_kriteria = k.id_kriteria 
                  ORDER BY na.id_alternatif, na.id_kriteria";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Menyimpan bobot kriteria
    public function simpanBobot($bobot_data, $metode) {
        try {
            // Hapus bobot lama untuk metode yang sama
            $query = "DELETE FROM " . $this->table_bobot . " WHERE metode_pembobotan = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$metode]);

            // Simpan bobot baru
            $query = "INSERT INTO " . $this->table_bobot . " (id_kriteria, bobot, metode_pembobotan) VALUES (?, ?, ?)";
            $stmt = $this->conn->prepare($query);
            
            foreach ($bobot_data as $id_kriteria => $bobot) {
                $stmt->execute([$id_kriteria, $bobot, $metode]);
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    // Menghitung bobot ROC
    public function hitungBobotROC() {
        $kriteria = $this->getKriteria();
        $n = count($kriteria);
        $bobot_roc = [];
        
        for ($j = 1; $j <= $n; $j++) {
            $sum = 0;
            for ($k = $j; $k <= $n; $k++) {
                $sum += 1 / $k;
            }
            $bobot_roc[$kriteria[$j-1]['id_kriteria']] = $sum / $n;
        }
        
        return $bobot_roc;
    }

    // Normalisasi SMART
    public function normalisasiSMART($data_nilai) {
        $kriteria = $this->getKriteria();
        $alternatif = $this->getAlternatif();
        
        // Organisir data nilai
        $matrix = [];
        foreach ($data_nilai as $nilai) {
            $matrix[$nilai['id_alternatif']][$nilai['id_kriteria']] = $nilai['nilai'];
        }

        // Cari min dan max untuk setiap kriteria
        $min_max = [];
        foreach ($kriteria as $k) {
            $values = [];
            foreach ($alternatif as $a) {
                if (isset($matrix[$a['id_alternatif']][$k['id_kriteria']])) {
                    $values[] = $matrix[$a['id_alternatif']][$k['id_kriteria']];
                }
            }
            $min_max[$k['id_kriteria']] = [
                'min' => min($values),
                'max' => max($values),
                'jenis' => $k['jenis_kriteria']
            ];
        }

        // Normalisasi
        $normalized = [];
        foreach ($matrix as $id_alt => $nilai_alt) {
            foreach ($nilai_alt as $id_krit => $nilai) {
                $min = $min_max[$id_krit]['min'];
                $max = $min_max[$id_krit]['max'];
                $jenis = $min_max[$id_krit]['jenis'];
                
                if ($max == $min) {
                    $normalized[$id_alt][$id_krit] = 1;
                } else {
                    if ($jenis == 'benefit') {
                        // Benefit: (x - min) / (max - min)
                        $normalized[$id_alt][$id_krit] = ($nilai - $min) / ($max - $min);
                    } else {
                        // Cost: (max - x) / (max - min)
                        $normalized[$id_alt][$id_krit] = ($max - $nilai) / ($max - $min);
                    }
                }
            }
        }

        return $normalized;
    }

    // Hitung skor SMART
    public function hitungSkorSMART($metode_bobot = 'manual') {
        // Ambil data
        $data_nilai = $this->getNilaiAlternatif();
        $normalized = $this->normalisasiSMART($data_nilai);
        
        // Ambil bobot
        $query = "SELECT * FROM " . $this->table_bobot . " WHERE metode_pembobotan = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$metode_bobot]);
        $bobot_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $bobot = [];
        foreach ($bobot_data as $b) {
            $bobot[$b['id_kriteria']] = $b['bobot'];
        }

        // Hitung skor akhir
        $skor_akhir = [];
        foreach ($normalized as $id_alt => $nilai_norm) {
            $skor = 0;
            foreach ($nilai_norm as $id_krit => $nilai) {
                if (isset($bobot[$id_krit])) {
                    $skor += $nilai * $bobot[$id_krit];
                }
            }
            $skor_akhir[$id_alt] = $skor;
        }

        // Urutkan berdasarkan skor (descending)
        arsort($skor_akhir);
        
        // Beri ranking
        $ranking = 1;
        $hasil = [];
        foreach ($skor_akhir as $id_alt => $skor) {
            $hasil[] = [
                'id_alternatif' => $id_alt,
                'skor_akhir' => $skor,
                'ranking' => $ranking++
            ];
        }

        // Simpan hasil
        $this->simpanHasil($hasil, $metode_bobot);
        
        return $hasil;
    }

    // Simpan hasil perhitungan
    private function simpanHasil($hasil, $metode_bobot) {
        try {
            // Hapus hasil lama
            $query = "DELETE FROM " . $this->table_hasil . " WHERE metode_pembobotan = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$metode_bobot]);

            // Simpan hasil baru
            $query = "INSERT INTO " . $this->table_hasil . " (id_alternatif, skor_akhir, ranking, metode_pembobotan) VALUES (?, ?, ?, ?)";
            $stmt = $this->conn->prepare($query);
            
            foreach ($hasil as $h) {
                $stmt->execute([$h['id_alternatif'], $h['skor_akhir'], $h['ranking'], $metode_bobot]);
            }
        } catch (Exception $e) {
            // Handle error
        }
    }

    // Ambil hasil perhitungan
    public function getHasil($metode_bobot = 'manual') {
        $query = "SELECT h.*, a.nama_alternatif 
                  FROM " . $this->table_hasil . " h 
                  JOIN " . $this->table_alternatif . " a ON h.id_alternatif = a.id_alternatif 
                  WHERE h.metode_pembobotan = ? 
                  ORDER BY h.ranking";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$metode_bobot]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Ambil detail perhitungan untuk analisis
    public function getDetailPerhitungan($metode_bobot = 'manual') {
        $data_nilai = $this->getNilaiAlternatif();
        $normalized = $this->normalisasiSMART($data_nilai);
        
        // Ambil bobot
        $query = "SELECT * FROM " . $this->table_bobot . " WHERE metode_pembobotan = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$metode_bobot]);
        $bobot_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $bobot = [];
        foreach ($bobot_data as $b) {
            $bobot[$b['id_kriteria']] = $b['bobot'];
        }

        return [
            'data_asli' => $this->organizeDataMatrix($data_nilai),
            'data_normalisasi' => $normalized,
            'bobot' => $bobot
        ];
    }

    // Organisir data dalam bentuk matrix
    private function organizeDataMatrix($data_nilai) {
        $matrix = [];
        foreach ($data_nilai as $nilai) {
            $matrix[$nilai['id_alternatif']][$nilai['id_kriteria']] = $nilai['nilai'];
        }
        return $matrix;
    }
}
?>

<?php

require_once __DIR__ . '/../models/Hutang.php';
require_once __DIR__ . '/../models/Penjualan.php';
require_once __DIR__ . '/../helpers/format.php';

class HutangController {
    private $model;
    private $penjualanModel;

    public function __construct() {
        $this->model = new Hutang();
        $this->penjualanModel = new Penjualan();
    }

    public function index() {
        $filter = $_GET['filter'] ?? 'semua';
        
        switch ($filter) {
            case 'belum_bayar':
                $hutang = $this->model->getBelumBayar();
                break;
            case 'lunas':
                $hutang = $this->model->getLunas();
                break;
            case 'jatuh_tempo':
                $hutang = $this->model->getHutangJatuhTempo();
                break;
            default:
                $hutang = $this->model->getAll();
        }
        
        $summary = $this->model->getTotalHutang();
        require_once __DIR__ . '/../views/hutang/index.php';
    }

    public function updateStatus($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $status = $_POST['status'] ?? '';
            
            if (in_array($status, ['belum_bayar', 'lunas'])) {
                if ($this->model->updateStatus($id, $status)) {
                    $_SESSION['success'] = 'Status hutang berhasil diupdate';
                } else {
                    $_SESSION['error'] = 'Gagal mengupdate status hutang';
                }
            } else {
                $_SESSION['error'] = 'Status tidak valid';
            }
            
            redirect('/hutang');
        }
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->model->delete($id)) {
                $_SESSION['success'] = 'Data hutang berhasil dihapus';
            } else {
                $_SESSION['error'] = 'Gagal menghapus data hutang';
            }
            redirect('/hutang');
        }
    }
}
?>

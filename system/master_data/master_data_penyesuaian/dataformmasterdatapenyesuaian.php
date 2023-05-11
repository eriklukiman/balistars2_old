<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
include_once $BASE_URL_PHP . '/system/fungsinavigasi.php';
include_once $BASE_URL_PHP . '/library/fungsirupiah.php';
session_start();

$idUser    = '';
$tokenCSRF = '';

extract($_SESSION);

//DESKRIPSI ID USER
$idUserAsli = dekripsi($idUser, $kunciRahasia);

//MENGECEK APAKAH ID USER YANG LOGIN ADA PADA DATABASE
$sqlCekUser = $db->prepare('SELECT idUser from balistars_user where idUser=?');
$sqlCekUser->execute([$idUserAsli]);
$dataCekUser = $sqlCekUser->fetch();

//MENGECEK APAKAH USER INI BERHAK MENGAKSES MENU INI
$sqlCekMenu = $db->prepare('SELECT * from balistars_user_detail 
  inner join balistars_menu_sub 
  on balistars_menu_sub.idMenuSub = balistars_user_detail.idMenuSub
  where balistars_user_detail.idUser = ?
  and namaFolder = ?');
$sqlCekMenu->execute([
    $idUserAsli,
    'master_data_penyesuaian'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser) {
    header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

$idPenyesuaian = '';
$readonly      = '';
$tanggalPenyesuaian = date('Y-m-d');
$jenisPenyesuaian = '';

extract($_POST);



$sqlUpdate  = $db->prepare('SELECT * from balistars_penyesuaian
  where idPenyesuaian = ?');
$sqlUpdate->execute([$idPenyesuaian]);
$dataUpdate = $sqlUpdate->fetch();

if ($dataUpdate) {
    $tanggalPenyesuaian = $dataUpdate['tanggalPenyesuaian'] ?? '';
    $dataUpdate['nominal'] = ubahToRp($dataUpdate['nominal'] ?? '');

    $flag = 'update';
} else {
    $flag = 'tambah';
}

if ($flag == 'update') {
    $readonly = 'disabled';
}

?>
<form id="formMasterDataPenyesuaian">
    <input type="hidden" name="flag" value="<?= $flag ?>">
    <input type="hidden" name="idPenyesuaian" value="<?= $idPenyesuaian ?>">

    <div class="form-group row">
        <div class="col-sm-6">
            <label>Jenis Penyesuaian</label>
            <select name="jenisPenyesuaian" id="jenisPenyesuaian" class="form-control select2" style="width: 100%;" onchange="selectTipeBayar('<?= $idPenyesuaian ?>')" required>
                <?php
                $arrayTipe = array('Pembelian', 'Penjualan', 'Biaya', 'Uang Masuk');
                for ($i = 0; $i < count($arrayTipe); $i++) {
                    $selected = selected($arrayTipe[$i], $dataUpdate['jenisPenyesuaian'] ?? '');
                ?>
                    <option value="<?= $arrayTipe[$i] ?>" <?= $selected ?>> <?= $arrayTipe[$i] ?> </option>
                <?php
                }
                ?>
            </select>
        </div>
        <div class="col-sm-6">
            <label>Status <span id="textTipeBayar"><?= $dataUpdate['tipePembayaran'] ?? '' ?></span></label>
            <select name="status" class="form-control select2" style="width: 100%;" required>
                <option value="">Pilih Status</option>
                <?php
                $arrayStatus = array('Naik', 'Turun');
                for ($i = 0; $i < count($arrayStatus); $i++) {
                    $selected = selected($arrayStatus[$i], $dataUpdate['status']);
                ?>
                    <option value="<?= $arrayStatus[$i] ?>" <?= $selected ?>> <?= $arrayStatus[$i] ?> </option>
                <?php
                }
                ?>
            </select>
        </div>
    </div>
    <div class="form-group row">
        <div class="col-sm-6">
            <label>Tanggal Awal Target</label>
            <div class="input-group date" data-date-autoclose="true" data-provide="datepicker" data-date-format="yyyy-mm-dd">
                <input type="tanggal" class="form-control" name="tanggalPenyesuaian" id="tanggalPenyesuaian" value="<?= $tanggalPenyesuaian ?>" autocomplete="off">
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="button">
                        <i class="fa fa-calendar"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <label>Cabang</label>
            <select name="idCabang" class="form-control select2" style="width: 100%;" required>
                <option value=""> Pilih Cabang </option>
                <?php
                $sqlCabang = $db->prepare('SELECT * FROM balistars_cabang where statusCabang = ? order by namaCabang');
                $sqlCabang->execute(['Aktif']);
                $dataCabang = $sqlCabang->fetchAll();
                foreach ($dataCabang as $data) {
                    $selected = selected($data['idCabang'], $dataUpdate['idCabang'] ?? '');
                ?>
                    <option value="<?= $data['idCabang'] ?>" <?= $selected ?>><?= $data['namaCabang'] ?></option>
                <?php
                }
                ?>
            </select>
        </div>
    </div>
    <div class="form-group row">
        <div class="col-sm-6">
            <label>Nominal</label>
            <input type="text" class="form-control" placeholder="Input Nominal" name="nominal" id="nominal" onkeyup="ubahToRp('#nominal')" value="<?= $dataUpdate['nominal'] ?? '' ?>">
        </div>
        <div class="col-sm-6" id=boxTipePembayaran>

        </div>
    </div>
    <div class="form-group row">
        <div class="col-sm-12">
            <label>Keterangan</label>
            <textarea class="form-control" id="keterangan" name="keterangan"> <?= $dataUpdate['keterangan'] ?? '' ?></textarea>
        </div>
    </div>
    <div class="form-group">
        <button type="button" class="btn btn-primary" onclick="prosesMasterDataPenyesuaian()">
            <i class="fa fa-save pr-2"></i> <strong>SAVE</strong>
        </button>
    </div>
</form>
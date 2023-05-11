<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
include_once $BASE_URL_PHP . '/library/fungsirupiah.php';
include_once $BASE_URL_PHP . '/library/fungsitanggal.php';
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
  'laporan_penyusutan_aktiva'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

$tanggalPenjualan = date('d-m-Y');
extract($_REQUEST);


$sqlUpdate  = $db->prepare('SELECT * 
  FROM balistars_pembelian_mesin_detail 
  where idPembelianDetail=?');
$sqlUpdate->execute([$idPembelianDetail]);
$dataUpdate = $sqlUpdate->fetch();

?>
<form id="formJualAktiva">
  <input type="hidden" name="idPembelianDetail"  value="<?=$idPembelianDetail?>">

  <div class="form-group row">
    <div class="col-sm-6">
      <label>Nama Aktiva</label>
      <input type="text" name="namaAktiva" id="namaAktiva" class="form-control" value="<?=$dataUpdate['namaBarang']?>" readonly>
    </div>
    <div class="col-sm-6">
      <label>Tanggal Jual</label>
      <div class="input-group date"  data-date-autoclose="true" data-provide="datepicker"  data-date-format="dd-mm-yyyy">
        <input type="tanggal" class="form-control form-control-lg" name="tanggalPenjualan" id="tanggalPenjualan" value="<?=$tanggalPenjualan?>"  autocomplete="off">
        <div class="input-group-append">                                 
          <button class="btn btn-outline-secondary" type="button">
            <i class="fa fa-calendar"></i>
          </button>
        </div>
      </div>
    </div>
  </div>  
  <div class="form-group row">
    <div class="col-sm-6">
      <label>DPP</label>
      <input type="text" class="form-control" placeholder="0" name="dpp" id="dpp" onkeyup="ubahToRp('#dpp')" value="<?=$dataUpdate['dpp']??''?>">
    </div>
    <div class="col-sm-6">
      <label>PPN</label>
      <input type="text" class="form-control" placeholder="0" name="ppn" id="ppn" onkeyup="ubahToRp('#ppn')" value="<?=$dataUpdate['ppn']??''?>">
    </div>
  </div> 
  <div class="form-group row">
    <div class="col-sm-6">
      <label>Bank</label>
      <select name="idBank" class="form-control select2" style="width: 100%;" required>
        <option value=""> Pilih Bank </option>
        <?php
        $sqlBank=$db->prepare('SELECT * FROM balistars_bank 
          where statusBank=? 
          order by namaBank');
        $sqlBank->execute(['Aktif']);
        $dataBank = $sqlBank->fetchAll();
        foreach($dataBank as $data){
          $selected=selected($data['idBank'],$dataUpdate['idBank']??'');
          ?>
          <option value="<?=$data['idBank']?>" <?=$selected?>><?=$data['namaBank']?></option>
          <?php
        }
        ?>
      </select>
    </div>
    <div class="col-sm-6">
      <label>Keterangan</label>
      <input type="text" name="keterangan" id="keterangan" class="form-control" placeholder="keterangan">
    </div>
  </div> 
  <div class="form-group">
    <button type="button" class="btn btn-primary" onclick="prosesJualAktiva()">
      <i class="fa fa-save"></i> Save
    </button>
  </div>
</form>
<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
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
  'form_saldo_awal_cabang'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

$idCabangCash = '';
$readonly      = '';
$tanggalCabangCash = date('Y-m-d');
extract($_REQUEST);

if($flag == 'update'){
  $readonly = 'disabled';
}

$sqlUpdate  = $db->prepare('SELECT * from balistars_cabang_cash
  where idCabangCash = ?');
$sqlUpdate->execute([$idCabangCash]);
$dataUpdate = $sqlUpdate->fetch();

if($dataUpdate){
  $tanggalCabangCash = $dataUpdate['tanggalCabangCash']??'';
  $dataUpdate['nilai'] = ubahToRp($dataUpdate['nilai']);
  
}
?>
<form id="formSaldoAwal">
  <input type="hidden" name="flag" value="<?=$flag?>">
  <input type="hidden" name="idCabangCash"  value="<?=$idCabangCash?>">
  <input type="hidden" name="parameterOrder" value="<?=$parameterOrder?>">


  <div class="form-group row">
    <div class="col-sm-6">
      <label>Tanggal Pemasukan</label>
      <div class="input-group date"  data-date-autoclose="true" data-provide="datepicker"  data-date-format="yyyy-mm-dd">
        <input type="tanggal" class="form-control" name="tanggalCabangCash" id="tanggalCabangCash" value="<?=$tanggalCabangCash?>"  autocomplete="off">
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
        $sqlCabang=$db->prepare('SELECT * FROM balistars_cabang where statusCabang=? order by namaCabang');
        $sqlCabang->execute(['Aktif']);
        $dataCabang = $sqlCabang->fetchAll();
        foreach($dataCabang as $data){
          $selected=selected($data['idCabang'],$dataUpdate['idCabang']??'');
          ?>
          <option value="<?=$data['idCabang']?>" <?=$selected?>><?=$data['namaCabang']?></option>
          <?php
        }
        ?>
      </select>
    </div>
  </div>  
  <div class="form-group row">
    <div class="col-sm-6">
      <label>Nilai</label>
      <input type="text" class="form-control" placeholder="nilai" name="nilai" id="nilai" onkeyup="ubahToRp('#nilai')" value="<?=$dataUpdate['nilai']??''?>">
    </div>
    <div class="col-sm-6">
      <label>Keterangan</label>
      <input type="text" class="form-control" placeholder="keterangan" name="keterangan" id="keterangan" value="<?=$dataUpdate['keterangan']??''?>">
    </div>
  </div>
  <div class="form-group">
    <button type="button" class="btn btn-primary" onclick="prosesSaldoAwal()">
      <i class="fa fa-save"></i> Save
    </button>
  </div>
</form>
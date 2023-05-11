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
  'form_memorial'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

$idMemorial = '';
$tanggalMemorial=date('d-m-Y');
extract($_REQUEST);

$sqlUpdate  = $db->prepare('
  SELECT * FROM balistars_memorial 
  where idMemorial=?
  and statusMemorial=?');
$sqlUpdate->execute([
  $idMemorial,
  'Aktif']);
$dataUpdate = $sqlUpdate->fetch();

if($dataUpdate){
  $dataUpdate['nilaiMemorial']=ubahToRp($dataUpdate['nilaiMemorial']??'');
  $tanggalMemorial=konversiTanggal($dataUpdate['tanggalMemorial']??'');
}

?>
<form id="formMemorial">
  <input type="hidden" name="flag" id="flag" value="<?=$flag?>">
  <input type="hidden" name="idMemorial"  value="<?=$dataUpdate['idMemorial']?>">
  <input type="hidden" name="parameterOrder" value="<?=$parameterOrder?>">

  <div class="form-group row">
    <div class="col-sm-6">
      <label>Kode ACC</label>
      <select name="kodeNeracaLajur" class="form-control select2" id="kodeNeracaLajur" style="width: 100%;" required>
        <?php
        $sqlMemorial=$db->prepare('SELECT * FROM balistars_kode_neraca_lajur where statusKodeNeracaLajur=? order by kodeNeracaLajur');
        $sqlMemorial->execute(['Aktif']);
        $dataMemorial = $sqlMemorial->fetchAll();
        foreach($dataMemorial as $data){
          $selected=selected($data['kodeNeracaLajur'],$dataUpdate['kodeNeracaLajur']??'');
          ?>
          <option value="<?=$data['kodeNeracaLajur']?>" <?=$selected?>><?=$data['kodeNeracaLajur']?> (<?=$data['keterangan']?>)</option>
          <?php
        }
        ?>
      </select>
    </div>
    <div class="col-sm-6">
      <label>Tanggal Memorial</label>
      <div class="input-group date"  data-date-autoclose="true" data-provide="datepicker"  data-date-format="dd-mm-yyyy">
        <input type="tanggal" class="form-control form-control-lg" name="tanggalMemorial" id="tanggalMemorial" value="<?=$tanggalMemorial?>"  autocomplete="off">
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
      <label>Nilai Memorial</label>
      <input type="text" class="form-control form-control-lg" placeholder="Nilai Setor" name="nilaiMemorial" id="nilaiMemorial" onkeyup="ubahToRp('#nilaiMemorial')" value="<?=$dataUpdate['nilaiMemorial']?>">
    </div>
    <div class="col-sm-6">
      <label>Tipe</label>
      <select name="tipe" class="form-control select2" style="width: 100%;" required>
        <?php
        $arrayTipe=array('A1','A2');
        for($i=0; $i<count($arrayTipe); $i++){
          $selected=selected($arrayTipe[$i],$dataUpdate['tipe']??'');
          ?>
          <option value="<?=$arrayTipe[$i]?>" <?=$selected?>> <?=$arrayTipe[$i]?> </option>
          <?php
        }
        ?>
      </select>
    </div>
  </div>
  <div class="form-group">
    <button type="button" class="btn btn-primary" onclick="prosesMemorial()">
      <i class="fa fa-save"></i> Save
    </button>
  </div>
</form>
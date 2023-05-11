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
  'form_persediaan_global'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

$idPersediaan = '';
$tanggalPersediaan=date('d-m-Y');
extract($_REQUEST);

$sqlUpdate  = $db->prepare('
  SELECT * FROM balistars_persediaan_global 
  where idPersediaan=?
  and statusPersediaan=?');
$sqlUpdate->execute([
  $idPersediaan,
  'Aktif']);
$dataUpdate = $sqlUpdate->fetch();

if($dataUpdate){
  if($dataUpdate['nilaiPersediaan']<0){
    $nilaiPersediaan=0-$dataUpdate['nilaiPersediaan'];
  }
  else{
    $nilaiPersediaan=$dataUpdate['nilaiPersediaan'];
  }
  $dataUpdate['nilaiPersediaan']=ubahToRp($nilaiPersediaan);
  $tanggalPersediaan=konversiTanggal($dataUpdate['tanggalPersediaan']??'');
}

?>
<form id="formPersediaanGlobal">
  <input type="hidden" name="flag" id="flag" value="<?=$flag?>">
  <input type="hidden" name="idPersediaan"  value="<?=$dataUpdate['idPersediaan']?>">
  <input type="hidden" name="parameterOrder" value="<?=$parameterOrder?>">

  <div class="form-group row">
    <div class="col-sm-6">
      <label>Tanggal</label>
      <div class="input-group date"  data-date-autoclose="true" data-provide="datepicker"  data-date-format="dd-mm-yyyy">
        <input type="tanggal" class="form-control form-control-lg" name="tanggalPersediaan" id="tanggalPersediaan" value="<?=$tanggalPersediaan?>"  autocomplete="off">
        <div class="input-group-append">                                            
          <button class="btn btn-outline-secondary" type="button">
            <i class="fa fa-calendar"></i>
          </button>
        </div>
      </div>
    </div>
    <div class="col-sm-6">
      <label>Debet/Kredit</label>
      <select name="tipeInput" class="form-control select2">
        <?php  
        $arrayTipe = array('Debet','Kredit');
        for ($i=0; $i < count($arrayTipe) ; $i++) { 
          if($arrayTipe[$i]=='Kredit' && $dataUpdate['nilaiPersediaan']<0){
            $selected='selected';
          }
          else{
            $selected='';
          }
          ?>
          <option value="<?=$arrayTipe[$i]?>"<?=$selected?>><?=$arrayTipe[$i]?></option>
          <?php
        }
        ?>
      </select>
    </div>
  </div> 
  <div class="form-group row">
    <div class="col-sm-6">
      <label>Nilai</label>
      <input type="text" class="form-control form-control-lg" placeholder="Nilai" name="nilaiPersediaan" id="nilaiPersediaan" onkeyup="ubahToRp('#nilaiPersediaan')" value="<?=$dataUpdate['nilaiPersediaan']?>">
    </div>
    <div class="col-sm-6">
      <label>Cabang</label>
      <select name="idCabang" class="form-control select2" id="idCabang" required>
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
  <div class="form-group">
    <button type="button" class="btn btn-primary" onclick="prosesPersediaanGlobal()">
      <i class="fa fa-save"></i> Save
    </button>
  </div>
</form>
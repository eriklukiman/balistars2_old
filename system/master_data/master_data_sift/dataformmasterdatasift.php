<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
include_once $BASE_URL_PHP . '/system/fungsinavigasi.php';
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
  'master_data_sift'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

$idSift = '';
$readonly      = '';
$tanggalBerlaku = date('d-m-Y');
extract($_REQUEST);

if($flag == 'update'){
  $readonly = 'disabled';
}

$sqlUpdate  = $db->prepare('SELECT * from balistars_sift
  where idSift = ?');
$sqlUpdate->execute([$idSift]);
$dataUpdate = $sqlUpdate->fetch();
if($dataUpdate){
  $tanggalBerlaku=konversiTanggal($dataUpdate['tanggalBerlaku']??'');
}
?>
<form id="formMasterDataSift">
  <input type="hidden" name="flag" value="<?=$flag?>">
  <input type="hidden" name="idSift"  value="<?=$idSift?>">
  <input type="hidden" name="parameterOrder" value="<?=$parameterOrder?>">


  <div class="form-group row">
    <div class="col-sm-6">
      <label>Cabang</label>
       <select name="idCabang" id="idCabang" class="form-control select2">
        <option value=""> Pilih Cabang </option>
        <?php
        $sqlCabang=$db->prepare('SELECT * FROM balistars_cabang where statusCabang=? order by namaCabang');
        $sqlCabang->execute(['Aktif']);
        $dataCabang = $sqlCabang->fetchAll();
        foreach($dataCabang as $data){
          $selected=selected($data['idCabang'],$dataUpdate['idCabang']??'');
          ?>
          <option value="<?=$data['idCabang']?>" <?=$selected?>> <?=$data['namaCabang']?> </option>
          <?php
        }
        ?>
      </select>
    </div>
    <div class="col-sm-6">
      <label>Tanggal Berlaku</label>
      <div class="input-group date"  data-date-autoclose="true" data-provide="datepicker"  data-date-format="dd-mm-yyyy">
        <input type="tanggal" class="form-control" name="tanggalBerlaku" id="tanggalBerlaku" value="<?=$tanggalBerlaku?>"  autocomplete="off">
        <div class="input-group-append">                                            
          <button class="btn btn-outline-secondary" type="button">
            <i class="fa fa-calendar"></i>
          </button>
        </div>
      </div>
    </div>
  </div>  
  <div class="row form-group">
    <input hidden id="input_starttime" class="form-control timepicker">
    <label for="input_starttime"></label>
    <div class="form-group col-sm-6">
       <label>Sift Normal (Senin-Jumat)</label>
        <div>
          <input type="time" name="siftNormalNormal" value="<?=$dataUpdate['siftNormalNormal']??''?>" id="inputMDEx1" class="form-control" required >
        </div>
    </div>
     <div class="form-group col-sm-6">
        <label>Sift Normal (Sabtu-Minggu)</label>
        <div>
          <input type="time" name="siftNormalWeekend" value="<?=$dataUpdate['siftNormalWeekend']??''?>" id="inputMDEx1" class="form-control" required >
        </div>
    </div>
  </div>
  <div class="form-group row">
    <div class="form-group col-sm-6">
       <label>Sift Pagi (Senin-Jumat)</label>
        <div>
          <input type="time" name="siftPagiNormal" value="<?=$dataUpdate['siftPagiNormal']??''?>" id="inputMDEx1" class="form-control" required >
        </div>
    </div>
     <div class="form-group col-sm-6">
        <label>Sift Pagi (Sabtu-Minggu)</label>
        <div>
          <input type="time" name="siftPagiWeekend" value="<?=$dataUpdate['siftPagiWeekend']??''?>" id="inputMDEx1" class="form-control" required >
        </div>
    </div>
  </div>
  <div class="form-group row">
    <div class="form-group col-sm-6">
       <label>Sift Middle 1 (Senin-Jumat)</label>
        <div>
          <input type="time" name="siftMiddleNormal" value="<?=$dataUpdate['siftMiddleNormal']??''?>" id="inputMDEx1" class="form-control" required >
        </div>
    </div>
     <div class="form-group col-sm-6">
        <label>Sift Middle 1 (Sabtu-Minggu)</label>
        <div>
          <input type="time" name="siftMiddleWeekend" value="<?=$dataUpdate['siftMiddleWeekend']??''?>" id="inputMDEx1" class="form-control" required >
        </div>
    </div>
  </div>
  <div class="form-group row">
    <div class="form-group col-sm-6">
       <label>Sift Middle 2 (Senin-Jumat)</label>
        <div>
          <input type="time" name="siftMiddle2Normal" value="<?=$dataUpdate['siftMiddle2Normal']??''?>" id="inputMDEx1" class="form-control" required >
        </div>
    </div>
     <div class="form-group col-sm-6">
        <label>Sift Middle 2 (Sabtu-Minggu)</label>
        <div>
          <input type="time" name="siftMiddle2Weekend" value="<?=$dataUpdate['siftMiddle2Weekend']??''?>" id="inputMDEx1" class="form-control" required >
        </div>
    </div>
  </div>
  <div class="form-group row">
    <div class="form-group col-sm-6">
       <label>Sift Middle 3 (Senin-Jumat)</label>
        <div>
          <input type="time" name="siftMiddle3Normal" value="<?=$dataUpdate['siftMiddle3Normal']??''?>" id="inputMDEx1" class="form-control" required >
        </div>
    </div>
     <div class="form-group col-sm-6">
        <label>Sift Middle 3 (Sabtu-Minggu)</label>
        <div>
          <input type="time" name="siftMiddle3Weekend" value="<?=$dataUpdate['siftMiddle3Weekend']??''?>" id="inputMDEx1" class="form-control" required >
        </div>
    </div>
  </div>
  <div class="form-group row">
    <div class="form-group col-sm-6">
       <label>Sift Siang (Senin-Jumat)</label>
        <div>
          <input type="time" name="siftSiangNormal" value="<?=$dataUpdate['siftSiangNormal']??''?>" id="inputMDEx1" class="form-control" required >
        </div>
    </div>
     <div class="form-group col-sm-6">
        <label>Sift Siang (Sabtu-Minggu)</label>
        <div>
          <input type="time" name="siftSiangWeekend" value="<?=$dataUpdate['siftSiangWeekend']??''?>" id="inputMDEx1" class="form-control" required >
        </div>
    </div>
  </div>                  
  <div class="form-group">
    <button type="button" class="btn btn-primary" onclick="prosesMasterDataSift()">
      <i class="fa fa-save"></i> Save
    </button>
  </div>
</form>
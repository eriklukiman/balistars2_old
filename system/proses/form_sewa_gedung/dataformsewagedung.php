<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
include_once $BASE_URL_PHP . '/system/fungsinavigasi.php';
include_once $BASE_URL_PHP . '/library/fungsirupiah.php';
include_once $BASE_URL_PHP . '/library/fungsitanggal.php';
include_once 'fungsinomor.php';
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
  'form_sewa_gedung'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

$idHutangGedung='';
$flag='';
$noNota='';
$success='';
$nilaiSewa='';
$tanggalSewa=date('d-m-Y');
$tanggalPenyusutan=date('d-m-Y');
extract($_REQUEST);

$sqlLogin  = $db->prepare('SELECT * FROM balistars_pegawai inner join balistars_user on balistars_pegawai.idPegawai=balistars_user.idPegawai inner join balistars_cabang on balistars_pegawai.idCabang=balistars_cabang.idCabang where balistars_user.idUser = ?');
$sqlLogin->execute([$idUserAsli]);
$dataLogin = $sqlLogin->fetch();

if($noNota==''){
  $noNota=generateNoNotaPenyusutan($db,$dataLogin['idCabang']);
}

$sqlUpdate=$db->prepare('
  SELECT * FROM balistars_hutang_gedung 
  where idHutangGedung=?');
$sqlUpdate->execute([$idHutangGedung]);
$dataUpdate=$sqlUpdate->fetch();
if($dataUpdate){
  $nilaiSewa=ubahToRp($dataUpdate['nilaiSewa']??'');
  $tanggalSewa=konversiTanggal($dataUpdate['tanggalSewa']??'');
  $tanggalPenyusutan=konversiTanggal($dataUpdate['tanggalPenyusutan']??'');
}

?>
<form id="formSewaGedung">
  <input type="hidden" name="idHutangGedung" value="<?=$idHutangGedung?>">
  <input type="hidden" name="noNota" value="<?=$noNota?>">
  <input type="hidden" name="flag" value="<?=$flag?>">
  <div class="form-row">
    <div class="form-group col-sm-6">
      <label>Nama Gedung <?=$noNota?></label>
        <select class="form-control select2" name="idGedung" id="idGedung">
          <option value="">Pilih Gedung</option>
          <?php
           $sqlKode=$db->prepare('
            SELECT * FROM balistars_gedung 
            where statusGedung=?');
            $sqlKode->execute(['Aktif']);
            $dataSewa=$sqlKode->fetchAll();
            foreach ($dataSewa as $row) {
              $selected=selected($row['idGedung'],$dataUpdate['idGedung']);
              ?>
              <option value="<?=$row['idGedung']?>"<?=$selected?>><?=$row['namaGedung']?></option>
              <?php
            }          
          ?>
      </select>
    </div>
    <div class="form-group col-sm-6">
      <label>Tanggal Sewa</label>
      <div class="input-group date"  data-date-autoclose="true" data-provide="datepicker"  data-date-format="dd-mm-yyyy">
        <input type="tanggal" class="form-control" name="tanggalSewa" id="tanggalSewa" value="<?=$tanggalSewa?>"  autocomplete="off">
        <div class="input-group-append">                                            
          <button class="btn btn-outline-secondary" type="button">
            <i class="fa fa-calendar"></i>
          </button>
        </div>
      </div>
    </div>
    <div class="form-group col-sm-6">
      <label>Tanggal Penyusutan</label>
      <div class="input-group date"  data-date-autoclose="true" data-provide="datepicker"  data-date-format="dd-mm-yyyy">
        <input type="tanggal" class="form-control" name="tanggalPenyusutan" id="tanggalPenyusutan" value="<?=$tanggalPenyusutan?>"  autocomplete="off" >
        <div class="input-group-append">                                            
          <button class="btn btn-outline-secondary" type="button">
            <i class="fa fa-calendar"></i>
          </button>
        </div>
      </div>
    </div>
    <div class="form-group col-sm-6">
      <label>Nominal</label>
      <input type="text" class="form-control" placeholder="Input Nominal" name="nilaiSewa" id="nilaiSewa" onkeyup="ubahToRp('#nilaiSewa')" value="<?=$nilaiSewa?>">
    </div>
    <div class="form-group col-sm-6">
      <label>No Nota</label>
      <input type="text" name="notaSewa" id="notaSewa" class="form-control" placeholder="Input No Nota Sewa" value="<?=$dataUpdate['notaSewa']?>" required>
    </div>
    <div class="form-group col-sm-6">
      <label>Penyusutan (tahun)</label>
      <input type="number" name="penyusutan" id="penyusutan" class="form-control" placeholder="Input Penyusutan" value="<?=$dataUpdate['penyusutan']?>" required>
    </div>
    <div class="form-group">
      <button type="button" class="btn btn-primary" onclick="prosesSewaGedung()">
        <i class="fa fa-save"></i> Save
      </button>
    </div>
  </div>
</form>

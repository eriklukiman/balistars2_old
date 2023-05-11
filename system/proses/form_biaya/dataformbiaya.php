<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
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
  'form_biaya'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

$flag       = '';
$flagDetail       = '';
$readonly      = '';
$noNota     ='';
$idBiayaDetail='';
$bulan = date('mY');
$tanggalBiaya = date('d-m-Y');
extract($_REQUEST);

$sqlLogin  = $db->prepare('SELECT * FROM balistars_pegawai inner join balistars_user on balistars_pegawai.idPegawai=balistars_user.idPegawai inner join balistars_cabang on balistars_pegawai.idCabang=balistars_cabang.idCabang where balistars_user.idUser = ?');
$sqlLogin->execute([$idUserAsli]);
$dataLogin = $sqlLogin->fetch();
$idCabang=$dataLogin['idCabang'];

if($noNota==''){
  if($tipe=='A1'){
    $sql=$db->prepare('SELECT tanggal from balistars_nomor where 
      jenis =? 
      and status=?');
    $sql->execute([
      'BiayaA1',
      'Aktif']);
    $data=$sql->fetch();
    $tanggalNo=$data['tanggal'];
    $bulanNo = explode('-', $tanggalNo);
    if($bulan!=$bulanNo[1].$bulanNo[0]){
      updateNoNotaBaruA1($db);
    }
    $noNota = generateNoNotaA1($db,$idCabang);
  }
  else{
    $sql=$db->prepare('SELECT tanggal from balistars_nomor where 
      jenis =? 
      and status=?');
    $sql->execute([
      'BiayaA2',
      'Aktif']);
    $data=$sql->fetch();
    $tanggalNo=$data['tanggal'];
    $bulanNo = explode('-', $tanggalNo);
    if($bulan!=$bulanNo[1].$bulanNo[0]){
      updateNoNotaBaruA2($db);
    }
    $noNota = generateNoNotaA2($db,$idCabang);
  }
}

// if($noNota=='' && $tipe=='A1'){
//   $noNota = generateNoNotaA1($db,$idCabang);
// }
// elseif($noNota=='' && $tipe=='A2'){
//   $noNota = generateNoNotaA2($db,$idCabang);
// }

$sqlUpdate = $db->prepare('SELECT * FROM balistars_biaya WHERE noNota=?');
$sqlUpdate->execute([$noNota]);
$dataUpdate=$sqlUpdate->fetch();
if($dataUpdate){
  $tanggalBiaya = konversiTanggal($dataUpdate['tanggalBiaya']??'');
  $tipe = $dataUpdate['tipeBiaya']??'';
}

?>
<form id="formBiaya">
  <input type="hidden" name="idBiaya" id="idBiaya" value="<?=$dataUpdate['idBiaya']??''?>">
  <input type="hidden" name="idCabang" id="idCabang" value="<?=$idCabang?>">
  <input type="hidden" name="flag" id="flag" value="<?=$flag?>">
  <input type="hidden" name="tipeBiaya" id="tipeBiaya" value="<?=$tipe?>">

  <div class="form-row">
    <div class="form-group col-sm-3">
      <label>No Nota</label>
      <input type="text" class="form-control" id="noNota" name="noNota" value="<?=$noNota?>" readonly>
    </div>
    <div class="form-group col-sm-3">
       <label>Cabang</label>
      <input type="text" class="form-control" id="cabang" value="<?=$dataLogin['namaCabang']??''?>" readonly>       
    </div>
    <div class="form-group col-sm-3">
      <?php 
      $disabled='disabled';
      if($idCabang==9){
        $disabled='';
      } 
      ?>
      <label>Tanggal Biaya</label>
      <div class="input-group date"  data-date-autoclose="true" data-provide="datepicker"  data-date-format="dd-mm-yyyy">
        <input type="tanggal" class="form-control" name="tanggalBiaya" id="tanggalBiaya" value="<?=$tanggalBiaya?>"  autocomplete="off" <?=$disabled?>>
        <div class="input-group-append">                            
          <button class="btn btn-outline-secondary" type="button">
            <i class="fa fa-calendar"></i>
          </button>
        </div>
      </div>
    </div>
    <div class="form-group col-sm-3">
      <label>Nama Pegawai</label>
      <select name="idPegawai" id="idPegawai" class="form-control select2" style="width: 100%;">
        <option value="">Pilih Pegawai</option>
        <?php
        $sqlPegawai=$db->prepare('SELECT * FROM balistars_pegawai where statusPegawai=? order by namaPegawai');
        $sqlPegawai->execute(['Aktif']);
        $dataPegawai=$sqlPegawai->fetchAll();
        foreach($dataPegawai as $data){
          $selected = selected($dataUpdate['idPegawai']??'',$data['idPegawai']);
          ?>
          <option value="<?=$data['idPegawai']?>"<?=$selected?>><?=$data['namaPegawai']?></option>
          <?php
        }
        ?> 
      </select>                         
    </div>
  </div>
  <div class="form-row">
    <div class="form-group col-sm-3">
      <label>No Nota Biaya</label>
      <input type="text" class="form-control" id="noNotaBiaya" placeholder="input nomor nota" name="noNotaBiaya" value="<?=$dataUpdate['noNotaBiaya']??''?>" >
    </div>
    <div class="form-group col-sm-3">
      <label>Kode Akunting</label>
      <select name="kodeAkunting" id="kodeAkunting" class="form-control select2" style="width: 100%;">
        <option value="">Pilih Kode Akunting</option>
        <?php
        $sqlKodeAkunting=$db->prepare('SELECT * FROM balistars_kode_akunting where statusKodeAkunting=? order by kodeAkunting');
        $sqlKodeAkunting->execute(['Aktif']);
        $dataKodeAkunting=$sqlKodeAkunting->fetchAll();
        foreach($dataKodeAkunting as $data){
          $selected = selected($dataUpdate['kodeAkunting']??'',$data['kodeAkunting']);
          ?>
          <option value="<?=$data['kodeAkunting']?>"<?=$selected?>>(<?=$data['kodeAkunting']?>) <?=$data['keterangan']?></option>
          <?php
        }
        ?> 
      </select>       
    </div>
    <?php 
    if($tipe=='A1'){
      ?>
      <div class="form-group col-sm-3">
        <label>Jenis PPN</label>
        <select name="jenisPPN" class="form-control select2" id="jenisPPN" onchange="getBiayaTersimpan('<?=$tipe?>'); showPersen();" style="width: 100%;" required>
          <?php
          $arrayTipe=array('Include','Exclude','Non PPN');
          for($i=0; $i<count($arrayTipe); $i++){
            $selected=selected($arrayTipe[$i],$dataUpdate['jenisPPN']??'');
            ?>
            <option value="<?=$arrayTipe[$i]?>" <?=$selected?>> <?=$arrayTipe[$i]?> </option>
            <?php
          }
          ?>
        </select>
      </div>
      <div class="form-group col-sm-3" id="boxPersenPPN">
      <label>Persen PPN</label>
      <select name="persenPPN" class="form-control select2" onchange="getBiayaTersimpan('<?=$tipe?>');" id="persenPPN" style="width: 100%;" required>
          <?php
          $arrayPersen=array('11','10');
          for($i=0; $i<count($arrayPersen); $i++){
            $selected=selected($arrayPersen[$i],$dataUpdate['persenPPN']??'');
            ?>
            <option value="<?=$arrayPersen[$i]?>" <?=$selected?>> <?=$arrayPersen[$i]?> % </option>
            <?php
          }
          ?>
        </select>      
      </div>
      <?php
    } else{
      ?>
      <input type="hidden" name="jenisPPN" id="jenisPPN" value="NonPPN">
      <input type="hidden" name="persenPPN" id="persenPPN" value="0">
      <?php
    } ?>
  </div>
</form>


<div style="overflow-x: auto;">
  <table class="table table-custom table-hover">
    <thead class="alert alert-info">
      <th style="width: 5%;">No</th>
      <th style="width: 25%;">Keterangan</th>
      <th style="width: 10%;">Qty</th>
      <th style="width: 20%;">Harga Satuan</th>
      <th style="width: 20%;">Nilai</th>
      <th style="width: 10%;">Aksi</th>
    </thead>
    <tbody id="dataFormItemBiaya"> </tbody>
    <tbody id="dataDaftarBiayaTersimpan"></tbody>
  </table>
</div>

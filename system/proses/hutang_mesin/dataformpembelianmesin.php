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
  'hutang_mesin'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

$flag       = '';
$readonly      = '';
$noNota     ='';
$tanggalPembelian = date('d-m-Y');
extract($_REQUEST);

$sqlLogin  = $db->prepare('SELECT * FROM balistars_pegawai inner join balistars_user on balistars_pegawai.idPegawai=balistars_user.idPegawai inner join balistars_cabang on balistars_pegawai.idCabang=balistars_cabang.idCabang where balistars_user.idUser = ?');
$sqlLogin->execute([$idUserAsli]);
$dataLogin = $sqlLogin->fetch();
$idCabang=$dataLogin['idCabang'];

if($noNota=='' && $tipePembelian=='A1'){
  $noNota = generateNoNotaA1($db,$idCabang);
}
elseif($noNota=='' && $tipePembelian=='A2'){
  $noNota = generateNoNotaA2($db,$idCabang);
}

$sqlUpdate = $db->prepare('SELECT * FROM balistars_pembelian_mesin WHERE noNota=?');
$sqlUpdate->execute([$noNota]);
$dataUpdate=$sqlUpdate->fetch();
if($dataUpdate){
  $tanggalPembelian = konversiTanggal($dataUpdate['tanggalPembelian']??'');
  $tipePembelian = $dataUpdate['tipePembelian']??'';
}

?>
<form id="formPembelianMesin">
  <input type="hidden" name="idPembelianMesin" id="idPembelianMesin" value="<?=$dataUpdate['idPembelianMesin']??''?>">
  <input type="hidden" name="flag" id="flag" value="<?=$flag?>">
  <input type="hidden" name="tipePembelian" id="tipePembelian" value="<?=$tipePembelian?>">

  <div class="form-row">
    <div class="form-group col-sm-3">
      <label>No Nota</label>
      <input type="text" class="form-control" id="noNota" name="noNota" value="<?=$noNota?>" readonly>
    </div>
    <div class="form-group col-sm-3">
       <label>Cabang</label>
      <select name="idCabang" id="idCabang" class="form-control select2" style="width: 100%;">
        <option value="">Pilih Cabang</option>
        <?php
        $sqlCabang=$db->prepare('SELECT * FROM balistars_cabang where statusCabang=? order by namaCabang');
        $sqlCabang->execute(['Aktif']);
        $dataCabang=$sqlCabang->fetchAll();
        foreach($dataCabang as $data){
          $selected = selected($dataUpdate['idCabang']??'',$data['idCabang']);
          ?>
          <option value="<?=$data['idCabang']?>"<?=$selected?>><?=$data['namaCabang']?></option>
          <?php
        }
        ?> 
      </select>        
    </div>
    <div class="form-group col-sm-3">
      <label>Tanggal Pembelian</label>
      <div class="input-group date"  data-date-autoclose="true" data-provide="datepicker"  data-date-format="dd-mm-yyyy">
        <input type="tanggal" class="form-control" name="tanggalPembelian" id="tanggalPembelian" value="<?=$tanggalPembelian?>"  autocomplete="off" >
        <div class="input-group-append">                                            
          <button class="btn btn-outline-secondary" type="button">
            <i class="fa fa-calendar"></i>
          </button>
        </div>
      </div>
    </div>
    <div class="form-group col-sm-3">
      <label>Nama Supplier</label>
      <input type="text" class="form-control" id="namaSupplier" placeholder="input nama Supplier" name="namaSupplier" value="<?=$dataUpdate['namaSupplier']??''?>" >
    </div>
  </div>
  <div class="form-row">
    <div class="form-group col-sm-3">
      <label>No Nota Pembelian</label>
      <input type="text" class="form-control" id="noNotaVendor" placeholder="input nomor nota" name="noNotaVendor" value="<?=$dataUpdate['noNotaVendor']??''?>" >
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
          <option value="<?=$data['kodeAkunting']?>"<?=$selected?>><?=$data['keterangan']?></option>
          <?php
        }
        ?> 
      </select>       
    </div>
    <div class="form-group col-sm-3">
      <label>Lama Penyusutan (Th)</label>
      <input type="number" name="lamaPenyusutan" class="form-control" placeholder="Lama penyusutan" id="lamaPenyusutan" value="<?=$dataUpdate['lamaPenyusutan']??''?>">     
    </div>
    <?php 
    if($tipePembelian=='A1'){
      ?>
      <div class="form-group col-sm-3">
        <label>Jenis PPN</label>
        <select name="jenisPPN" class="form-control select2" id="jenisPPN" onchange="getPembelianMesinTersimpan(); showPersen();" style="width: 100%;" required>
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
      <select name="persenPPN" class="form-control select2" onchange="getPembelianMesinTersimpan();" id="persenPPN" style="width: 100%;" required>
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
      <th style="width: 25%;">Nama Barang</th>
      <th style="width: 10%;">Qty</th>
      <th style="width: 20%;">Harga Satuan (Rp)</th>
      <th style="width: 15%;">Diskon (Rp)</th>
      <th style="width: 20%;">Nilai (Rp)</th>
      <th style="width: 10%;">Aksi</th>
    </thead>
    <tbody id="dataFormItemPembelianMesin"> </tbody>
    <tbody id="dataDaftarPembelianMesinTersimpan">
    </tbody>
  </table>
</div>

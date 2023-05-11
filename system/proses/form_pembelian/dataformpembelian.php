<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
include_once $BASE_URL_PHP . '/system/fungsinavigasi.php';
include_once $BASE_URL_PHP . '/library/fungsitanggal.php';
include_once $BASE_URL_PHP . '/library/fungsirupiah.php';
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
  'form_pembelian'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

$flag       = '';
$flagDetail ='';
$readonly      = '';
$noNota     ='';
$idPembelianDetail='';
$tanggalPembelian = date('d-m-Y');
extract($_REQUEST);

if($konsumen=='Cash' && $tipe=='A2'){
  $judul='No.Telp';
} elseif($konsumen=='Cash' && $tipe=='A1'){
  $judul='No.Telp/PPN';
}elseif($konsumen=='Giro' && $tipe=='A2'){
  $judul='';
}elseif($konsumen=='Giro' && $tipe=='A1'){
  $judul='PPN';
}

$sqlLogin  = $db->prepare('SELECT * FROM balistars_pegawai inner join balistars_user on balistars_pegawai.idPegawai=balistars_user.idPegawai inner join balistars_cabang on balistars_pegawai.idCabang=balistars_cabang.idCabang where balistars_user.idUser = ?');
$sqlLogin->execute([$idUserAsli]);
$dataLogin = $sqlLogin->fetch();
$idCabang=$dataLogin['idCabang'];

if($noNota==''){
  if($tipe=='A1'){
    $noNota = generateNoBeliA1($db,$idCabang);
  } else{
    $noNota = generateNoBeliA2($db,$idCabang);
  }
  
}

$sqlUpdate = $db->prepare('SELECT * FROM balistars_pembelian WHERE noNota=?');
$sqlUpdate->execute([$noNota]);
$dataUpdate=$sqlUpdate->fetch();
if($dataUpdate){
  $tanggalPembelian = konversiTanggal($dataUpdate['tanggalPembelian']??'');
}

?>
<form id="formPembelian">
  <input type="hidden" name="idPembelian" id="idPembelian" value="<?=$dataUpdate['idPembelian']??''?>">
  <input type="hidden" name="idCabang" id="idCabang" value="<?=$idCabang?>">
  <input type="hidden" name="flag" id="flag" value="<?=$flag?>">
  <input type="hidden" name="tipe" id="tipe" value="<?=$tipe?>">
  <input type="hidden" name="konsumen" id="konsumen" value="<?=$konsumen?>">

  <div class="row">
    <div class="form-group col-sm-6">
      <label>No Nota/Suplier</label>
      <div class="form-inline">
        <input type="text" class="form-control" id="noNota" name="noNota" value="<?=$noNota?>" style="width: 45%;" readonly>
        <span style="margin-right: 3px;"></span>
        <?php 
        if($konsumen=='Giro'){
         ?>
         <input type="hidden" name="namaSupplier" id="namaSupplier" value="">
         <input type="hidden" name="noTelpSupplier" id="noTelpSupplier" value="">
         <select name="idSupplier" id="idSupplier" class="form-control select2" style="width: 45%;">
            <?php
            $sqlSupplier=$db->prepare('SELECT * FROM balistars_supplier where statusSupplier=? order by namaSupplier');
            $sqlSupplier->execute(['Aktif']);
            $dataSupplier=$sqlSupplier->fetchAll();
            foreach($dataSupplier as $data){
              $selected = selected($dataUpdate['idSupplier']??'',$data['idSupplier']);
              ?>
              <option value="<?=$data['idSupplier']?>"<?=$selected?>><?=$data['namaSupplier']?>/<?=$data['noTelpSupplier']?></option>
              <?php
            }
            ?> 
          </select> 
         <?php 
        } else{
          ?> 
          <input type="hidden" name="idSupplier" id="idSupplier" value="0">
          <input type="text" class="form-control" name="namaSupplier" id="namaSupplier" value="<?=$dataUpdate['namaSupplier']??''?>" placeholder="Input Nama Supplier" style="width: 45%;">
          <?php 
        }
        ?>

      </div>
      <label><?=$judul?></label>
      <div class="form-inline">
        <?php 
        if($konsumen=='Cash'){
         ?>
        <input type="text" class="form-control" id="noTelpSupplier" name="noTelpSupplier" value="<?=$dataUpdate['noTelpSupplier']?>" style="width: 45%;" placeholder="input Nomor Telp">
        <span style="margin-right: 3px;"></span>
           <?php 
         }
        if($tipe=='A1'){
        ?>
          <select name="jenisPPN" class="form-control select2" id="jenisPPN" onchange="getPembelianTersimpan('<?=$konsumen?>'); showPersenPPN()" style="width: 25%;" required>
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
          <div class="form-group col-sm-3" id="boxPersenPPN">
          <select name="persenPPN" class="form-control select2" onchange="getPembelianTersimpan('<?=$konsumen?>');" id="persenPPN" style="width: 95%;" required>
              <?php
              $arrayPersen=array('11','10');
              for($i=0; $i<count($arrayPersen); $i++){
                $selected=selected($arrayPersen[$i],$dataUpdate['persenPPN']??'');
                ?>
                <option value="<?=$arrayPersen[$i]?>" <?=$selected?>> <?=$arrayPersen[$i]?>% </option>
                <?php
              }
              ?>
            </select>      
          </div>
         <?php 
       } else{
          ?>
          <input type="hidden" name="jenisPPN" id="jenisPPN" value="nonPPN">
          <input type="hidden" name="persenPPN" id="persenPPN" value="0">
          <?php 
        }
           ?>
      </div>
    </div>
    <div class="form-group col-sm-6">
      <label>Tanggal Pembelian/Cabang</label>
      <div class="form-inline">
        <div class="input-group date"  data-date-autoclose="true" data-provide="datepicker" style="width: 45%"  data-date-format="dd-mm-yyyy">
          <input type="tanggal" class="form-control" name="tanggalPembelian" id="tanggalPembelian" value="<?=$tanggalPembelian?>"  autocomplete="off" disabled>
          <div class="input-group-append">                                            
            <button class="btn btn-outline-secondary" type="button">
              <i class="fa fa-calendar"></i>
            </button>
          </div>
        </div>
        <span style="margin-right: 3px;"></span>
       <input type="text" class="form-control" id="cabang" value="<?=$dataLogin['namaCabang']??''?>" style="width: 50%;" readonly>
      </div>
      <label>Jatuh Tempo(hari)/Nomor Nota Pembelian</label>
      <div class="form-inline">
        <input type="number" name="jatuhTempo" id="jatuhTempo" class="form-control" value="<?=$dataUpdate['jatuhTempo']??''?>" style="width: 45%;" placeholder="Jatuh Tempo" required >
        <span style="margin-right: 3px;"></span>
        <input type="text" name="noNotaVendor" id="noNotaVendor" style="width: 50%;" class="form-control" value="<?=$dataUpdate['noNotaVendor']??''?>"  placeholder="Input No Nota Supplier" required >
      </div>   
    </div>
  </div>
</form>


<div style="overflow-x: auto;">
  <table class="table table-custom table-hover">
    <thead class="alert alert-info">
      <th style="width: 5%;">No</th>
      <th style="width: 15%;">Jenis Order</th>
      <th style="width: 20%;">Nama Bahan</th>
      <th style="width: 8%;">Qty <br> (Pcs)</th>
      <th style="width: 15%;">Harga Satuan <br> (Rp)</th>
      <th style="width: 15%;">Diskon <br> (Rp)</th>
      <th style="width: 15%;">Nilai <br> (Rp)</th>
      <th style="width: 10%;">Aksi</th>
    </thead>
    <tbody id="dataFormItemPembelian"></tbody>
    <tbody id="dataDaftarPembelianTersimpan"></tbody>
  </table>
</div>

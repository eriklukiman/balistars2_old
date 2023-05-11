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
  'laporan_penjualan'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

$sqlLogin  = $db->prepare('SELECT * FROM balistars_pegawai inner join balistars_user on balistars_pegawai.idPegawai=balistars_user.idPegawai inner join balistars_cabang on balistars_pegawai.idCabang=balistars_cabang.idCabang where balistars_user.idUser = ?');
$sqlLogin->execute([$idUserAsli]);
$dataLogin = $sqlLogin->fetch();
$idCabang = $dataLogin['idCabang'];
  
$idKasir       = '';
$readonly      = '';
$noNota     ='';
$flag ='';
$tipe='';
$konsumen='';
$tanggalPenjualan = date('d-m-Y');
extract($_REQUEST);


if($noNota==''){
  if($tipe=='A1'){
    $noNota = generateNoNotaA1($db,$idCabang);
  }
  else{
    $noNota = generateNoNotaA2($db,$idCabang);
  }
}

$sqlUpdate = $db->prepare('SELECT * FROM balistars_penjualan
inner join balistars_cabang 
on balistars_penjualan.idCabang=balistars_cabang.idCabang 
WHERE noNota=?');
$sqlUpdate->execute([$noNota]);
$dataUpdate=$sqlUpdate->fetch();
if($dataUpdate){
  $tanggalPenjualan = konversiTanggal($dataUpdate['tanggalPenjualan']??'');
  $idCabang=$dataUpdate['idCabang'];
}

?>

<form id="formKopKasir">
  <input type="hidden" name="idPenjualan" id="idPenjualan" value="<?=$dataUpdate['idPenjualan']??''?>">
  <input type="hidden" name="idCabang" id="idCabang" value="<?=$idCabang?>">
  <input type="hidden" name="tipePenjualan" id="tipePenjualan" value="<?=$tipe?>">
  <input type="hidden" name="konsumen" id="konsumen" value="<?=$konsumen?>">
  <input type="hidden" name="flag" id="flag" value="<?=$flag?>">
  <div class="form-row">
    <div class="col-sm-7">
        <label>Nomor Nota / Nama Customer / No Telp <?=$tipe?> <?=$konsumen?></label>
        <div class="form-inline">
          <input type="text" class="form-control" id="noNota" name="noNota" value="<?=$noNota?>" readonly style=" width: 30%;">
          <span style="margin-right: 3px;"></span>
          <?php
          if($konsumen=='umum'){
           ?>
            <input type="hidden" name="idCustomer" id="idCustomer" value="0">
            <input type="text" class="form-control" id="namaCustomer" placeholder="nama Customer" name="namaCustomer" value="<?=$dataUpdate['namaCustomer']??''?>" required style="width: 30%;">
            <span style="margin-right: 3px;"></span>
            <input type="text" name="noTelpCustomer" id="noTelpCustomer" class="form-control" style="width: 30%;" value="<?=$dataUpdate['noTelpCustomer']??''?>" placeholder="Input No Telp" required> 
           <?php 
         }
         else{
            ?>
            <select name="idCustomer" id="idCustomer" class="form-control select2" style="width: 40%;" required>
            <?php
              $sqlCustomer=$db->prepare('SELECT * FROM balistars_customer where statusCustomer=? order by namaCustomer');
              $sqlCustomer->execute(['Aktif']);
              $dataCustomer = $sqlCustomer->fetchAll();
              foreach($dataCustomer as $data){
                $selected=selected($data['idCustomer'],$dataUpdate['idCustomer']??'');
                ?>
                <option value="<?=$data['idCustomer']?>" <?=$selected?> ><?=$data['namaCustomer']?> / <?=$data['noTelpCustomer']?></option>
                <?php
              }
              ?>
            </select> 
            <?php 
          }
             ?>
            
        </div>
        <label>Jenis Pembayaran / Bank Tujuan/status Nota</label>
        <div class="form-inline" style="padding-bottom: 3px;">
          <select name="jenisPembayaran" class="form-control select2" id="jenisPembayaran" style="width: 30%">
            <?php
            $arrayPembayaran=array('Cash','Transfer');
            for($i=0; $i<count($arrayPembayaran); $i++){
              $selected=selected($arrayPembayaran[$i],$dataUpdate['jenisPembayaran']??'');
              ?>
              <option value="<?=$arrayPembayaran[$i]?>" <?=$selected?>> <?=$arrayPembayaran[$i]?> </option>
              <?php
            }
            ?>
          </select>
          <span style="margin-right: 3px;"></span>
          <select name="bankTujuanTransfer" id="bankTujuanTransfer" class="form-control select2" style="width: 30%">
            <?php
            $selected=selected(0,$dataUpdate['bankTujuanTransfer']??'');
            ?>
            <option value="0" <?=$selected?>> Cash </option>
            <?php
            $sqlBank=$db->prepare('SELECT * FROM balistars_bank where tipe=? and statusBank=? order by namaBank');
            $sqlBank->execute(['A1','Aktif']);
            $dataBank=$sqlBank->fetchAll();
            foreach($dataBank as $row){
              $selected=selected($row['idBank'],$dataUpdate['bankTujuanTransfer']??'');
              ?>
              <option value="<?=$row['idBank']?>" <?=$selected?>> <?=$row['namaBank']?> </option>
              <?php
            }
            ?>
          </select>
          <span style="margin-right: 3px;"></span>
          <select name="statusInput" id="statusInput" class="form-control select2" style="width: 30%">
            <?php
            $statusInput=array('new','old');
            for($i=0; $i<count($statusInput); $i++){
              $selected=selected($statusInput[$i],$dataUpdate['statusInput']??'');
              ?>
              <option value="<?=$statusInput[$i]?>" <?=$selected?>> <?=$statusInput[$i]?> </option>
              <?php
            }
            ?>
          </select>
        </div>
        <?php
        if($tipe=='A1'){
         ?>
         <label >PPN/Faktur</label>
        <div class="form-inline" >
          <select name="jenisPPN" id="jenisPPN" class="form-control select2" style="width: 30%" onchange="getBarangTersimpan(); showPersenPPN();">
            <?php
            $arrayPPN=array('Include','Exclude','Non PPN');
            for($i=0; $i<count($arrayPPN); $i++){
              $selected=selected($arrayPPN[$i],$dataUpdate['jenisPPN']??'');
              ?>
              <option value="<?=$arrayPPN[$i]?>" <?=$selected?>> <?=$arrayPPN[$i]?> </option>
              <?php
            }
            ?>
          </select>
          <span style="margin-right: 3px;"></span>
          <select name="statusFakturPajak" id="statusFakturPajak" class="form-control select2" style="width: 30%">
            <?php
            $arrayFaktur=array('Tanpa Faktur','Dengan Faktur');
            for($i=0; $i<count($arrayFaktur); $i++){
              $selected=selected($arrayFaktur[$i],$dataUpdate['statusFakturPajak']??'');
              ?>
              <option value="<?=$arrayFaktur[$i]?>" <?=$selected?>> <?=$arrayFaktur[$i]?> </option>
              <?php
            }
            ?>
          </select>
          <span style="margin-right: 3px;"></span>
          <div class="form-group col-sm-3" id="boxPersenPPN">
            <select name="persenPPN" class="form-control select2" onchange="getBarangTersimpan();" id="persenPPN" style="width: 100%;" required>
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
        </div>
         <?php 
         } else{ 
          ?>
          <input type="hidden" name="jenisPPN" id="jenisPPN" value="nonPPN">
          <input type="hidden" name="statusFakturPajak" id="statusFakturPajak" value="Tanpa Faktur">
          <input type="hidden" name="persenPPN" id="persenPPN" value="0">
          <?php 
          } ?>
        
      </div>
      <div class="col-sm-5">
        <label>Tanggal Penjualan / Cabang</label>
        <div class="form-inline">
          <div class="input-group date"  data-date-autoclose="true" data-provide="datepicker" style="width: 45%"  data-date-format="dd-mm-yyyy">
            <input type="text" class="form-control" name="tanggalPenjualan" id="tanggalPenjualan" value="<?=$tanggalPenjualan?>"  autocomplete="off" disabled>
            <div class="input-group-append">                                            
            <button class="btn btn-outline-secondary" type="button">
              <i class="fa fa-calendar"></i>
            </button>
            </div>
          </div>
          <span style="margin-right: 3px;"></span>
          <input type="text" class="form-control" value="<?=$dataUpdate['namaCabang']?>" style="width: 45%;" readonly>   
        </div>
        <label>Lama Selesai (Hari) / Designer</label>
        <div class="form-inline">
          <input type="number" name="lamaSelesai" id="lamaSelesai" class="form-control" value="<?=$dataUpdate['lamaSelesai']??''?>" placeholder="Input Lama Selesai" required style="width: 45%;"> 
          <span style="margin-right: 3px;"></span>
          <select name="idDesigner" id="idDesigner" class="form-control select2" style="width: 45%;">
              <?php
              $selectedOther='';
              $idJabatanDesigner=6;
              $idCabangDesigner=$dataLogin['idCabang'];

              $sqlDesigner=$db->prepare('SELECT * FROM balistars_pegawai where idJabatan=? and idCabang=? and statusPegawai=? order by namaPegawai');

              $sqlDesigner->execute([$idJabatanDesigner,$idCabangDesigner,'Aktif']);
              $dataDesigner=$sqlDesigner->fetchAll();

              foreach($dataDesigner as $row){
                $selected=selected($row['idPegawai'],$dataUpdate['idDesigner']);
                ?>
                <option value="<?=$row['idPegawai']?>" <?=$selected?>> <?=$row['namaPegawai']?> </option>
                <?php
              }
              if($dataUpdate['idDEsigner']=='0'){
                $selectedOther='selected';
              }
              ?>
              <option value="0" <?=$selectedOther?>> OTHER </option>
            </select>
        </div>
      </div>
    </div>  
  </div> 
</form>
<br>

<div style="overflow-x: auto;">
        <table class="table table-custom table-hover">
          <thead class="alert alert-info">
            <th style="width: 5%;">No</th>
            <th>Jenis Order</th>
            <th style="width: 15%;">Nama Bahan</th>
            <th>Ukuran <br> (cm x cm)</th>
            <th>Finishing</th>
            <th style="width: 8%;">Qty <br> (Pcs)</th>
            <th style="width: 10%;">Harga Satuan <br> (Rp)</th>
            <th style="width: 15%;">Nilai <br> (Rp)</th>
            <th style="width: 5%;">Aksi</th>
          </thead>
          <tbody id="dataFormItemKasir"></tbody>
          <tbody id="dataDaftarBarangTersimpan"></tbody>
        </table>
      </div>
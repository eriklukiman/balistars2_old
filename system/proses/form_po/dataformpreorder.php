<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
include_once $BASE_URL_PHP . '/library/fungsitanggal.php';
include_once $BASE_URL_PHP . '/system/fungsinavigasi.php';
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
  'form_po'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}
  
$idPo       = '';
$readonly      = '';
$noPo     ='';
$rentang ='';
$flag ='';
$tanggalPo = date('d-m-Y');
$tanggalSelesai = date('d-m-Y');
extract($_REQUEST);

$sqlLogin  = $db->prepare('SELECT * FROM balistars_pegawai inner join balistars_user on balistars_pegawai.idPegawai=balistars_user.idPegawai inner join balistars_cabang on balistars_pegawai.idCabang=balistars_cabang.idCabang where balistars_user.idUser = ?');
$sqlLogin->execute([$idUserAsli]);
$dataLogin = $sqlLogin->fetch();

if($noPo==''){
  $noPo = generateNoNota($db,$dataLogin['idCabang']);
}

$sqlUpdate = $db->prepare('SELECT * FROM balistars_po WHERE noPo=?');
$sqlUpdate->execute([$noPo]);
$dataUpdate=$sqlUpdate->fetch();
if($dataUpdate){
  $tanggalPo = konversiTanggal($dataUpdate['tanggalPo']??'');
  $tanggalSelesai = konversiTanggal($dataUpdate['tanggalSelesai']??'');
  $idCabangAdvertising = $dataUpdate['idCabangAdvertising']??'';
}

?>
  <form id="formPo">
    <input type="hidden" name="idPo" id="idPo" value="<?=$dataUpdate['idPo']??''?>">
    <input type="hidden" name="idCabang" id="idCabang" value="<?=$dataLogin['idCabang']?>">
    <input type="hidden" name="konsumen" id="konsumen" value="<?=$konsumen?>">
    <input type="hidden" name="rentang" id="rentang" value="<?=$rentang?>">
    <input type="hidden" name="flag" id="flag" value="<?=$flag?>">
    
    <div class="row">
      <div class="col-sm-7">
        <label>Nomor Nota / Nama Customer / No Telp (<?=$konsumen?> <?=$flag?>)</label>
        <div class="form-inline">
          <input type="text" class="form-control" id="noPo" name="noPo" value="<?=$noPo?>" readonly style="margin-right: 3px;">
          <span style="margin-right: 3px;"></span>
          <?php
          if($konsumen=='pelanggan'){
            ?>
            <select name="customer" id="customer" class="form-control select2" style="width: 50%;">
              <?php
              $sqlPelanggan=$db->prepare('SELECT * FROM balistars_customer where statusCustomer=? order by namaCustomer');
              $sqlPelanggan->execute(['Aktif']);
              $dataPelanggan=$sqlPelanggan->fetchAll();
              foreach($dataPelanggan as $row){
                $selected=selected($row['idCustomer'],$dataUpdate['idCustomer']);
                ?>
                <option value="<?=$row['idCustomer']?>/<?=$row['namaCustomer']?>/<?=$row['noTelpCustomer']?>" <?=$selected?>> <?=$row['namaCustomer']?> / <?=$row['noTelpCustomer']?> </option>
                <?php
              }
              ?>
            </select>
            <span style="margin-right: 3px;"></span>
            <?php
          }
          else{
            ?>
          <input type="hidden" name="idCustomer" id="idCustomer" value="0">
          <input type="text" class="form-control" id="namaCustomer" placeholder="nama Customer" name="namaCustomer" value="<?=$dataUpdate['namaCustomer']??''?>" required style="width: 25%;">
          <span style="margin-right: 3px;"></span>
          <input type="text" name="noTelpCustomer" id="noTelpCustomer" class="form-control" value="<?=$dataUpdate['noTelpCustomer']??''?>" placeholder="Input No Telp" required> 
          <?php 
          }
           ?> 
        </div>
        <label>Keterangan</label>
        <div class="form-inline" style="padding-bottom: 3px;">
          <textarea class="form-control" name="keterangan" placeholder="keterangan" id="keterangan" style="width: 65%;"><?=$dataUpdate['keterangan']??''?></textarea>
        </div>
        
      </div>
      <div class="col-sm-5">
        <label>Tanggal PO / Cabang</label>
        <div class="form-inline">
          <div class="input-group date"  data-date-autoclose="true" data-provide="datepicker" style="width: 50%;"  data-date-format="yyyy-mm-dd">
            <input type="tanggal" class="form-control" name="tanggalPo" id="tanggalPo" value="<?=$tanggalPo?>"  autocomplete="off" disabled>
            <div class="input-group-append">                                            
            <button class="btn btn-outline-secondary" type="button">
              <i class="fa fa-calendar"></i>
            </button>
            </div>
          </div>
          
          <span style="margin-right: 3px;"></span>
          <input type="text" class="form-control" value="<?=$dataLogin['namaCabang']?>" style="width: 40%;" readonly>   
        </div>
        <label>Tanggal Selesai / Cabang Advertising</label>
        <div class="form-inline">
          <div class="input-group date"  data-date-autoclose="true" data-provide="datepicker" style="width: 50%;"  data-date-format="yyyy-mm-dd">
            <input type="tanggal" class="form-control" name="tanggalSelesai" id="tanggalSelesai" value="<?=$tanggalSelesai?>"  autocomplete="off">
            <div class="input-group-append">                                            
              <button class="btn btn-outline-secondary" type="button">
                <i class="fa fa-calendar"></i>
              </button>
            </div> 
          </div>
          <span style="margin-right: 3px;"></span>
          <select name="idCabangAdvertising" id="idCabangAdvertising" class="form-control select2" style="width: 40%;">
            <?php
            $sqlJenisPenjualan=$db->prepare('SELECT * FROM balistars_cabang_advertising where statusCabangAdvertising=?');
            $sqlJenisPenjualan->execute(['Aktif']);
            $dataUnitAdv=$sqlJenisPenjualan->fetchAll();
            foreach($dataUnitAdv as $row){
              $selected=selected($idCabangAdvertising,$row['idCabang']);
              ?>
              <option value="<?=$row['idCabang']?>" <?=$selected?>> <?=$row['namaCabang']?> </option>
              <?php
            }
          ?>
          </select>
        </div>
      </div>
    </div>
  </form>


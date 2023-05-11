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
  'laporan_penjualan'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}
  
$readonly      = '';
$idPenjualanDetail    ='';
$flag ='';
extract($_REQUEST);

$sqlLogin  = $db->prepare('SELECT * FROM balistars_pegawai inner join balistars_user on balistars_pegawai.idPegawai=balistars_user.idPegawai inner join balistars_cabang on balistars_pegawai.idCabang=balistars_cabang.idCabang where balistars_user.idUser = ?');
$sqlLogin->execute([$idUserAsli]);
$dataLogin = $sqlLogin->fetch();
$idCabang = $dataLogin['idCabang'];

$sqlUpdate = $db->prepare('SELECT * FROM balistars_penjualan_detail WHERE idPenjualanDetail=?');
$sqlUpdate->execute([$idPenjualanDetail]);
$dataUpdate=$sqlUpdate->fetch();
if($dataUpdate){
  $dataUpdate['hargaSatuan']=ubahToRp($dataUpdate['hargaSatuan']);
  $dataUpdate['qty'] = ubahToRp($dataUpdate['qty']);
  $dataUpdate['nilai'] = ubahToRp($dataUpdate['nilai']);
}

?>

  <form id="dataFormItemKasir">
    <input type="hidden" name="flagDetail" id="flagDetail" value="<?=$flagDetail?>">
    <input type="hidden" name="noNota" id="noNota" value="<?=$dataUpdate['noNota']?>">
    <input type="hidden" name="idPenjualanDetail" id="idPenjualanDetail" value="<?=$dataUpdate['idPenjualanDetail']?>">
    <input type="hidden" id="dataUpdateSupplierSub" value="<?=$dataUpdate['supplierSub']?>">
    <input type="hidden" id="dataUpdateIdCabangAdvertising" value="<?=$dataUpdate['idCabangAdvertising']?>">

    <input type="hidden" name="idPenjualanDetail" id="idPenjualanDetail" value="<?=$dataUpdate['idPenjualanDetail']?>">
    <td style="vertical-align: top;">#</td>
    <td>
      <div class="form-group">
        <select name="jenisOrder" id="jenisOrder" class="form-control select2" onchange="showJenisPenjualan()" >
          <!-- <option value="">Pilih Jenis Order</option> -->
          <?php
          $sqlJenisPenjualan=$db->prepare('SELECT * FROM balistars_jenis_penjualan where statusJenisPenjualan=?');
          $sqlJenisPenjualan->execute(['Aktif']);
          $dataJenisPenjualan=$sqlJenisPenjualan->fetchAll();
          foreach ($dataJenisPenjualan as $data){
            $selected=selected($data['jenisPenjualan'],$dataUpdate['jenisOrder']??'');
            ?>
            <option value="<?=$data['jenisPenjualan']?>" <?=$selected?>> <?=$data['jenisPenjualan']?> </option>
            <?php
          }
          ?>
        </select>
      </div>
      <div class="form-group">
        <select name="jenisPenjualan" id="jenisPenjualan" class="form-control select2" onchange="showJenisPenjualan()">
          <?php
          $jenisPenjualan=array('Reguler','Sub');
          for($i=0; $i<count($jenisPenjualan); $i++){
            $selected=selected($jenisPenjualan[$i],$dataUpdate['jenisPenjualan']??'');
            ?>
            <option value="<?=$jenisPenjualan[$i]?>" <?=$selected?>> <?=$jenisPenjualan[$i]?> </option>
            <?php
          }
          ?>
        </select>
      </div>
      <div class="form-group" id="idCabangAdvertisingShow" style="display: none;">
        <select name="idCabangAdvertising" id="idCabangAdvertising" class="form-control select2">
          <option value="0">pilih C.Adv</option>
            <?php
            $sqlJenisPenjualan=$db->prepare('SELECT * FROM balistars_cabang_advertising where statusCabangAdvertising=?');
          $sqlJenisPenjualan->execute(['Aktif']);
          $dataUnitAdv=$sqlJenisPenjualan->fetchAll();
            foreach($dataUnitAdv as $row){
              $selected=selected($dataUpdate['idCabangAdvertising'],$row['idCabang']);
              ?>
              <option value="<?=$row['idCabang']?>" <?=$selected?>> <?=$row['namaCabang']?> </option>
              <?php
            }
          ?>
          </select>
      </div>
      <div class="form-group">
        <input type="text" name="supplierSub" id="supplierSub" placeholder="Supplier Sub"  value="<?=$dataUpdate['supplierSub']?>" class="form-control" style="display: none;">
      </div>
    </td>
    <td style="vertical-align: top;">
      <input type="text" name="namaBahan" id="namaBahan" class="form-control" placeholder="Input Nama Bahan" value="<?=$dataUpdate['namaBahan']?>" >
    </td>
    <td style="vertical-align: top;">
      <input type="text" name="ukuran" id="ukuran" class="form-control" placeholder="Input Ukuran" value="<?=$dataUpdate['ukuran']?>" >
    </td>
    <td style="vertical-align: top;">
      <input type="text" name="finishing" id="finishing" class="form-control" placeholder="Input Finishing" value="<?=$dataUpdate['finishing']?>" >
    </td>
    <td style="vertical-align: top;">
      <input type="text" name="qty" id="qty" class="form-control" placeholder="qty" onkeyup="ubahToRp('#qty'); getNilai();" value="<?=$dataUpdate['qty']?>" >
    </td>
    <td style="vertical-align: top;">
      <input type="text" name="hargaSatuan" id="hargaSatuan" class="form-control" placeholder="harga" onkeyup="ubahToRp('#hargaSatuan'); getNilai();" value="<?=$dataUpdate['hargaSatuan']?>" >
    </td>
    <td style="vertical-align: top;">
      <input type="text" name="nilai" id="nilai" class="form-control" placeholder="0" value="<?=$dataUpdate['nilai']?>" readonly>
    </td>
    <td style="vertical-align: top;">
      <button type="button" class="btn btn-success" onclick="prosesPenjualanDetail()">
        <i class="fa fa-save" style="text-align: right;"></i>
      </button>
    </td>
  </form>

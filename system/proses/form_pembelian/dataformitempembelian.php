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


extract($_REQUEST);

$sqlLogin  = $db->prepare('SELECT * FROM balistars_pegawai inner join balistars_user on balistars_pegawai.idPegawai=balistars_user.idPegawai inner join balistars_cabang on balistars_pegawai.idCabang=balistars_cabang.idCabang where balistars_user.idUser = ?');
$sqlLogin->execute([$idUserAsli]);
$dataLogin = $sqlLogin->fetch();
$idCabang = $dataLogin['idCabang'];

$sqlUpdateDetail = $db->prepare('SELECT * FROM balistars_pembelian_detail WHERE idPembelianDetail=?');
$sqlUpdateDetail->execute([$idPembelianDetail]);
$dataUpdateDetail=$sqlUpdateDetail->fetch();
if($dataUpdateDetail){
  $dataUpdateDetail['hargaSatuan'] = ubahToRp($dataUpdateDetail['hargaSatuan']??'');
  $dataUpdateDetail['diskon'] = ubahToRp($dataUpdateDetail['diskon']??'');
  $dataUpdateDetail['nilai'] = ubahToRp($dataUpdateDetail['nilai']??'');
  $dataUpdateDetail['qty'] = ubahToRp($dataUpdateDetail['qty']??'');
}

?>

  <form id="dataFormItemPembelian">
    <input type="hidden" name="flagDetail" id="flagDetail" value="<?=$flagDetail?>">
    <input type="hidden" name="idPembelianDetail" id="idPembelianDetail" value="<?=$idPembelianDetail?>">
    <td style="vertical-align: top;">#
      <input type="hidden" name="flagDetail" id="flagDetail" value="<?=$flagDetail?>">
      <input type="hidden" name="idPembelianDetail" id="idPembelianDetail" value="<?=$idPembelianDetail?>">
    </td>
    <td style="vertical-align: top;">
      <select name="jenisOrder" id="jenisOrder" class="form-control select2">
      <?php
      $sqlJenisPenjualan=$db->prepare('SELECT * FROM balistars_jenis_penjualan where statusJenisPenjualan=?');
      $sqlJenisPenjualan->execute(['Aktif']);
      $dataJenisPenjualan=$sqlJenisPenjualan->fetchAll();
      foreach ($dataJenisPenjualan as $data){
        $selected=selected($data['jenisPenjualan'],$dataUpdateDetail['jenisOrder']??'');
        ?>
        <option value="<?=$data['jenisPenjualan']?>" <?=$selected?> > <?=$data['jenisPenjualan']?> </option>
        <?php
      }
      ?>
    </select>
    </td>
    <td style="vertical-align: top;">
      <input type="text" class="form-control" name="namaBarang" id="namaBarang" placeholder="Nama Barang" value="<?=$dataUpdateDetail['namaBarang']?>">
    </td>
    <td style="vertical-align: top;">
      <input type="text" name="qty" id="qty" placeholder="0" class="form-control" onkeyup="ubahToRp('#qty'); getNilai();" style="text-align: right;" value="<?=$dataUpdateDetail['qty']?>">
    </td>
    <td style="vertical-align: top;">
      <input type="text" class="form-control" style="text-align: right;" name="hargaSatuan" placeholder="Harga Satuan" onkeyup="ubahToRp('#hargaSatuan'); getNilai();" id="hargaSatuan" value="<?=$dataUpdateDetail['hargaSatuan']?>">
    </td>
    <td style="vertical-align: top;">
      <input type="text" class="form-control" style="text-align: right;" name="diskon" placeholder="0" onkeyup="ubahToRp('#diskon'); getNilai();" id="diskon" value="<?=$dataUpdateDetail['diskon']??'0'?>">
    </td>
    <td style="vertical-align: top;">
      <input type="text" name="nilai" id="nilai" placeholder="0" class="form-control" readonly style="text-align: right;" value="<?=$dataUpdateDetail['nilai']?>">
    </td>
    <td style="vertical-align: top;">
      <button type="button" class="btn btn-success" onclick="prosesPembelianDetail('<?=$konsumen?>','<?=$tipe?>')">
        <i class="fa fa-save"></i>
      </button>
    </td>
  </form>

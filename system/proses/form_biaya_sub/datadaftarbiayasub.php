<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
include_once $BASE_URL_PHP . '/library/fungsitanggal.php';
include_once $BASE_URL_PHP . '/library/fungsirupiah.php';

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
  'form_biaya_sub'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);

$sqlLogin  = $db->prepare('SELECT * FROM balistars_pegawai inner join balistars_user on balistars_pegawai.idPegawai=balistars_user.idPegawai inner join balistars_cabang on balistars_pegawai.idCabang=balistars_cabang.idCabang where balistars_user.idUser = ?');
$sqlLogin->execute([$idUserAsli]);
$dataLogin = $sqlLogin->fetch();

$tanggal      = explode(' - ', $rentang);
$tanggalAwal  = konversiTanggal($tanggal[0]);
$tanggalAkhir = konversiTanggal($tanggal[1]);

$sql=$db->prepare('
  SELECT * FROM balistars_penjualan_detail 
  inner join balistars_penjualan 
  on balistars_penjualan_detail.noNota=balistars_penjualan.noNota 
  where (balistars_penjualan.tanggalPenjualan between ? and ?) 
  and balistars_penjualan_detail.jenisPenjualan=? 
  and balistars_penjualan.idCabang=? 
  and balistars_penjualan_detail.statusCancel=? 
  order by tanggalPenjualan DESC, balistars_penjualan_detail.noNota DESC');
$sql->execute([
  $tanggalAwal,
  $tanggalAkhir,
  'Sub',
  $dataLogin['idCabang'],
  'ok']);
$hasil=$sql->fetchAll();

$n = 1;
foreach($hasil as $row){
  ?>
  <tr>
    <?php
    $disabled1  = '';
    $disabled2  = '';
    if($dataCekMenu['tipeEdit']=='0'){
       $disabled1 = 'style = "display : none;"';
    }
    if($dataCekMenu['tipeDelete']=='0'){
       $disabled2 = 'style = "display : none;"';
    }
     ?>
    <td style="vertical-align: top;"><?=$n?></td>
    <td style="vertical-align: top;">
      <button type               = "button" 
              title              = "Edit"
              class              = "btn btn-primary tombolEditAchievement" 
              style              = "color: white;"
              onclick = "inputBiayaSub('<?=$row['idPenjualanDetail']?>')" <?=$disabled1?>>
        <i class="fa fa-calculator"></i>
      </button>
    </td>
    <td style="vertical-align: top;" ><?=wordwrap(ubahTanggalIndo($row['tanggalPenjualan']),50,'<br>')?></td>
    <td style="vertical-align: top;"><?=wordwrap($row['noNota'],50,'<br>')?></td>
    <td style="vertical-align: top;" >Rp <?=wordwrap(ubahToRp($row['nilai']),50,'<br>')?></td>
    <td style="vertical-align: top;"><?=wordwrap($row['namaBahan'],50,'<br>')?></td>
  </tr>
  <?php
  $n++;
}
?>
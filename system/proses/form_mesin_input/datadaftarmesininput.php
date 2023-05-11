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
  'form_mesin_input'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

$sqlLogin  = $db->prepare('SELECT * FROM balistars_pegawai inner join balistars_user on balistars_pegawai.idPegawai=balistars_user.idPegawai inner join balistars_cabang on balistars_pegawai.idCabang=balistars_cabang.idCabang where balistars_user.idUser = ?');
$sqlLogin->execute([$idUserAsli]);
$dataLogin = $sqlLogin->fetch();

extract($_REQUEST);
$tanggal      = explode(' - ', $rentang);
$tanggalAwal  = konversiTanggal($tanggal[0]);
$tanggalAkhir = konversiTanggal($tanggal[1]);

$sql=$db->prepare('
  SELECT * FROM balistars_performa_mesin_input 
  where jenisOrder=? 
  and idCabang=? 
  and (tanggalPerforma between ? and ?) 
  order by tanggalPerforma DESC');
$sql->execute([
  $jenisOrder,
  $dataLogin['idCabang'],
  $tanggalAwal,
  $tanggalAkhir]);
$data = $sql->fetchAll();

$n = 1;
foreach($data as $row){
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
    <td><?=$n?></td>
    <td>
      <button type               = "button" 
              title              = "Edit"
              class              = "btn btn-warning tombolEditAchievement" 
              style              = "color: white;"
              onclick = "editMesinInput('<?=$row['idPerforma']?>')" <?=$disabled1?>>
        <i class="fa fa-edit"></i>
      </button>
      <button type    = "button"
              title   = "Hapus" 
              class   = "btn btn-danger" 
              onclick = "cancelMesinInput('<?=$row['idPerforma']?>')" <?=$disabled2?>>
        <i class="fa fa-trash"></i>
      </button>
    </td>
    <td><?=wordwrap($row['jenisOrder'],50,'<br>')?></td>
    <td><?=wordwrap($row['noNota'],50,'<br>')?></td>
    <td style="text-align: center;"><?=wordwrap(ubahTanggalIndo($row['tanggalPerforma']),50,'<br>')?></td>
    <td><?=wordwrap($row['namaCustomer'],50,'<br>')?></td>
    <td><?=wordwrap($row['namaBahan'],50,'<br>')?></td>
    <td><?=wordwrap($row['ukuran'],50,'<br>')?></td>
    <td style="text-align: center;"><?=wordwrap(ubahToRp($row['qty']),50,'<br>')?></td>
    <td style="text-align: center;"><?=wordwrap(floatval($row['luas']),50,'<br>')?></td>
  </tr>
  <?php
  $n++;
}
?>
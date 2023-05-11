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
  'setor_penjualan_cash'
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

if($idCabang==0){
  $parameter1 =' and idCabang !=?';
} else{
  $parameter1 =' and idCabang =?';
}

if($tipe=='Semua'){
  $parameter2 = ' and balistars_setor_penjualan_cash.tipe!=?';
} else{
  $parameter2 = ' and balistars_setor_penjualan_cash.tipe=?';
}

$sql=$db->prepare('
  SELECT *, userInput.userName as userNameInput, userEdit.userName as userNameEdit FROM balistars_setor_penjualan_cash 
  inner join balistars_bank 
  on balistars_setor_penjualan_cash.idBank=balistars_bank.idBank 
  left join balistars_user userInput
  on balistars_setor_penjualan_cash.idUser=userInput.idUser 
  left join balistars_user userEdit 
  on balistars_setor_penjualan_cash.idUserEdit=userEdit.idUser
  where (balistars_setor_penjualan_cash.tanggalSetor between ? and ?) 
  and statusSetor=?'
  .$parameter1 
  .$parameter2);
$sql->execute([
  $tanggalAwal,
  $tanggalAkhir,
  'Aktif',
  $idCabang,
  $tipe]);
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
    <td><?=$n?></td>
    <td>
      <?php 
      if($row['statusFinal']!='Final'){
      ?>
      <button type               = "button" 
              title              = "Edit"
              class              = "btn btn-primary tombolEditAchievement" 
              style              = "color: white;"
              onclick = "finalSetorPenjualanCash('<?=$row['idSetor']?>')" <?=$disabled2?>>
        <i class="fa fa-check"></i>
      </button>
      <button type    = "button"
              title   = "Hapus" 
              class   = "btn btn-warning" 
              onclick = "editSetorPenjualanCash('<?=$row['idSetor']?>')" <?=$disabled1?>>
        <i class="fa fa-edit"></i>
      </button>
      <?php 
      } ?>
      <button type    = "button"
              title   = "Hapus" 
              class   = "btn btn-danger" 
              onclick = "bukaSetorPenjualanCash('<?=$row['idSetor']?>')" <?=$disabled2?>>
        <i class="fa fa-window-close"></i>
      </button>
    </td>
    <td ><?=wordwrap(ubahTanggalIndo($row['tanggalSetor']),50,'<br>')?></td>
    <td><?=wordwrap($row['namaBank'],50,'<br>')?></td>
    <td >Rp <?=wordwrap(ubahToRp($row['jumlahSetor']),50,'<br>')?></td>
    <td><?=wordwrap($row['userNameInput'],50,'<br>')?></td>
    <td><?=wordwrap($row['userNameEdit'],50,'<br>')?></td>
  </tr>
  <?php
  $n++;
}
?>
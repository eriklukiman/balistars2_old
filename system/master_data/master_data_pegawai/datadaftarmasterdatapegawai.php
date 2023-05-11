<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
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
  'master_data_pegawai'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);

$sqlPegawai  = $db->prepare('SELECT * FROM balistars_pegawai inner join balistars_jabatan on balistars_pegawai.idJabatan=balistars_jabatan.idJabatan inner join balistars_cabang on balistars_pegawai.idCabang=balistars_cabang.idCabang
 where statusPegawai != ? order by namaPegawai');
$sqlPegawai->execute(['Non Aktif']);
$dataPegawai = $sqlPegawai->fetchAll();

$n = 1;
foreach($dataPegawai as $row){
  ?>
  <tr>
    <td><?=$n?></td>
    <td>
      <?php 
      if($dataCekMenu['tipeEdit']=='1'){
       ?>
      <button type               = "button" 
              title              = "Edit"
              class              = "btn btn-warning tombolEditPegawai" 
              style              = "color: white;"
              onclick = "editPegawai('<?=$row['idPegawai']?>')">
        <i class="fa fa-edit"></i>
      </button>
      <?php 
      }
      if($dataCekMenu['tipeDelete']=='1'){
       ?>
      <button type    = "button"
              title   = "Hapus" 
              class   = "btn btn-danger" 
              onclick = "cencelPegawai('<?=$row['idPegawai']?>')">
        <i class="fa fa-trash"></i>
      </button>
      <?php 
      }
       ?>
    </td>
    <td><?=wordwrap($row['NIK'],50,'<br>')?></td>
    <td><?=wordwrap($row['namaPegawai'],50,'<br>')?></td>
    <td><?=ubahTanggalIndo(wordwrap($row['tglMulaiKerja'],50,'<br>'))?></td>
    <td><?=wordwrap($row['alamatPegawai'],50,'<br>')?></td>
    <td><?=wordwrap($row['noTelpPegawai'],50,'<br>')?></td>
    <td><?=wordwrap($row['namaJabatan'],50,'<br>')?></td>
    <td><?=wordwrap($row['namaCabang'],50,'<br>')?></td>
  </tr>
  <?php
  $n++;
}
?>
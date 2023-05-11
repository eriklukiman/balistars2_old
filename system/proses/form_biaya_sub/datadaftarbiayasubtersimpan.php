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

$sqlTampil=$db->prepare('
  SELECT * FROM balistars_biaya_sub 
  where idCabang=? 
  and idPenjualanDetail=? 
  and statusBiayaSub=?
  order by balistars_biaya_sub.tanggalPembayaran DESC');
$sqlTampil->execute([
  $dataLogin['idCabang'],
  $idPenjualanDetail,
  'Aktif']);
$dataTampil=$sqlTampil->fetchAll();

foreach($dataTampil as $data){
  ?>
<tr>
  <td><?=ubahTanggalIndo($data['tanggalPembayaran'])?></td>
  <td><?=$data['namaSupplier']?></td>
  <td>Rp <?=ubahToRp($data['nilaiPembayaran'])?></td>
  <td><?=$data['keterangan']?></td>
  <td>
    <button 
    type    ="button" 
    class   ="btn btn-warning" 
    onclick ="editInputBiayaSub('<?=$data['idBiaya']?>','<?=$data['idPenjualanDetail']?>');">
    <i class="fa fa-edit"></i>
    </button>
    <button 
    type    ="button" 
    class   ="btn btn-danger" 
    onclick ="deleteInputBiayaSub('<?=$data['idBiaya']?>','<?=$data['idPenjualanDetail']?>');">
    <i class="fa fa-trash"></i>
    </button>
  </td>
</tr>
<?php
}
?>



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
  'form_biaya'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);

$tanggal = explode(' - ', $rentang);
$tanggalAwal = konversiTanggal($tanggal[0]);
$tanggalAkhir = konversiTanggal($tanggal[1]); 


$sqlPegawai= $db->prepare('SELECT * from balistars_pegawai where idPegawai=? and statusPegawai=?');
$sqlPegawai->execute([$dataCekMenu['idPegawai'],'Aktif']);
$data = $sqlPegawai->fetch();

$sqlBiaya  = $db->prepare('SELECT * FROM balistars_biaya inner join balistars_pegawai on balistars_biaya.idPegawai = balistars_pegawai.idPegawai inner join balistars_kode_akunting on balistars_biaya.kodeAkunting=balistars_kode_akunting.kodeAkunting WHERE (tanggalBiaya between ? and ?) and tipeBiaya = ? and statusBiaya=? and balistars_biaya.idCabang=? order by tanggalBiaya');
$sqlBiaya->execute([$tanggalAwal,$tanggalAkhir,$tipe,'Aktif',$data['idCabang']]);
$dataBiaya = $sqlBiaya->fetchAll();

$n = 1;
foreach($dataBiaya as $row){
  ?>
  <tr>
    <?php
    $disabled1  = '';
    $disabled2  = '';
    if($dataCekMenu['tipeEdit']=='0'){
       $disabled1 = 'display : none;';
    }
    if($dataCekMenu['tipeDelete']=='0'){
       $disabled2 = 'display : none;';
    }
     ?>
    <td><?=$n?></td>
    <td>
      <button type               = "button" 
              title              = "Edit"
              class              = "btn btn-warning tombolEditBank" 
              style              = "color: white;<?=$disabled1?>"
              onclick = "editBiaya('<?=$row['noNota']?>')">
        <i class="fa fa-edit"></i>
      </button>
      <button type    = "button"
              title   = "Hapus" 
              class   = "btn btn-danger" 
              style="<?=$disabled2?>"
              onclick = "cancelBiaya('<?=$row['noNota']?>')" >
        <i class="fa fa-trash"></i>
      </button>
    </td>
    <td><?=wordwrap($row['noNota'],50,'<br>')?></td>
    <td><?=wordwrap(ubahTanggalIndo($row['tanggalBiaya']),50,'<br>')?></td>
    <td><?=wordwrap($row['keterangan'],50,'<br>')?></td>
    <td>Rp <?=wordwrap(ubahToRp($row['grandTotal']),50,'<br>')?></td>
  </tr>
  <?php
  $n++;
}
?>
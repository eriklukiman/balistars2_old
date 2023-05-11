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
  'laporan_biaya'
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


// $sqlPegawai= $db->prepare('SELECT * from balistars_pegawai where idPegawai=? and statusPegawai=?');
// $sqlPegawai->execute([$dataCekMenu['idPegawai'],'Aktif']);
// $data = $sqlPegawai->fetch();

if($tipe=='Semua'){
  $parameter1 = ' AND balistars_biaya.tipeBiaya != ?';
} else{
  $parameter1 = ' AND balistars_biaya.tipeBiaya = ?';
}

if($idCabang=='0'){
  $parameter2 = ' AND balistars_biaya.idCabang != ?';
} else{
  $parameter2 = ' AND balistars_biaya.idCabang = ?';
}

$sqlBiaya  = $db->prepare('
  SELECT * FROM balistars_biaya 
  inner join balistars_cabang 
  on balistars_biaya.idCabang = balistars_cabang.idCabang 
  left join balistars_user 
  on balistars_biaya.idUser=balistars_user.idUser
  WHERE (balistars_biaya.tanggalBiaya between ? and ?)' . $parameter1 . $parameter2. 
  'and statusBiaya=? order by tanggalBiaya');
$sqlBiaya->execute([
  $tanggalAwal,
  $tanggalAkhir,
  $tipe,
  $idCabang,
  'Aktif']);
$dataBiaya = $sqlBiaya->fetchAll();
//var_dump($sqlBiaya->errorInfo());
$n = 1;
$totalBiaya=0;
foreach($dataBiaya as $row){
  $sqlDetail=$db->prepare('SELECT * FROM balistars_biaya_detail where noNota=? and statusCancel=? order by idBiayaDetail');
  $sqlDetail->execute([$row['noNota'],"oke"]);
  $hasilDetail=$sqlDetail->fetchAll();

  if(!$hasilDetail){
    $rowspan=1;
  }
  else{
    $rowspan=count($hasilDetail);
  }

  ?>
  <tr>
    <td style="vertical-align: top;" rowspan="<?=$rowspan?>"><?=$n?></td>
    <td style="vertical-align: top;" rowspan="<?=$rowspan?>">
      <?php 
      if($dataCekMenu['tipeEdit']=='1'){
       ?>
      <button type               = "button" 
              title              = "Edit"
              class              = "btn btn-warning tombolEditBiaya" 
              style              = "color: white;"
              onclick = "editBiaya('<?=$row['noNota']?>')">
        <i class="fa fa-edit"></i>
      </button>
      <?php 
      }
      if($dataCekMenu['tipeDelete']=='1'){
       ?>
      <button type    = "button"
              title   = "Hapus" 
              class   = "btn btn-danger" 
              style="color: white;"
              onclick = "cancelBiaya('<?=$row['noNota']?>')" >
        <i class="fa fa-trash"></i>
      </button>
      <?php 
      }
       ?>
    </td>
    <td style="vertical-align: top;" rowspan="<?=$rowspan?>"><?=wordwrap(ubahTanggalIndo($row['tanggalBiaya']),50,'<br>')?></td>
    <td style="vertical-align: top;" rowspan="<?=$rowspan?>"><?=wordwrap($row['noNotaBiaya'],50,'<br>')?></td>
   <?php 
   $cek=1;
   if(!$hasilDetail){
    ?>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td style="text-align: right; vertical-align: top;">Rp <?=wordwrap(ubahToRp($row['grandTotal']),50,'<br>')?></td>
    <?php 
    if($dataCekMenu['tipeA2']==1){
      ?>
      <td style="text-align: center; vertical-align: top;"><?=wordwrap($row['tipeBiaya'],50,'<br>')?></td>
    <?php
    } ?>
    <td style="text-align: center; vertical-align: top;"><?=wordwrap($row['userName'],50,'<br>')?></td>
  </tr>
    <?php
   } 
   else{
    foreach($hasilDetail as $item){
      if($cek==1){
        ?>
        <td><?=wordwrap($item['keterangan'],25,'<br>')?></td>
        <td><?=wordwrap(ubahToRp($item['qty']),50,'<br>')?></td>
        <td>Rp <?=wordwrap(ubahToRp($item['hargaSatuan']),50,'<br>')?></td>
        <td>Rp <?=wordwrap(ubahToRp($item['nilai']),50,'<br>')?></td>
        <td style="text-align: right; vertical-align: top;" rowspan="<?=$rowspan?>">Rp <?=wordwrap(ubahToRp($row['grandTotal']),50,'<br>')?></td>
        <?php 
        if($dataCekMenu['tipeA2']==1){
          ?>
          <td style="text-align: center; vertical-align: top;" rowspan="<?=$rowspan?>"><?=wordwrap($row['tipeBiaya'],50,'<br>')?></td>
        <?php
        } ?>
          <td style="text-align: center; vertical-align: top;" rowspan="<?=$rowspan?>"><?=wordwrap($row['userName'],50,'<br>')?></td>
      </tr>
        <?php
      }
      else{
        ?>
        <tr>
          <td><?=wordwrap($item['keterangan'],25,'<br>')?></td>
          <td><?=wordwrap(ubahToRp($item['qty']),50,'<br>')?></td>
          <td>Rp <?=wordwrap(ubahToRp($item['hargaSatuan']),50,'<br>')?></td>
          <td>Rp <?=wordwrap(ubahToRp($item['nilai']),50,'<br>')?></td>
        </tr>
      <?php
      }
      $cek++;
    }
   }
  $totalBiaya=$totalBiaya+$row['grandTotal'];
  $n++;
}
?>
<tr>
  <td colspan="7"></td>
  <td style="text-align: right;">Total Biaya : </td>
  <td style="text-align: right;">Rp <?=ubahToRp($totalBiaya)?></td>
</tr>
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
  'hutang_gedung'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);

$sqlPembelian = $db->prepare('
  SELECT * FROM balistars_hutang_gedung
  where idHutangGedung=?');
$sqlPembelian->execute([$idHutangGedung]);
$dataPembelian=$sqlPembelian->fetch();

$totalPembayaran=0;
$sql=$db->prepare('
  SELECT * FROM balistars_hutang_gedung_pembayaran inner join balistars_bank
  on balistars_hutang_gedung_pembayaran.bankAsalTransfer=balistars_bank.idBank
  where idHutangGedung=? 
  order by tanggalPembayaran');
$sql->execute([$idHutangGedung]);
$hasil=$sql->fetchAll();
$hutangAwal=$dataPembelian['nilaiSewa'];
foreach($hasil as $data){
  ?>
<tr>
  <td><?=ubahTanggalIndo($data['tanggalPembayaran'])?></td>
  <td>Rp <?=ubahToRp($hutangAwal)?></td>
  <td><?=$data['namaBank']?></td>
  <td>Rp <?=ubahToRp($data['jumlahPembayaran'])?></td>
  <td><?=$data['noGiro']?></td>
  <td><?=ubahTanggalIndo($data['tanggalCair'])?></td>
  <td style="text-align: center;"><?=$data['statusCair']?></td>
  <td>
    <?php 
    if($data['statusCair']=="Cair"){
      $totalPembayaran=$totalPembayaran+$data['jumlahPembayaran'];
      $hutangAwal=$hutangAwal-$data['jumlahPembayaran'];
    }
    echo 'Rp '. ubahToRp($hutangAwal);
    ?>
  </td>
  <td>
    <?php 
    if($data['statusCair']=='Belum Cair'){
      ?>
    <button 
    type    ="button" 
    class   ="btn btn-warning" 
    onclick ="editBayarHutangGedung('<?=$data['idHutangGedung']?>','<?=$data['idPembayaran']?>');">
    <i class="fa fa-edit"></i>
    </button>
    <button 
    type    ="button" 
    class   ="btn btn-danger" 
    onclick ="cancelBayarHutangGedung('<?=$data['idHutangGedung']?>','<?=$data['idPembayaran']?>');">
    <i class="fa fa-trash"></i>
    </button>
      <button 
      type    ="button" 
      class   ="btn btn-success" 
      onclick ="finalisasiBayarHutangGedung('<?=$data['idHutangGedung']?>','<?=$data['idPembayaran']?>');">
      <i class="fa fa-check"></i>
      </button>
      <?php
    } ?>
  </td>
</tr>
<?php
}
?>
<tr>
<td colspan="2"></td>
<td style="text-align: right; font-weight: bold;" >Total Pembayaran Cair : </td>
<td colspan="2">Rp <?=ubahToRp($totalPembayaran)?></td>
</tr>



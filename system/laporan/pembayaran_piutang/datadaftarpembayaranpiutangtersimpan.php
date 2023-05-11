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
  'pembayaran_piutang'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);

$subTotalA1=0;
$subTotalA2=0;
$sqlDetail=$db->prepare('SELECT *, balistars_piutang.bankTujuanTransfer 
  FROM balistars_piutang 
  inner join balistars_penjualan 
  on balistars_penjualan.noNota=balistars_piutang.noNota 
  where balistars_piutang.noNota=? 
  order by balistars_piutang.tanggalPembayaran ASC, 
  balistars_piutang.timeStamp ASC');
$sqlDetail->execute([$noNota]);
$dataDetail=$sqlDetail->fetchAll();
foreach($dataDetail as $row){
  ?>
<tr>
  <td><?=tanggalTerbilang($row['tanggalPembayaran'])?></td>
  <td><?=ubahToRp($row['grandTotal'])?></td>
  <td><?=ubahToRp($row['jumlahPembayaran'])?></td>
  <td><?=ubahToRp($row['sisaPiutang'])?></td>
  <td>
    <?php 
    if($row['bankTujuanTransfer']=='-'){
      echo "PPN Dinas";
    }
    else if($row['bankTujuanTransfer']=="0"){
      echo "Cash";
    }
    else{
       $sqlBank=$db->prepare('SELECT namaBank FROM balistars_bank where idBank=?');
       $sqlBank->execute([$row['bankTujuanTransfer']]);
       $dataBank=$sqlBank->fetch();
       echo $dataBank['namaBank'];
    }
    ?>
  </td>
  <td><?=ubahToRp($row['PPH'])?></td>
  <td><?=ubahToRp($row['biayaAdmin'])?></td>
  <td>
    <button 
    type    ="button" 
    class   ="btn btn-success" 
    onclick ="printPembayaranPiutangHistory('<?=$noNota?>','<?=$row['idPiutang']?>');">
    <i class="fa fa-print"></i>
    </button>
  </td>
</tr>
<?php
}
?>

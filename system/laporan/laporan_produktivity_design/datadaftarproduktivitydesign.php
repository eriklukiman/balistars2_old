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
  'laporan_produktivity_design'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);

$rentang=explode(' - ',$rentang);
$tanggalAwal=konversiTanggal($rentang[0]);
$tanggalAkhir=konversiTanggal($rentang[1]);
$durasi = selisihTanggal($tanggalAwal,$tanggalAkhir)+1;

if ($idCabang =='0'){
 $parameter1='and balistars_penjualan.idCabang!=?';
}
else{
 $parameter1='and balistars_penjualan.idCabang=?';
}

$sql=$db->prepare('
  (
    SELECT namaPegawai, balistars_cabang.namaCabang as cabang , count(noNota) as jumlahOrder, sum(grandTotal) as jumlahRupiah
    FROM balistars_penjualan 
    inner join balistars_pegawai 
    on balistars_pegawai.idPegawai=balistars_penjualan.idDesigner 
    inner join balistars_cabang 
    on balistars_penjualan.idCabang=balistars_cabang.idCabang 
    where (tanggalPenjualan between ? and ?) 
    and statusPenjualan=?'
    .$parameter1
    .' group by idDesigner
  )
  UNION
  (
    SELECT "OTHER" as namaPegawai, balistars_cabang.namaCabang as cabang , count(noNota) as jumlahOrder, sum(grandTotal) as jumlahRupiah
    FROM balistars_penjualan 
    inner join balistars_cabang 
    on balistars_penjualan.idCabang=balistars_cabang.idCabang 
    where (tanggalPenjualan between ? and ?)
    and idDesigner =? 
    and statusPenjualan=?'
    .$parameter1
    .' group by idDesigner
  ) order by '.$tipe.' Desc');
  $sql->execute([
    $tanggalAwal,$tanggalAkhir,
    'Aktif',
    $idCabang,
    $tanggalAwal,$tanggalAkhir,
    0,
    'Aktif',
    $idCabang]);
  $hasil=$sql->fetchAll();

  $n=1;
  foreach($hasil as $data){
?>

<tr>
  <td><?=$n?></td>
  <td><?=wordwrap($data['namaPegawai'],50,'<br>')?></td>
  <td><?=$data['cabang']?></td>
  <td style="text-align: center;"><?=ubahToRp($data['jumlahOrder'])?></td>
  <td style="text-align: center;">Rp <?=ubahToRp($data['jumlahRupiah'])?></td>
  <td style="text-align: center;">Rp <?=ubahToRp($data['jumlahRupiah']/$durasi)?></td>
</tr>
<?php
$n++;
}
?>

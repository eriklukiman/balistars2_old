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
  'hutang_mesin'
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
$tanggalAwalSekali  = '2015-01-01';
$tanggalAkhirSekali = waktuKemarin($tanggalAwal);

if($tipe=='Semua'){
  $parameter='';
  $execute=[$tanggalAwalSekali,$tanggalAkhir,'Aktif',$tanggalAwalSekali,$tanggalAkhirSekali,$tanggalAwalSekali,$tanggalAkhirSekali,$tanggalAwal,$tanggalAkhir,$tanggalAwal,$tanggalAkhir];
}
else{
  $parameter=' AND balistars_pembelian_mesin.tipePembelian=?';
  $execute=[$tanggalAwalSekali,$tanggalAkhir,'Aktif',$tipe,$tanggalAwalSekali,$tanggalAkhirSekali,$tanggalAwalSekali,$tanggalAkhirSekali,$tanggalAwal,$tanggalAkhir,$tanggalAwal,$tanggalAkhir];
}

$sqlHutang=$db->prepare('
  SELECT *, dataNota.noNota as noNota FROM
  (
    (
      SELECT DISTINCT noNota,kodeAkunting, namaSupplier, tanggalPembelian FROM balistars_pembelian_mesin WHERE (tanggalPembelian BETWEEN ? and ?) AND statusPembelianMesin=? '.$parameter.'
    )
    as dataNota
    LEFT JOIN
    (
      SELECT SUM(hutangAwal) as hutangAwal, noNota FROM
      (
        (SELECT (0-SUM(jumlahPembayaran)) as hutangAwal, balistars_pembelian_mesin.noNota 
        FROM balistars_hutang_mesin 
        INNER JOIN balistars_pembelian_mesin 
        ON balistars_hutang_mesin.noNota=balistars_pembelian_mesin.noNota 
        WHERE (tanggalCair BETWEEN ? AND ?) 
        GROUP BY noNota)

        UNION

        (SELECT SUM(grandTotal) as hutangAwal, noNota 
        FROM balistars_pembelian_mesin 
        WHERE (tanggalPembelian BETWEEN ? AND ?) 
        GROUP BY balistars_pembelian_mesin.noNota)
      )
      as data1
      GROUP BY noNota
    )
    as dataAkumulasi
    ON dataAkumulasi.noNota = dataNota.noNota
    LEFT JOIN
    (
      SELECT SUM(pembayaran) as pembayaran, SUM(pembelian) as pembelian, noNota
      FROM
      (
        (SELECT SUM(jumlahPembayaran) as pembayaran, 0 as pembelian, balistars_pembelian_mesin.noNota 
        FROM balistars_hutang_mesin 
        INNER JOIN balistars_pembelian_mesin 
        ON balistars_hutang_mesin.noNota=balistars_pembelian_mesin.noNota 
        WHERE (tanggalCair BETWEEN ? AND ?) 
        GROUP BY noNota)

        UNION

        (SELECT  0 as pembayaran, SUM(grandTotal) as pembelian, noNota 
        FROM balistars_pembelian_mesin 
        WHERE (tanggalPembelian BETWEEN ? AND ?) 
        GROUP BY balistars_pembelian_mesin.noNota)
      )
      as data2
      GROUP BY noNota
    )
    as dataMain
    ON dataMain.noNota=dataNota.noNota
  )
');
$sqlHutang->execute($execute);

$dataHutang=$sqlHutang->fetchAll();
$grandTotal=0;
$grandTotalAwal=0;
$grandTotalPembelian=0;
$grandTotalPembayaran=0;

$n = 1;
foreach($dataHutang as $row){
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
              class              = "btn btn-warning tombolEditPembelianMesin" 
              style              = "color: white;<?=$disabled1?>"
              onclick = "editPembelianMesin('<?=$row['noNota']?>')">
        <i class="fa fa-edit"></i>
      </button>
      <button type    = "button"
              title   = "Hapus" 
              class   = "btn btn-danger" 
              style="<?=$disabled2?>"
              onclick = "cancelPembelianMesin('<?=$row['noNota']?>')" >
        <i class="fa fa-trash"></i>
      </button>
      <button type               = "button" 
              title              = "Edit"
              class              = "btn btn-primary tombolBayarPembelianMesin" 
              style              = "color: white;<?=$disabled1?>"
              onclick = "bayarPembelianMesin('<?=$row['noNota']?>')">
        <i class="fa fa-calculator"></i>
      </button>
    </td>
    <td><?=wordwrap($row['noNota'],50,'<br>')?></td>
    <td><?=wordwrap($row['kodeAkunting'],50,'<br>')?></td>
    <td><?=wordwrap(ubahTanggalIndo($row['tanggalPembelian']),50,'<br>')?></td>
    <td><?=wordwrap($row['namaSupplier'],50,'<br>')?></td>
    <td>Rp <?=wordwrap(ubahToRp($row['hutangAwal']),50,'<br>')?></td>
    <td>Rp <?=wordwrap(ubahToRp($row['pembelian']),50,'<br>')?></td>
    <td>Rp <?=wordwrap(ubahToRp($row['pembayaran']),50,'<br>')?></td>
    <td>Rp <?=wordwrap(ubahToRp($row['hutangAwal']+$row['pembelian']-$row['pembayaran']),50,'<br>')?></td>
  </tr>
  <?php
    $grandTotal+=$row['hutangAwal']+$row['pembelian']-$row['pembayaran'];
    $grandTotalAwal+=$row['hutangAwal'];
    $grandTotalPembelian+=$row['pembelian'];
    $grandTotalPembayaran+=$row['pembayaran'];
    $n++;
  }
  ?>
  <tr>
    <td colspan="6" style="text-align: right;"><b>Grand Total</b></td>
    <td><?=ubahToRp($grandTotalAwal)?></td>
    <td><?=ubahToRp($grandTotalPembelian)?></td>
    <td><?=ubahToRp($grandTotalPembayaran)?></td>
    <td><?=ubahToRp($grandTotal)?></td>
    <td></td>
  </tr>

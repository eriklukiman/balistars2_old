<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
include_once $BASE_URL_PHP . '/library/fungsirupiah.php';
include_once $BASE_URL_PHP . '/library/fungsitanggal.php';
include_once 'fungsiakumulasi.php';

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
  'ranking_absensi'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

$flag='';

$n=0;
$totalBiayaKlik=0;
$totalPenjualan=0;
$totalPenjualanBack=0;
$totalProduktivityFix=0;
$totalSetor=0;
$totalBiaya=0;
$totalBiayaHO=0;
$totalPembelianCash=0;
$totalPembelianKredit=0;
$totalPiutang=0;
$totalProduktivity=0;

extract($_REQUEST);

$tanggalAkhir=$tanggal;
$tanggalAkhir=konversiTanggal($tanggalAkhir);
$tanggalPecah=explode('-', $tanggalAkhir);
$tanggalAwal=$tanggalPecah[0].'-'.$tanggalPecah[1].'-01';
$hariAkhir=(int)$tanggalPecah[2];

?>
<table class="table table-bordered">
  <thead class="bg-info text-white">
    <tr >
      <th rowspan="2">No</th>
      <th rowspan="2">Cabang</th>
      <th rowspan="2">Penjualan</th>
      <?php 
      if($tipe=='A1'){
        ?>
         <th rowspan="2">Persentase (%)</th>
        <?php
      }
      ?>
      <th rowspan="2">Setoran Bank</th>
      <th colspan="2">Biaya</th>
      <th colspan="2">Pembelian Bahan</th>
      <?php 
      if($tipe=='A2'){
        ?>
        <th rowspan="2">Klik Cabang</th>
        <?php
      }
      ?>
      <th rowspan="2">Piutang</th>
    </tr>
    <tr>
      <?php 
      if($tipe=='A2'){
        $biayaCol=1;
        ?>
          <th>Biaya Cabang</th>
          <th>Biaya HO</th>
        <?php
      }
      else{
        $biayaCol=2;
        ?>
          <th colspan="2">Biaya Cabang</th>
        <?php
      }
      ?>
      <th>Pembelian Cash</th>
      <th>Pembelian Kredit</th>
    </tr>
  </thead>
  <?php 
  $sqlCabang=$db->prepare('SELECT * 
    FROM balistars_cabang 
    where namaCabang!=? 
    and statusCabang =? 
    order by idCabang');
  $sqlCabang->execute(["HEAD OFFICE","Aktif"]);
  $dataCabang=$sqlCabang->fetchAll();

  $sqlBankCV=$db->prepare('SELECT idBank 
    FROM balistars_bank 
    WHERE namaBank LIKE ? 
    and statusBank=?');
  $sqlBankCV->execute(["%CV%","Aktif"]);
  $dataCV=$sqlBankCV->fetchAll();

  foreach ($dataCabang as $row) {
    $n++;
    $setor=0;
    
    $sqlPenjualanBack=$db->prepare('SELECT SUM(grandTotal) as totalPenjualan 
      FROM balistars_penjualan 
      where idCabang=? 
      and (tanggalPenjualan between ? and ?) 
      and statusPenjualan=?');
    $sqlPenjualanBack->execute([
      $row['idCabang'],
      $tanggalAwal,$tanggalAkhir,
      'Aktif']);

    if($tipe=="A1"){
      foreach ($dataCV as $dataBank) {
        $setor=$setor+debetBank($tanggalAwal,$tanggalAkhir,$dataBank['idBank'],$row['idCabang'],$db);
      } 

      $sqlPenjualan=$db->prepare('SELECT SUM(grandTotal) as totalPenjualan 
        FROM balistars_penjualan 
        where idCabang=? 
        and tipePenjualan=? 
        and (tanggalPenjualan BETWEEN ? and ?) 
        and statusFinalNota=? 
        and statusPenjualan=?'); 
      $sqlPenjualan->execute([
        $row['idCabang'],
        $tipe,
        $tanggalAwal,$tanggalAkhir,
        'final',
        'Aktif']);

      $sqlBiaya=$db->prepare('SELECT SUM(grandTotal) as totalBiaya 
        FROM balistars_biaya 
        where idCabang=? 
        and tipeBiaya=? 
        and (tanggalBiaya BETWEEN ? and ?) 
        and statusBiaya=?');
      $sqlBiaya->execute([
        $row['idCabang'],
        $tipe,
        $tanggalAwal,$tanggalAkhir,
        'Aktif']);

      $sqlPembelianCash=$db->prepare('SELECT SUM(grandTotal) as totalPembelian 
        FROM balistars_pembelian 
        where idCabang=? 
        and idSupplier=? 
        and tipePembelian=? 
        and (tanggalPembelian BETWEEN ? and ?) 
        and status=?');
      $sqlPembelianCash->execute([
        $row['idCabang'],
        0,
        $tipe,
        $tanggalAwal,$tanggalAkhir,
        'Aktif']);

      $sqlPembelianKredit=$db->prepare('SELECT SUM(grandTotal) as totalPembelian 
        FROM balistars_pembelian 
        where idCabang=? 
        and idSupplier!=? 
        and  tipePembelian=? 
        and (tanggalPembelian BETWEEN ? and ?) 
        and status=?');
      $sqlPembelianKredit->execute([
        $row['idCabang'],
        0,
        $tipe,
        $tanggalAwal,$tanggalAkhir,
        'Aktif']);

      $sqlPiutang=$db->prepare('SELECT SUM(data1.piutang) as totalPiutang 
        FROM (SELECT MIN(sisaPiutang) as piutang 
          FROM balistars_piutang 
          inner join balistars_penjualan 
          on balistars_piutang.noNota=balistars_penjualan.noNota 
          where balistars_penjualan.idCabang=? 
          and balistars_penjualan.tipePenjualan=? 
          and balistars_penjualan.statusFinalNota=? 
          and balistars_piutang.tanggalPembayaran<=? 
          group by balistars_piutang.noNota) 
        as data1');
      $sqlPiutang->execute([
        $row['idCabang'],
        "A1",
        "final",
        $tanggalAkhir]);
    }
    else{
      $sqlPenjualan=$db->prepare('SELECT SUM(grandTotal-nilaiPPN) as totalPenjualan 
        FROM balistars_penjualan 
        where idCabang=? 
        and (tanggalPenjualan BETWEEN ? and ?) 
        and statusFinalNota=? 
        and statusPenjualan=?');
      $sqlPenjualan->execute([
        $row['idCabang'],
        $tanggalAwal,$tanggalAkhir,
        'final',
        'Aktif']);

      $sqlTransfer=$db->prepare('SELECT SUM(jumlahPembayaran) as totalTransfer, SUM(biayaAdmin) as totalAdmin, SUM(PPH) as totalPPH  
        FROM balistars_piutang 
        inner join balistars_penjualan 
        on balistars_piutang.noNota=balistars_penjualan.noNota 
        where balistars_piutang.bankTujuanTransfer!=? 
        and balistars_piutang.jenisPembayaran=? 
        and balistars_penjualan.idCabang=? 
        and (balistars_piutang.tanggalPembayaran between ? and ?) 
        and statusPenjualan=?');
      $sqlTransfer->execute([
        0,
        "Transfer",
        $row['idCabang'],
        $tanggalAwal,$tanggalAkhir,
        'Aktif']);  

      $sqlSetor=$db->prepare('SELECT SUM(jumlahSetor) as totalSetor 
        FROM balistars_setor_penjualan_cash 
        where idCabang=? 
        and (tanggalSetor between ? and ?) 
        and statusSetor=?');
      $sqlSetor->execute([
        $row['idCabang'],
        $tanggalAwal,$tanggalAkhir,
        'Aktif']);

      $sqlBiaya=$db->prepare('SELECT SUM(grandTotal) as totalBiaya 
        FROM balistars_biaya 
        where idCabang=? 
        and (tanggalBiaya BETWEEN ? and ?) 
        and statusBiaya=?');
      $sqlBiaya->execute([
        $row['idCabang'],
        $tanggalAwal,$tanggalAkhir,
        'Aktif']);

      $sqlPembelianCash=$db->prepare('SELECT SUM(grandTotal) as totalPembelian 
        FROM balistars_pembelian 
        where idCabang=? 
        and idSupplier=? 
        and (tanggalPembelian BETWEEN ? and ?) 
        and status=?');
      $sqlPembelianCash->execute([
        $row['idCabang'],
        0,
        $tanggalAwal,$tanggalAkhir,
        'Aktif']);

      $sqlPembelianKredit=$db->prepare('SELECT SUM(grandTotal) as totalPembelian 
        FROM balistars_pembelian 
        where idCabang=? 
        and idSupplier!=? 
        and (tanggalPembelian BETWEEN ? and ?) 
        and status=?');
      $sqlPembelianKredit->execute([
        $row['idCabang'],
        0,
        $tanggalAwal,$tanggalAkhir,
        'Aktif']);

      $sqlPiutang=$db->prepare('SELECT SUM(data1.piutang) as totalPiutang 
        FROM (SELECT MIN(sisaPiutang) as piutang 
          FROM balistars_piutang 
          inner join balistars_penjualan 
          on balistars_piutang.noNota=balistars_penjualan.noNota 
          where balistars_penjualan.idCabang=? 
          and balistars_penjualan.statusFinalNota=? 
          and balistars_penjualan.tanggalPenjualan<=? 
          group by balistars_piutang.noNota) 
        as data1');
      $sqlPiutang->execute([
        $row['idCabang'],
        "final",
        $tanggalAkhir]);

      $dataTransfer=$sqlTransfer->fetch();    
      $dataSetor=$sqlSetor->fetch();

      $setor=$dataSetor['totalSetor']
            +$dataTransfer['totalTransfer']
            -$dataTransfer['totalAdmin']
            -$dataTransfer['totalPPH'];

    }

    $dataPenjualan=$sqlPenjualan->fetch();
    $dataPenjualanBack=$sqlPenjualanBack->fetch();
    $dataBiaya=$sqlBiaya->fetch();
    $dataPembelianCash=$sqlPembelianCash->fetch();
    $dataPembelianKredit=$sqlPembelianKredit->fetch();
    $dataPiutang=$sqlPiutang->fetch();

    $penjualan=$dataPenjualan['totalPenjualan'];
    $penjualanBack=$dataPenjualanBack['totalPenjualan'];
    $biaya=$dataBiaya['totalBiaya'];
    $setor=$setor;
    $pembelianCash=$dataPembelianCash['totalPembelian'];
    $pembelianKredit=$dataPembelianKredit['totalPembelian'];

    if($tipe=="A2"){
      $penjualan=$penjualan+fungsiPenyesuaian($db,$tanggalAwal,$tanggalAkhir,$row['idCabang'],"Penjualan");
      $penjualanBack=$penjualanBack+fungsiPenyesuaian($db,$tanggalAwal,$tanggalAkhir,$row['idCabang'],"Penjualan");
      $biaya=$biaya+fungsiPenyesuaian($db,$tanggalAwal,$tanggalAkhir,$row['idCabang'],"Biaya");
      $setor=$setor+fungsiPenyesuaian($db,$tanggalAwal,$tanggalAkhir,$row['idCabang'],"Uang Masuk");
      $pembelianCash=$pembelianCash+fungsiPenyesuaianPembelian($db,$tanggalAwal,$tanggalAkhir,$row['idCabang'],"Pembelian","Cash");
      $pembelianKredit=$pembelianKredit+fungsiPenyesuaianPembelian($db,$tanggalAwal,$tanggalAkhir,$row['idCabang'],"Pembelian","Kredit");
    }
    if($penjualanBack==0){
      $persentase=0;
    }else{
      $persentase=$penjualan*100/$penjualanBack;
      $persentase=round($persentase,2);
    }
    $piutang=$dataPiutang['totalPiutang'];

    ?>
    <tr>
      <td><?=$row['idCabang']?></td>
      <td><?=$row['namaCabang']?></td>
      <td>
        <?php
        echo ubahToRp($penjualan);
        ?>
      </td>
      <?php 
      if($tipe=='A1'){
        ?>
        <td><?=$persentase?>%</td>
        <?php
      }
      ?>
      <td><?=ubahToRp($setor)?></td>
      <td colspan="<?=$biayaCol?>"><?=ubahToRp($biaya)?></td>
      <?php 
      if($tipe=='A2' && $n==1){
        ?>
        <td rowspan="<?=count($dataCabang)?>">
           <?php
          $sqlBiayaHO=$db->prepare('SELECT SUM(grandTotal) as totalBiaya 
            FROM balistars_biaya 
            where idCabang=? 
            and (tanggalBiaya BETWEEN ? and ?) 
            and statusBiaya=?');
          $sqlBiayaHO->execute([
            9,
            $tanggalAwal,$tanggalAkhir,
            'Aktif']);
          $dataBiayaHO=$sqlBiayaHO->fetch();
          echo ubahToRp($dataBiayaHO['totalBiaya']);
          ?>
        </td>
        <?php
      }
      ?>
      <td><?=ubahToRp($pembelianCash)?>
      </td>
      <td><?=ubahToRp($pembelianKredit)?>
      </td>
      <?php 
      if($tipe=='A2'){
        ?>
        <td>
          <?php 
          $sqlDaily=$db->prepare('SELECT sum(jumlahKlik) as daily 
            FROM balistars_performa_mesin_laser 
            where (tanggalPerforma between ? and ?) 
            and idCabang=? 
            and statusKlik=? 
            order by tanggalPerforma DESC, idPerformaLaser DESC limit 1');
          $sqlDaily->execute([
            $tanggalAwal,$tanggalAkhir,
            $row['idCabang'],
            'Aktif']);
          $dataDaily=$sqlDaily->fetch();
          $banyakKlik=$dataDaily['daily'];

          $sqlKlik=$db->prepare('SELECT * 
            FROM balistars_biaya_klik 
            where tanggalBiaya<=? 
            and idCabang=? 
            and statusBiayaKlik=?
            order by tanggalBiaya DESC limit 1');
          $sqlKlik->execute([
            $tanggalAkhir,
            $row['idCabang'],
            'Aktif']);
          $dataKlik=$sqlKlik->fetch();

          echo ubahToRp($banyakKlik*$dataKlik['jumlahBiaya']);
          $totalBiayaKlik=$totalBiayaKlik
                          +($banyakKlik*$dataKlik['jumlahBiaya']);
          ?>
        </td>
        <?php
      }
      ?>
      <td><?=ubahToRp($piutang)?></td>
    </tr>
    <?php
    $totalPenjualan=$totalPenjualan+$penjualan;
    $totalPenjualanBack+=$penjualanBack;
    $totalSetor=$totalSetor+$setor;
    $totalBiaya=$totalBiaya+$biaya;
    $totalPembelianCash=$totalPembelianCash+$pembelianCash;
    $totalPembelianKredit=$totalPembelianKredit+$pembelianKredit;
    $totalPiutang=$totalPiutang+$piutang;
  }
  $totalPenjualan=$totalPenjualan;
  $totalBiaya=$totalBiaya;
  $totalPembelianCash=$totalPembelianCash;
  if($totalPenjualanBack==0){
    $persentaseTotal=0;
  } else{
    $persentaseTotal=round($totalPenjualan*100/$totalPenjualanBack,2);
  }
  if($tipe=='A2'){
    $totalBiayaHO=$dataBiayaHO['totalBiaya'];
  }
  ?>
  <tr>
    <td colspan="2">TOTAL</td>
    <td><?=ubahToRp($totalPenjualan)?></td>
    <?php 
    if($tipe=='A1'){
      ?>
        <td><?=$persentaseTotal?>%</td>
      <?php
    }
    ?>
    <td><?=ubahToRp($totalSetor)?></td>
    <td colspan="<?=$biayaCol?>"><?=ubahToRp($totalBiaya)?></td>
    <?php 
    if($tipe=='A2'){
      ?>
      <td><?=ubahToRp($totalBiayaHO)?></td>
      <?php
    }
    ?>
    <td><?=ubahToRp($totalPembelianCash)?></td>
    <td><?=ubahToRp($totalPembelianKredit)?></td>
    <?php 
    if($tipe=='A2'){
      ?>
      <td><?=ubahToRp($totalBiayaKlik)?></td>
      <?php
    }
    ?>
    <td><?=ubahToRp($totalPiutang)?></td>
  </tr>
</table>
</div>
<div class="row">
<div class="col-md-4">
  <table class="table table-bordered">
    <thead>
      <tr>
        <td class="bg-info text-white" style="width: 30%;">Penjualan Group </td>
        <td></td>
        <td><?=ubahToRp($totalPenjualan)?></td>
      </tr>
      <tr>
        <td class="bg-info text-white">pembelian bahan cash </td>
        <td><?=ubahToRp($totalPembelianCash)?></td>
        <td></td>
      </tr>
      <tr>
        <td class="bg-info text-white">pembelian bahan kredit  </td>
        <td><?=ubahToRp($totalPembelianKredit)?></td>
        <td></td>
      </tr>
      <tr>
        <td class="bg-info text-white">biaya cabang</td>
        <td><?=ubahToRp($totalBiaya)?></td>
        <td></td>
      </tr>
      <?php 
      if($tipe=='A2'){
      ?>
      <tr>
         <td class="bg-info text-white">biaya HO</td>
        <td><?=ubahToRp($totalBiayaHO)?></td>
        <td></td>
      </tr>
      <tr>
        <td class="bg-info text-white">biaya klik cabang</td>
        <td><?=ubahToRp($totalBiayaKlik)?></td>
        <td></td>
      </tr>
      <?php 
      }
      ?> 
      <tr> 
        <td class="bg-info text-white">biaya penyusutan</td>
        <td></td>
        <td>
          <?php
          $totalPenyusutan=0;
          $sqlPenyusutan=$db->prepare('SELECT nilaiPenyusutan 
            FROM balistars_penyusutan 
            where tanggalPenyusutan<=? 
            and tipe=? 
            and statusPenyusutan=?
            order by tanggalPenyusutan DESC limit 1');
          $sqlPenyusutan->execute([
            $tanggalAkhir,
            $tipe,
            "Aktif"]);
          $dataPenyusutan=$sqlPenyusutan->fetch();
          $totalPenyusutan=$dataPenyusutan['nilaiPenyusutan'];
          echo ubahToRp($totalPenyusutan);
          ?>
        </td>
      </tr>
      <tr>
        <td class="bg-info text-white">gaji sementara</td>
       <td style="width: 35%">
        <?php
            $gajiSementara=round(((15*$totalPenjualan/100)+55000000),2);
            echo ubahToRp($gajiSementara);
        ?>
      </td>
        <td></td>
      </tr>
      <tr>
        <td></td>
        <td></td>
        <td></td>
      </tr>
      <tr>
        <td class="bg-info text-white">Total Biaya</td>
        <td></td>
        <td>
          <?php 
          $totalBiayaFix=$totalBiaya
                        +$totalPembelianCash
                        +$totalPembelianKredit
                        +$totalPenyusutan
                        +$totalBiayaHO
                        +$totalBiayaKlik
                        +$gajiSementara;
          echo ubahToRp($totalBiayaFix);
           ?>
        </td>
      </tr>
      <tr>
        <td class="bg-info text-white">Total Sementara Profit</td>
        <td></td>
        <td><?=ubahToRp($totalPenjualan-$totalBiayaFix)?></td>
      </tr>
      <tr>
        <td class="bg-info text-white">Piutang Akumulasi</td>
        <td></td>
        <td><?=ubahToRp($totalPiutang)?></td>
      </tr>
    </thead>
  </table>
</div>
<div class="col-md-8">
  <?php  
  if($tipe=="A1"){
  ?>
  <table class="table">
    <tr class="bg-info text-white">
      <td>Omset</td>
      <td>Total PPN</td>
      <td>PPN Umum</td>
      <td>PPN Dinas</td>
      <td>Pembelian Kredit</td>
      <td>PPN Masukan</td>
      <td>Lebih /Kurang bayar </td>
    </tr>
    <tr>
      <?php  
        $sqlPPN=$db->prepare('SELECT SUM(jumlahPembayaran) as totalPPN 
          FROM balistars_piutang 
          where bankTujuanTransfer=? 
          and jenisPembayaran=? 
          and (tanggalPembayaran between ? and ?)');
        $sqlPPN->execute([
          "-",
          "PPN",
          $tanggalAwal,
          $tanggalAkhir]);
        $dataPPN=$sqlPPN->fetch();
      ?>
      <td><?=ubahToRp($totalPenjualan)?></td>
      <td><?=ubahToRp(round(($totalPenjualan-$dataPPN['totalPPN'])/1.1)*0.1)?></td>
      <td><?=ubahToRp(round(($totalPenjualan/1.1)*0.1))?></td>
      <td>
        <?php
        echo ubahToRp(($dataPPN['totalPPN']/1.1)*0.1);
        ?>
      </td>
      <td><?=ubahToRp($totalPembelianKredit)?></td>
      <td><?=ubahToRp(($totalPembelianKredit/1.1)*0.1)?></td>
      <td><?=ubahToRp((($totalPenjualan-$pembelianKredit)/1.1)*0.1)?></td>
    </tr>
  </table>
  <br>
    <?php 
    }
    if($tipe=="A2"){
      $arrayPenyesuaian=array('Penjualan','Biaya','Pembelian','Uang Masuk');
      for ($i=0; $i < count($arrayPenyesuaian) ; $i++) { 
        $sqlPenyesuaian=$db->prepare('SELECT * 
          FROM balistars_penyesuaian 
          inner join balistars_cabang 
          on balistars_cabang.idCabang=balistars_penyesuaian.idCabang 
          where jenisPenyesuaian=? 
          and (tanggalPenyesuaian between ? and ?) 
          and statusPenyesuaian=?');
        $sqlPenyesuaian->execute([
          $arrayPenyesuaian[$i],
          $tanggalAwal,$tanggalAkhir,
          'Aktif']);
        $dataPenyesuaian=$sqlPenyesuaian->fetchAll();
        if(count($dataPenyesuaian)>0){
        ?>
         <table class="table">
          <tr class="bg-info text-white">
            <td colspan="5">Penyesuaian <?=$arrayPenyesuaian[$i]?></td>
          </tr>
          <tr>
            <td>No</td> 
            <td>Status</td>
            <td>Cabang</td>
            <td>Nominal</td>
            <td>Keterangan</td>
          </tr>
          <?php
          $n=0;  
          foreach ($dataPenyesuaian as $row) {
            $n++;
          ?>
          <tr>
            <td><?=$n?></td>
            <td><?=$row['status']?></td>
            <td><?=$row['namaCabang']?></td>
            <td><?=ubahToRp($row['nominal'])?></td>
            <td><?=$row['keterangan']?></td>
          </tr>
          <?php 
            } 
          }
          ?>
        </table>
        <?php
      }             
    } 
    ?>
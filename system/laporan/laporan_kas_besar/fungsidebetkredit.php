<?php 

function debetKasBesarCabang($idCabang,$tanggal,$saldo,$db,$jenis,$tipe){
  
  if($idCabang=='0'){
    $parameter1 = ' and idCabang !=?';
    $parameter2 = ' and balistars_penjualan.idCabang !=?';
    //var_dump('id cabang = 0');
  } else {
    $parameter1 = ' and idCabang =?';
    $parameter2 = ' and balistars_penjualan.idCabang =?';
    //var_dump('id cabang != 0');
  }
  
  if($tipe=='Semua'){
    $parameter3 = ' and tipePenjualan !=?';
    $parameter4 = ' and balistars_penjualan.tipePenjualan !=?';
    //var_dump('tipe = Semua');

  } else {
    $parameter3 = ' and tipePenjualan =?';
    $parameter4 = ' and balistars_penjualan.tipePenjualan =?';
    //var_dump('tipe != Semua');
  }

  if($jenis=='Semua'){
    $parameter5=' and bankTujuanTransfer IS NOT ?';
    $parameter6=' and balistars_piutang.bankTujuanTransfer IS NOT ?';
    $execute1 = [$tanggal,'Aktif',$idCabang,$tipe,NULL];
    $execute2 = [$tanggal,'Aktif',$idCabang,$tipe,NULL];
    $execute3 = [$tanggal,'Aktif',$idCabang,$tipe,NULL];
    $execute4 = [$tanggal,'Final',$idCabang];
    //var_dump('jenis = Semua');
  }
  elseif($jenis=='Transfer'){
    $parameter5=' and (bankTujuanTransfer !=? and bankTujuanTransfer !=?)';
    $parameter6=' and (balistars_piutang.bankTujuanTransfer !=? and balistars_piutang.bankTujuanTransfer !=?)';

    $execute1 = [$tanggal,'Aktif',$idCabang,$tipe,'0','-'];
    $execute2 = [$tanggal,'Aktif',$idCabang,$tipe,'0','-'];
    $execute3 = [$tanggal,'Aktif',$idCabang,$tipe,'0','-'];
    $execute4 = [$tanggal,'Final',$idCabang];
    //var_dump('jenis = transfer');
  }
  elseif($jenis=='0'){
    $parameter5=' and bankTujuanTransfer =?';
    $parameter6=' and balistars_piutang.bankTujuanTransfer =?';

    $execute1 = [$tanggal,'Aktif',$idCabang,$tipe,$jenis];
    $execute2 = [$tanggal,'Aktif',$idCabang,$tipe,$jenis];
    $execute3 = [$tanggal,'Aktif',$idCabang,$tipe,$jenis];
    $execute4 = [$tanggal,'Final',$idCabang];
     //var_dump('jenis = '.$jenis);
  }
  elseif($jenis=='-'){
    $parameter5=' and bankTujuanTransfer =?';
    $parameter6=' and balistars_piutang.bankTujuanTransfer =?';

    $execute1 = ['error','Aktif',$idCabang,$tipe,$jenis];
    $execute2 = ['error','Aktif',$idCabang,$tipe,$jenis];
    $execute3 = [$tanggal,'Aktif',$idCabang,$tipe,$jenis];
    $execute4 = ['error','Final',$idCabang];
     //var_dump('jenis = '.$jenis);
  }
    

  
  if(($tipe=="Semua" && $jenis=="Semua") 
  || ($tipe=="Semua" && $jenis=='0') 
  || ($tipe=="A2" && $jenis=="Semua") 
  || ($tipe=="A2" && $jenis=='0')){
  }
  else{
    $execute4[0] = 'error';
  }
  

 $sqlDebet1=$db->prepare('SELECT (jumlahPembayaranAwal) as debet, noNota, namaCustomer, idCustomer, bankTujuanTransfer 
  from balistars_penjualan 
  where tanggalPenjualan=?
  and statusPenjualan=?'
  .$parameter1
  .$parameter3
  .$parameter5);

  $sqlDebet2=$db->prepare('SELECT (jumlahPembayaran) as debet, (PPH+biayaAdmin) as kredit, PPH, biayaAdmin, balistars_penjualan.noNota, balistars_piutang.idPiutang, balistars_penjualan.namaCustomer as namaCustomer, balistars_penjualan.idCustomer as idCustomer, balistars_piutang.bankTujuanTransfer as bankTujuan 
    from balistars_piutang 
    inner join balistars_penjualan 
    on balistars_penjualan.noNota=balistars_piutang.noNota 
    where balistars_piutang.tanggalPembayaran=?
    and statusPenjualan=?'
    .$parameter2
    .$parameter4
    .$parameter6);

  $sqlDebet3=$db->prepare('SELECT (jumlahPembayaran) as debet, (PPH+biayaAdmin) as kredit, PPH, biayaAdmin, balistars_penjualan.noNota, balistars_piutang.idPiutang, balistars_penjualan.namaCustomer as namaCustomer, balistars_penjualan.idCustomer as idCustomer, balistars_piutang.bankTujuanTransfer as bankTujuan 
    from balistars_piutang 
    inner join balistars_penjualan 
    on balistars_penjualan.noNota=balistars_piutang.noNota 
    where balistars_piutang.tanggalPembayaran=?
    and statusPenjualan=?'
    .$parameter2
    .$parameter4
    .$parameter6);

  $sqlDebet4=$db->prepare('SELECT *, nilai as debet 
    from balistars_cabang_cash 
    where tanggalCabangCash=? 
    and statusFinal=?'
    .$parameter1);

  $sqlDebet1->execute($execute1);
  $sqlDebet2->execute($execute2);
  $sqlDebet3->execute($execute3);
  $sqlDebet4->execute($execute4);

  $dataDebet1=$sqlDebet1->fetchAll();
  $dataDebet2=$sqlDebet2->fetchAll();
  $dataDebet3=$sqlDebet3->fetchAll();
  $dataDebet4=$sqlDebet4->fetchAll();

foreach($dataDebet1 as $row){
    $namaBahan='';
    $namaCustomer = searchNamaCustomer($db,$row['idCustomer'],$row['namaCustomer']);
    $sqlPenjualanDetail=$db->prepare('SELECT * from balistars_penjualan_detail where noNota=?');
    $sqlPenjualanDetail->execute([$row['noNota']]);
    $dataPenjualanDetail=$sqlPenjualanDetail->fetchAll();
    foreach ($dataPenjualanDetail as $cek) { 
      $namaBahan=$namaBahan.$cek['namaBahan']." / ".$cek['ukuran']." ,"; }
    $saldo[0]+=$row['debet'];
    $saldo[1]+=$row['debet'];
    $saldo[3]++;
    executeKasBesarCabang($row,$row['debet'],$saldo,$namaCustomer,$namaBahan,$tanggal,'debet');
    if($row['bankTujuanTransfer']!='0'){
      $saldo[0]-=$row['debet'];
      $saldo[2]+=$row['debet'];
      $saldo[3]++;
      executeKasBesarCabang($row,$row['debet'],$saldo,$namaCustomer,$namaBahan,$tanggal,'kredit');
    }
  }

  foreach($dataDebet2 as $row){
    $namaCustomer = searchNamaCustomer($db,$row['idCustomer'],$row['namaCustomer']);
    $sqlCheck=$db->prepare('SELECT MIN(idPiutang) as idPiutangMinimal from balistars_piutang where noNota=?');
    $sqlCheck->execute([$row['noNota']]);
    $dataCheck=$sqlCheck->fetch();
    if($dataCheck['idPiutangMinimal']==$row['idPiutang']){
    }
    else if($row['bankTujuan']=='-'){
    }
    else{
      $saldo[0]+=$row['debet'];
      $saldo[1]+=$row['debet'];
      $saldo[3]++;
      executeKasBesarCabang($row,$row['debet'],$saldo,$namaCustomer,'Pembayaran Piutang',$tanggal,'debet');

      if($row['PPH']!=0 ){
        $saldo[0]-=$row['PPH'];
        $saldo[2]+=$row['PPH'];
        $saldo[3]++;
        executeKasBesarCabang($row,$row['PPH'],$saldo,$namaCustomer,'Biaya PPH',$tanggal,'kredit');
      }
      if($row['biayaAdmin']!=0 ){
        $saldo[0]-=$row['biayaAdmin'];
        $saldo[2]+=$row['biayaAdmin'];
        $saldo[3]++;
        executeKasBesarCabang($row,$row['biayaAdmin'],$saldo,$namaCustomer,'Biaya Admin',$tanggal,'kredit');
      }
      if($row['bankTujuan']!=0){
        $saldo[0]-=($row['debet']-$row['kredit']);
        $saldo[2]+=($row['debet']-$row['kredit']);
        $saldo[3]++;
        executeKasBesarCabang($row,($row['debet']-$row['kredit']),$saldo,$namaCustomer,'Transfer Bank',$tanggal,'kredit');
      }
    } 
  }

  foreach($dataDebet3 as $row){
    $namaCustomer = searchNamaCustomer($db,$row['idCustomer'],$row['namaCustomer']);
    if($row['bankTujuan']=='-'){
      $saldo[0]+=$row['debet'];
      $saldo[1]+=$row['debet'];
      $saldo[3]++;
      executeKasBesarCabang($row,$row['debet'],$saldo,$namaCustomer,'PPN Bayar Dinas',$tanggal,'debet');
    }
    // if($row['PPH']!=0 && $row['bankTujuan']!='0'){
    //   $saldo[0]-=$row['PPH'];
    //   $saldo[2]+=$row['PPH'];
    //   $saldo[3]++;
    //   executeKasBesarCabang($row,$row['PPH'],$saldo,$namaCustomer,'Biaya PPH',$tanggal,'kredit');
    // }
    // if($row['biayaAdmin']!=0 && $row['bankTujuan']!='0'){
    //   $saldo[0]-=$row['biayaAdmin'];
    //   $saldo[2]+=$row['biayaAdmin'];
    //   $saldo[3]++;
    //   executeKasBesarCabang($row,$row['biayaAdmin'],$saldo,$namaCustomer,'Biaya Admin',$tanggal,'kredit');
    // }
    if($row['bankTujuan']=='-'){
      $saldo[0]-=$row['debet'];
      $saldo[2]+=$row['debet'];
      $saldo[3]++;
      executeKasBesarCabang($row,$row['debet'],$saldo,$namaCustomer,'PPN Bayar Dinas',$tanggal,'kredit');
    }
    // if($row['PPH']!=0 && $row['bankTujuan']!='0'){
    //   $saldo[0]+=$row['PPH'];
    //   $saldo[1]+=$row['PPH'];
    //   $saldo[3]++;
    //   executeKasBesarCabang($row,$row['PPH'],$saldo,$namaCustomer,'Biaya PPH',$tanggal,'debet');
    // }
    // if($row['biayaAdmin']!=0 && $row['bankTujuan']!='0'){
    //   $saldo[0]+=$row['biayaAdmin'];
    //   $saldo[1]+=$row['biayaAdmin'];
    //   $saldo[3]++;
    //   executeKasBesarCabang($row,$row['biayaAdmin'],$saldo,$namaCustomer,'Biaya Admin',$tanggal,'debet');
    // }
  }

  foreach($dataDebet4 as $row){
    $saldo[0]+=$row['debet'];
    $saldo[1]+=$row['debet'];
    $saldo[3]++;
    executeKasBesarCabang($row,$row['debet'],$saldo,'-','Pemasukan Lain-Lain',$tanggal,'debet');
  }

  return $saldo;

}

function kreditKasBesarCabang($idCabang,$tanggal,$saldo,$db,$jenis,$tipe){
  //var_dump($jenis,$idCabang);
  if($idCabang=='0'){
    $parameter1=' and idCabang != ?';
  }
  else{
    $parameter1=' and idCabang = ?';
  }

  if($tipe=='Semua'){
    $parameter2=' and tipe != ?';
  }
  else{
    $parameter2=' and tipe = ?';
  }
  if($jenis=='Semua' || $jenis=='0'){
    $tanggalSetor=$tanggal;
  }
  elseif($jenis=='Transfer'){
    $tanggalSetor=null;
  }

  $sqlKredit1=$db->prepare('SELECT jumlahSetor as kredit 
    from balistars_setor_penjualan_cash 
    where tanggalSetor=?'
    .$parameter1
    .$parameter2);
  $sqlKredit1->execute([
    $tanggalSetor,
    $idCabang,
    $tipe]);
  $dataKredit1=$sqlKredit1->fetchAll();

  foreach($dataKredit1 as $row){
    $saldo[0]-=$row['kredit'];
    $saldo[2]+=$row['kredit'];
    $saldo[3]++;
    executeKasBesarCabang($row,$row['kredit'],$saldo,'-','Setor Penjualan Cash',$tanggal,'kredit');
  }
  return $saldo;
}



function searchNamaCustomer($db,$idCustomer,$namaCustomer)
{
  if($idCustomer=="0"){
    $namaCustomer=$namaCustomer;
  }
  else{
    $sqlKonsumen=$db->prepare('SELECT namaCustomer from balistars_customer where idCustomer=?');
    $sqlKonsumen->execute([$idCustomer]);
    $dataKonsumen=$sqlKonsumen->fetch();
    $namaCustomer=$dataKonsumen['namaCustomer'];
  }
  return $namaCustomer;
}


function executeKasBesarCabang($row,$debet,$saldo,$namaCustomer,$keterangan,$tanggal,$status)
{
  if(isset($row['noNota'])){
    $noNota=$row['noNota'];
  }
  else{
    $noNota='-';
  }
  ?>
  <tr>
    <td><?=$saldo[3]?></td>
    <td><?=tanggalTerbilang($tanggal)?></td>
    <td><?=$noNota?></td>
    <td><?=$namaCustomer?></td>
    <td><?=wordwrap($keterangan,50,'<br>')?></td>
    <?php  
    if($status=='debet'){
      ?>
      <td><?=ubahToRp($debet)?></td>
      <td>-</td>
      <?php
    }
    else{
      ?>
      <td>-</td>
      <td><?=ubahToRp($debet)?></td>
      <?php
    }
    ?>
    <td><?=ubahToRp($saldo[0])?></td>
  </tr>
  <?php
}
?>


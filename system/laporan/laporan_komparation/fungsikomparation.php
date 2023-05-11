<?php 
function fungsiNilai($tanggalAwal,$tanggalAkhir,$jenis,$idCabang,$db)
  {
    $jenisPecah=explode(' ', $jenis);

    if($jenis=="Penjualan"){
      $sqlPenjualan=$db->prepare('SELECT SUM(grandTotal-nilaiPPN) as totalPenjualan 
      	FROM balistars_penjualan 
      	where idCabang=? 
      	and (tanggalPenjualan BETWEEN ? and ?) 
      	and statusPenjualan=?');
      $sqlPenjualan->execute([
      	$idCabang,
      	$tanggalAwal,$tanggalAkhir,
      	'Aktif']);
      $dataPenjualan=$sqlPenjualan->fetch();
      $nilai=ubahToRp($dataPenjualan['totalPenjualan']);
    }
    else if($jenis=="Klik"){
      $nilai1=0;
      $nilai2=0; 

      $sqlDailyAfter1=$db->prepare('SELECT klikBefore 
      	FROM balistars_performa_mesin_laser 
      	where tanggalPerforma<=? 
      	and idCabang=? 
      	and statusKlik=?
      	order by tanggalPerforma DESC, idPerformaLaser DESC limit 1');
      $sqlDailyAfter1->execute([
      	$tanggalAkhir, 
      	$idCabang,
      	"Aktif"]);
      $dataDailyAfter1=$sqlDailyAfter1->fetch();

      $sqlDailyBefore1=$db->prepare('SELECT klikBefore 
      	FROM balistars_performa_mesin_laser 
      	where tanggalPerforma<? 
      	and idCabang=? 
      	and statusKlik=?
      	order by tanggalPerforma DESC, idPerformaLaser DESC limit 1');
      $sqlDailyBefore1->execute([
      	$tanggalAwal, 
      	$idCabang,
      	"Aktif"]);
      $dataDailyBefore1=$sqlDailyBefore1->fetch();

      $sqlDailyAfter2=$db->prepare('SELECT klikBefore 
      	FROM balistars_performa_mesin_bw 
      	where tanggalPerforma<=? 
      	and idCabang=? 
      	order by tanggalPerforma DESC, idPerformaBW DESC limit 2');
      $sqlDailyAfter2->execute([$tanggalAkhir, $idCabang]);
      $dataDailyAfter2=$sqlDailyAfter2->fetch();

      $sqlDailyBefore2=$db->prepare('SELECT klikBefore 
      	FROM balistars_performa_mesin_bw 
      	where tanggalPerforma<? 
      	and idCabang=? 
      	order by tanggalPerforma DESC, idPerformaBW DESC limit 2');
      $sqlDailyBefore2->execute([
      	$tanggalAwal, 
      	$idCabang]);
      $dataDailyBefore2=$sqlDailyBefore2->fetch();

      $nilai1=$dataDailyAfter1['klikBefore']-$dataDailyBefore1['klikBefore'];
      $nilai2=$dataDailyAfter2['klikBefore']-$dataDailyBefore2['klikBefore'];
      $nilai=$nilai1+$nilai2;

    }
    else if($jenis=="Pembelian Bahan"){
      $sqlPembelian=$db->prepare('SELECT SUM(grandTotal) as totalPembelian 
      	FROM balistars_pembelian 
      	where idCabang=? 
      	and (tanggalPembelian BETWEEN ? and ?) 
      	and statusPembelian=? 
      	and status=?');
      $sqlPembelian->execute([
      	$idCabang,
      	$tanggalAwal,$tanggalAkhir,
      	"Lunas",
      	"Aktif"]);
      $dataPembelian=$sqlPembelian->fetch();
      $nilai=ubahToRp($dataPembelian['totalPembelian']);
    }
    else if($jenis=="Piutang"){
      $sqlPiutang=$db->prepare('SELECT SUM(data1.piutang) as totalPiutang 
      	FROM (SELECT MIN(sisaPiutang) as piutang 
      		FROM balistars_piutang 
      		inner join balistars_penjualan 
      		on balistars_piutang.noNota=balistars_penjualan.noNota 
      		where balistars_penjualan.idCabang=? 
      		and balistars_penjualan.statusFinalNota=? 
      		and (balistars_piutang.tanggalPembayaran BETWEEN ? and ?) 
      		group by balistars_piutang.noNota) as data1');
      $sqlPiutang->execute([
      	$idCabang,
      	"final",
      	$tanggalAwal,$tanggalAkhir]);
      $dataPiutang=$sqlPiutang->fetch();
      $nilai=ubahToRp($dataPiutang['totalPiutang']);
    }
    else if($jenisPecah[0]=="Produktivity"){
      $jenisPenjualan1=strtolower($jenisPecah[1]);
      if($jenisPecah[1]=="BW"){
        $sqlDailyAfter=$db->prepare('SELECT klikBefore 
        	FROM balistars_performa_mesin_bw 
        	where tanggalPerforma<=? 
        	and idCabang=? 
        	order by tanggalPerforma DESC, idPerformaBW DESC limit 1');
        $sqlDailyAfter->execute([$tanggalAkhir, $idCabang]);
        $dataDailyAfter=$sqlDailyAfter->fetch();

        $sqlDailyBefore=$db->prepare('SELECT klikBefore 
          FROM balistars_performa_mesin_bw 
          where tanggalPerforma<? 
          and idCabang=? 
          order by tanggalPerforma DESC, idPerformaBW DESC limit 1');
        $sqlDailyBefore->execute([$tanggalAwal, $idCabang]);
        $dataDailyBefore=$sqlDailyBefore->fetch();

        $nilai=$dataDailyAfter['klikBefore']-$dataDailyBefore['klikBefore'];

      }
      elseif($jenisPecah[1]=="Laser"){
        $sqlDailyAfter=$db->prepare('SELECT klikBefore 
        	FROM balistars_performa_mesin_laser 
        	where tanggalPerforma<=? 
        	and idCabang=? 
          and statusKlik=?
        	order by tanggalPerforma DESC, idPerformaLaser DESC limit 1');
        $sqlDailyAfter->execute([
          $tanggalAkhir, 
          $idCabang,
          "Aktif"]);
        $dataDailyAfter=$sqlDailyAfter->fetch();

        $sqlDailyBefore=$db->prepare('SELECT klikBefore 
          FROM balistars_performa_mesin_laser 
          where tanggalPerforma<? 
          and idCabang=? 
          and statusKlik=? 
          order by tanggalPerforma DESC, idPerformaLaser DESC limit 1');
        $sqlDailyBefore->execute([
          $tanggalAwal, 
          $idCabang,
          "Aktif"]);
        $dataDailyBefore=$sqlDailyBefore->fetch();

        $nilai=$dataDailyAfter['klikBefore']-$dataDailyBefore['klikBefore'];

      }
      else{
        $sqlProduktivity=$db->prepare('SELECT SUM(luas) as total 
          FROM balistars_performa_mesin_'.$jenisPenjualan1.' 
          where idCabang=? 
          and (tanggalPerforma between ? and ?) 
          and statusPerformaMesin'.$jenisPecah[1].'=?');
        $sqlProduktivity->execute([
          $idCabang,
          $tanggalAwal,$tanggalAkhir,
          "Aktif"]);
        $dataProduktivity=$sqlProduktivity->fetch();

        $sqlTambahan=$db->prepare('SELECT SUM(luas) as totalLuas 
          FROM balistars_performa_mesin_input 
          where idCabang=? 
          and (tanggalPerforma between ? and ?) 
          and jenisOrder=? 
          and statusMesinInput=?');
        $sqlTambahan->execute([
          $idCabang, 
          $tanggalAwal,$tanggalAkhir,
          $jenisPecah[1],
          "Aktif"]);
        $dataTambahan=$sqlTambahan->fetch();

        $nilai=$dataProduktivity['total']+$dataTambahan['totalLuas'];

        if($nilai>0){
        }
        else{
          $nilai=0;
        }
      }
        
    }
    else if($jenis=="Biaya Operasional"){
      $sqlBiaya=$db->prepare('SELECT SUM(grandTotal) as totalBiaya 
        FROM balistars_biaya 
        where idCabang=? 
        and (tanggalBiaya BETWEEN ? and ?) 
        and statusBiaya=? 
        and kodeAkunting!=? 
        and kodeAkunting!=? 
        and kodeAkunting!=?');
      $sqlBiaya->execute([
        $idCabang,
        $tanggalAwal,$tanggalAkhir,
        "Aktif",
        "6141",
        "6397",
        "6110"]);
      $dataBiaya=$sqlBiaya->fetch();
      $nilai=ubahToRp($dataBiaya['totalBiaya']);
    }
    else if($jenis=="Biaya Advertising"){
      $sqlBiaya=$db->prepare('SELECT SUM(grandTotal) as totalBiaya 
        FROM balistars_biaya 
        where idCabang=? 
        and (tanggalBiaya BETWEEN ? and ?) 
        and kodeAkunting=? 
        and statusBiaya=?');
      $sqlBiaya->execute([
        $idCabang,
        $tanggalAwal,$tanggalAkhir,
        "6141",
        "Aktif"]);
      $dataBiaya=$sqlBiaya->fetch();
      $nilai=ubahToRp($dataBiaya['totalBiaya']);
    }
    else if($jenis=="Biaya Lain Lain+Promosi"){
      $sqlBiaya1=$db->prepare('SELECT SUM(grandTotal) as totalBiaya 
        FROM balistars_biaya 
        where idCabang=? 
        and (tanggalBiaya BETWEEN ? and ?) 
        and kodeAkunting=? 
        and statusBiaya=?');
      $sqlBiaya1->execute([
        $idCabang,
        $tanggalAwal,$tanggalAkhir,
        "6397",
        "Aktif"]);
      $dataBiaya1=$sqlBiaya1->fetch();

      $sqlBiaya2=$db->prepare('SELECT SUM(grandTotal) as totalBiaya 
        FROM balistars_biaya 
        where idCabang=? 
        and (tanggalBiaya BETWEEN ? and ?) 
        and kodeAkunting=? 
        and statusBiaya=?');
      $sqlBiaya2->execute([
        $idCabang,
        $tanggalAwal,$tanggalAkhir,
        "6110",
        "Aktif"]);
      $dataBiaya2=$sqlBiaya2->fetch();
      $nilai=ubahToRp($dataBiaya1['totalBiaya']+$dataBiaya2['totalBiaya']);
    }
    else if($jenis=="Biaya Klik"){
      
      $banyakKlik=0;

      $sqlDailyAfter=$db->prepare('SELECT klikBefore FROM balistars_performa_mesin_laser 
        where tanggalPerforma<=? 
        and idCabang=? 
        and statusKlik=?
        order by tanggalPerforma DESC, idPerformaLaser DESC limit 1');
      $sqlDailyAfter->execute([
        $tanggalAkhir, 
        $idCabang,
        "Aktif"]);
      $dataDailyAfter=$sqlDailyAfter->fetch();

      $banyakKlik += $dataDailyAfter['klikBefore'];

      $sqlDailyAfter=$db->prepare('SELECT klikBefore 
        FROM balistars_performa_mesin_bw 
        where tanggalPerforma<=? 
        and idCabang=? 
        order by tanggalPerforma DESC, idPerformaBW DESC limit 1');
      $sqlDailyAfter->execute([
        $tanggalAkhir, 
        $idCabang]);
      $dataDailyAfter=$sqlDailyAfter->fetch();

      $banyakKlik += $dataDailyAfter['klikBefore'];

      $sqlDailyBefore=$db->prepare('SELECT klikBefore 
        FROM balistars_performa_mesin_laser 
        where tanggalPerforma<? 
        and idCabang=? 
        and statusKlik=? 
        order by tanggalPerforma DESC, idPerformaLaser DESC limit 1');
      $sqlDailyBefore->execute([
        $tanggalAwal, 
        $idCabang,
        "Aktif"]);
      $dataDailyBefore=$sqlDailyBefore->fetch();

      $banyakKlik -= $dataDailyBefore['klikBefore'];

      $sqlDailyBefore=$db->prepare('SELECT klikBefore 
        FROM balistars_performa_mesin_bw 
        where tanggalPerforma<? 
        and idCabang=? 
        order by tanggalPerforma DESC, idPerformaBW DESC limit 1');
      $sqlDailyBefore->execute([
        $tanggalAwal, 
        $idCabang]);
      $dataDailyBefore=$sqlDailyBefore->fetch();

      $banyakKlik -= $dataDailyBefore['klikBefore'];

      $sqlKlik=$db->prepare('SELECT * 
        FROM balistars_biaya_klik 
        where tanggalBiaya<=? 
        and idCabang=? 
        and statusBiayaKlik=? 
        order by tanggalBiaya DESC limit 1');
      $sqlKlik->execute([
        $tanggalAkhir,
        $idCabang,
        "Aktif"]);
      $dataKlik=$sqlKlik->fetch();

      $nilai=ubahToRp($banyakKlik*$dataKlik['jumlahBiaya']);

    }
    else{
      $nilai="";
    }
    return $nilai;
  }
 ?>
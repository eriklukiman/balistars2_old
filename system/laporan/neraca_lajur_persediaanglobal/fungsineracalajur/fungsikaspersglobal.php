<?php  
function kasPersGlobal($total,$db,$tanggalAwal,$tanggalAkhir,$tipe)
{
	$tanggakPecah 	   = explode('-', $tanggalAwal);
	$tanggalAwalSaldo  = '2019-01-01';
	$tanggalAkhirSaldo = waktuKemarin($tanggalAwal);
	
	$sql 	 = $db->prepare('SELECT 
		CONCAT( ? ,balistars_cabang.namaCabang) as keterangan, 
		CONCAT( ? ,balistars_cabang.idCabang) as kodeACC,
		balistars_cabang.idCabang, saldoAwal, debet, kredit, memorial, 0 as laba
		FROM balistars_cabang 
		LEFT JOIN
		(
	    SELECT SUM(debet-kredit) as saldoAwal, idCabang 
	    FROM
	    (
        (
          SELECT SUM(grandTotal-nilaiPPN) as debet, 0 as kredit, idCabang 
          FROM balistars_pembelian 
          where (tanggalPembelian between ? and ?) 
          AND status="Aktif"
          GROUP BY idCabang
        )
        UNION
        (
          SELECT SUM(nilaiPersediaan) as debet, 0 as kredit, idCabang 
          FROM balistars_persediaan_global 
          where (tanggalPersediaan between ? and ?) 
          and nilaiPersediaan>=0 
          and statusPersediaan="Aktif"
          GROUP BY idCabang
        )
        UNION
        (
          SELECT 0 as debet, SUM(0-nilaiPersediaan) as kredit, idCabang 
          FROM balistars_persediaan_global 
          where (tanggalPersediaan between ? and ?) 
          and nilaiPersediaan<0 
          AND statusPersediaan="Aktif"
          GROUP BY idCabang
        )
	    )
	    as data1
	    GROUP BY data1.idCabang
		) 
		as dataAkumulasi
		ON dataAkumulasi.idCabang=balistars_cabang.idCabang
		LEFT JOIN
		(
	    SELECT SUM(debet) as debet, SUM(kredit) as kredit, idCabang 
	    FROM
	    (
        (
          SELECT SUM(nilaiPersediaan) as debet, 0 as kredit, idCabang 
          FROM balistars_persediaan_global 
          where (tanggalPersediaan between ? and ?) 
          and nilaiPersediaan>=0 
          and statusPersediaan="Aktif"
          GROUP BY idCabang
        )
        UNION
        (
          SELECT 0 as debet, SUM(0-nilaiPersediaan) as kredit, idCabang 
          FROM balistars_persediaan_global 
          where (tanggalPersediaan between ? and ?) 
          and nilaiPersediaan<0 
          and statusPersediaan="Aktif" 
          GROUP BY idCabang
        )
	    )
	    as data2
	    GROUP BY data2.idCabang
		) as dataMain
		ON dataMain.idCabang=balistars_cabang.idCabang
		LEFT JOIN 
		(
	    SELECT SUM(nilaiMemorial) AS memorial, kodeNeracaLajur AS kodeACC 
	    FROM balistars_memorial 
	    WHERE (tanggalMemorial BETWEEN ? and ?) 
	    AND statusMemorial="Aktif"
	    GROUP BY kodeNeracaLajur
		)
		AS dataMemorial
		ON (CONCAT( ? ,balistars_cabang.idCabang))=dataMemorial.kodeACC
    WHERE balistars_cabang.idCabang!=?
    AND balistars_cabang.statusCabang="Aktif"
  ');
	$sql->execute([
		'Pers Global ',
		'1149,',
		$tanggalAwalSaldo, $tanggalAkhirSaldo,
		$tanggalAwalSaldo, $tanggalAkhirSaldo,
		$tanggalAwalSaldo, $tanggalAkhirSaldo,
		
		$tanggalAwal, $tanggalAkhir,
		$tanggalAwal, $tanggalAkhir,

		$tanggalAwal, $tanggalAkhir,
		'1149,',9
	]);
	$data = $sql->fetchAll();
	foreach ($data as $row) {
		$row['keterangan'] = strtoupper($row['keterangan']);
		$total	  = tampilTable(
					($row['kodeACC']),
					($row['keterangan']),
					($row['saldoAwal']),
					($row['debet']),
					($row['kredit']),
					($row['saldoAwal']+$row['debet']-$row['kredit']),
					($row['memorial']),
					($row['laba']),
					($row['saldoAwal']+$row['debet']-$row['kredit']+$row['memorial']),
					$total);
	}
	return $total;
}
?>
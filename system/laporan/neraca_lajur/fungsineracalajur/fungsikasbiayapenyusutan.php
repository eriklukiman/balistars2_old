<?php  
function kasBiayaPenyusutan($total,$db,$tanggalAwal,$tanggalAkhir,$tipe)
{
	$tanggakPecah 	   = explode('-', $tanggalAwal);
	$tanggalAwalSaldo  = '2001-01-01';
	$tanggalAkhirSaldo = waktuKemarin($tanggalAwal);
	$execute = array(
		'5556','1313','BIAYA PEN. MESIN DAN PERLENGKAPAN',
		'6160','1314','BIAYA PEN. KENDARAAN',
		'6335','1316','BIAYA PEN. INVENTARIS DAN PERLENGKAPAN',

		$tanggalAwal,$tanggalAkhir, 'Aktif',

		$tanggalAwal,$tanggalAkhir,
	);
	$sql 	 = $db->prepare('
		SELECT *, dataKode.kodeACC as kodeACC,  dataKode.kodeACC2 as kodeACC2 
		FROM
		( 
	    SELECT ? as kodeACC, ? as kodeACC2, ? as keterangan 
	    UNION 
	    SELECT ? as kodeACC, ? as kodeACC2, ? as keterangan 
	    UNION 
	    SELECT ? as kodeACC, ? as kodeACC2, ? as keterangan 
		)
		as dataKode
		LEFT JOIN
		( 
	    SELECT 0 as saldoAwal, SUM(debet) as debet, SUM(kredit) as kredit, kodeACC2
	    FROM 
	    ( 
		    SELECT 0 as kredit, sum(nilaiPenyusutan) AS debet, kodeAkunting as kodeACC2
	      FROM balistars_mesin_penyusutan
	      where (tanggalPenyusutan BETWEEN ? and ?) 
	      and statusPenyusutan=? 
	      group by kodeACC2
	    ) 
	    as data2 GROUP BY kodeACC2
		) 
		as dataMain
		ON dataKode.kodeACC2=dataMain.kodeACC2
		LEFT JOIN 
		( 
			SELECT SUM(nilaiMemorial) AS memorial, kodeNeracaLajur AS kodeACC2 
			FROM balistars_memorial 
		 	WHERE (tanggalMemorial BETWEEN ? and ?) 
		 	AND statusMemorial="Aktif"
		 	GROUP BY kodeNeracaLajur 
		) AS dataMemorial 
		ON dataKode.kodeACC2=dataMemorial.kodeACC2
	');
	$sql->execute($execute);
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
					($row['saldoAwal']+$row['debet']-$row['kredit']+$row['memorial']),
					(0),
					$total);
	}
	return $total;
}
?>
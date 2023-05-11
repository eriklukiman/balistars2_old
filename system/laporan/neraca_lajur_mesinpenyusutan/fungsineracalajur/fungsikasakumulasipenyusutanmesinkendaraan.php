<?php  
function kasPenyusutanMesinKendaraan($total,$db,$tanggalAwal,$tanggalAkhir,$tipe)
{
	$tanggakPecah 	   = explode('-', $tanggalAwal);
	$tanggalAwalSaldo  = '2001-01-01';
	$tanggalAkhirSaldo = waktuKemarin($tanggalAwal);
	$execute = array(
		'1313','AK. PEN. MESIN DAN PERLENGKAPAN',
		'1314','AK. PEN. KENDARAAN',
		'1316','AK. PEN. INVENTARIS DAN PERLENGKAPAN',

		$tanggalAwalSaldo,$tanggalAkhirSaldo, 'Aktif',

		$tanggalAwal,$tanggalAkhir, 'Aktif',

		$tanggalAwal,$tanggalAkhir,
	);
	$sql 	 = $db->prepare('
		SELECT *, dataKode.kodeACC as kodeACC 
		FROM
		( 
	    SELECT ? as kodeACC, ? as keterangan 
	    UNION 
	    SELECT ? as kodeACC, ? as keterangan 
	    UNION 
	    SELECT ? as kodeACC, ? as keterangan
		)
		as dataKode
		LEFT JOIN
		(
	    SELECT (SUM(debetAwal)-SUM(kreditAwal)) as saldoAwal, kodeACC 
	    FROM 
	    (
        SELECT sum(nilaiPenyusutan) as kreditAwal, 0 AS debetAwal, kodeAkunting as kodeACC
        FROM balistars_mesin_penyusutan
        where (tanggalPenyusutan BETWEEN ? and ?)
        and statusPenyusutan=? 
        group by kodeACC 
		  )
	    as data1 
	    GROUP BY kodeACC
		)
		as dataAkumulasi
		ON dataKode.kodeACC=dataAkumulasi.kodeACC
		LEFT JOIN
		( 
	    SELECT SUM(debet) as debet, SUM(kredit) as kredit, kodeACC 
	    FROM 
	    ( 
		    SELECT sum(nilaiPenyusutan) as kredit, 0 AS debet, kodeAkunting as kodeACC
	      FROM balistars_mesin_penyusutan
	      where (tanggalPenyusutan BETWEEN ? and ?) 
	      and statusPenyusutan=? 
	      group by kodeACC
	    ) 
	    as data2 GROUP BY kodeACC 
		) 
		as dataMain
		ON dataKode.kodeACC=dataMain.kodeACC
		LEFT JOIN 
		( 
			SELECT SUM(nilaiMemorial) AS memorial, kodeNeracaLajur AS kodeACC 
			FROM balistars_memorial 
		 	WHERE (tanggalMemorial BETWEEN ? and ?) 
		 	AND statusMemorial="Aktif" 
		 	GROUP BY kodeNeracaLajur 
		) AS dataMemorial 
		ON dataKode.kodeACC=dataMemorial.kodeACC
	');
	$sql->execute($execute);
	$data = $sql->fetchAll();
	//var_dump($sql->errorInfo());
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
					(0),
					($row['saldoAwal']+$row['debet']-$row['kredit']+$row['memorial']),
					$total);
	}
	return $total;
}
?>
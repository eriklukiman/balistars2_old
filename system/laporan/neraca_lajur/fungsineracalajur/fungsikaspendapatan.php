<?php  
function kasPendapatan($total,$db,$tanggalAwal,$tanggalAkhir,$tipe)
{
	$tanggakPecah 	   = explode('-', $tanggalAwal);
	$tanggalAwalSaldo  = '2019-01-01';
	$tanggalAkhirSaldo = waktuKemarin($tanggalAwal);
	$execute = array(
		'7110','2','Pendapatan Bunga Bank',
		'7115','4','Pendapatan Umum',

		$tanggalAwal,$tanggalAkhir,'Final',

		$tanggalAwal,$tanggalAkhir,
	);
	$sql 	 = $db->prepare('
		SELECT *, dataKode.kodeACC as kodeACC,  dataKode.kodeACC2 as kodeACC2 
		FROM
		( 
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
		    SELECT 0 as debet, SUM(nilai) as kredit, idKodePemasukan as kodeACC2
				FROM balistars_pemasukan_lain 
		    where (tanggalPemasukanLain between ? AND ?) 
		    and statusFinal=? 
		    and statusPemasukanLain="Aktif"
		    GROUP by kodeACC2
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
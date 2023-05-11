<?php  
function kasKendaraanInventaris($total,$db,$tanggalAwal,$tanggalAkhir,$tipe)
{
	$tanggakPecah 	   = explode('-', $tanggalAwal);
	$tanggalAwalSaldo  = '2001-01-01';
	$tanggalAkhirSaldo = waktuKemarin($tanggalAwal);
	$execute = array(
		'1314','Kendaraan',
		'1316','Inventaris dan Perlengkapan',

		'Include',$tanggalAwalSaldo,$tanggalAkhirSaldo,

		'Include',$tanggalAwal,$tanggalAkhir,

		$tanggalAwal,$tanggalAkhir,
	);
	$sql 	 = $db->prepare('
		SELECT *, dataKode.kodeACC as kodeACC FROM
		( 
	    SELECT ? as kodeACC, ? as keterangan 
	    UNION 
	    SELECT ? as kodeACC, ? as keterangan 
		)
		as dataKode
		LEFT JOIN
		(
	    SELECT (SUM(nilai)-SUM(kredit)) as saldoAwal, kodeACC 
	    FROM 
	    (
        SELECT 
        CASE WHEN jenisPPN = ? THEN (hargaSatuan*100/110)*qty 
        ELSE hargaSatuan*qty END AS nilai, 0 as kredit, kodeAkunting as kodeACC 
        FROM balistars_pembelian_mesin_detail 
        inner join balistars_pembelian_mesin 
        on balistars_pembelian_mesin_detail.noNota=balistars_pembelian_mesin.noNota
        where (tanggalPembelian BETWEEN ? and ?) 
        AND statusPembelianMesin="Aktif"
		  )
	    as data1 
	    GROUP BY kodeACC
		)
		as dataAkumulasi
		ON dataKode.kodeACC=dataAkumulasi.kodeACC
		LEFT JOIN
		( 
	    SELECT SUM(nilai) as debet, SUM(kredit) as kredit, kodeACC 
	    FROM 
	    ( 
	     	SELECT CASE WHEN jenisPPN = ? THEN (hargaSatuan*100/110)*qty 
	     	ELSE hargaSatuan*qty END AS nilai, 0 as kredit, kodeAkunting as kodeACC 
	     	FROM balistars_pembelian_mesin_detail 
	     	inner join balistars_pembelian_mesin 
	     	on balistars_pembelian_mesin_detail.noNota=balistars_pembelian_mesin.noNota 
	     	where (tanggalPembelian BETWEEN ? and ?) 
	     	AND statusPembelianMesin="Aktif"
	    ) 
	    as data2 GROUP BY kodeACC 
		) 
		as dataMain
		ON dataKode.kodeACC=dataMain.kodeACC
		LEFT JOIN 
		( 
			SELECT SUM(nilaiMemorial) AS memorial, kodeNeracaLajur AS kodeACC 
			FROM balistars_memorial 
		 	WHERE (tanggalMemorial BETWEEN ? AND ?) 
		 	AND statusMemorial="Aktif"
		 	GROUP BY kodeNeracaLajur 
		) AS dataMemorial 
		ON dataKode.kodeACC=dataMemorial.kodeACC
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
					(0),
					($row['saldoAwal']+$row['debet']-$row['kredit']+$row['memorial']),
					$total);
	}
	return $total;
}
?>
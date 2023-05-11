<?php  
function kasPemutihanPiutang($total,$db,$tanggalAwal,$tanggalAkhir,$tipe)
{
	$tanggakPecah 	   = explode('-', $tanggalAwal);
	$tanggalAwalSaldo  = '2019-01-01';
	$tanggalAkhirSaldo = waktuKemarin($tanggalAwal);
	$execute = array(
		'Pemutihan Piutang ','A1','1131.1','1132.1',
		$tanggalAwalSaldo,$tanggalAkhirSaldo,

		// $tanggalAwal,$tanggalAkhir,
		$tanggalAwal,$tanggalAkhir,

		$tanggalAwal,$tanggalAkhir,
	);
	$sql 	 = $db->prepare('
		SELECT *, CONCAT (?, dataAkumulasi.tipe) as keterangan, dataAkumulasi.kodeACC as kodeACC, 0 as laba
		FROM
		(
			SELECT SUM(debet-kredit) as saldoAwal, tipe,
			 	CASE 
		      WHEN tipe = ? THEN ?
		      ELSE ?
		    END AS kodeACC    
			FROM
			(
				SELECT SUM(balistars_pemutihan_piutang.sisaPiutang) as debet, 0 as kredit, tipePenjualan as tipe 
				FROM balistars_pemutihan_piutang 
				inner join balistars_penjualan 
				on balistars_pemutihan_piutang.noNota=balistars_penjualan.noNota 
				where (tanggalPenjualan BETWEEN ? and ?) 
				AND statusPemutihan="Aktif"
				GROUP BY tipePenjualan
			)
			as data1
			GROUP BY tipe
		)
		as dataAkumulasi
		LEFT JOIN
		(
			SELECT SUM(debet) as debet, sum(kredit) as kredit, tipe 
			FROM
			(
				(	
				SELECT SUM(balistars_pemutihan_piutang.sisaPiutang) as debet, 0 as kredit, tipePenjualan as tipe 
				FROM balistars_pemutihan_piutang 
				inner join balistars_penjualan 
				on balistars_pemutihan_piutang.noNota=balistars_penjualan.noNota 
				where (tanggalPenjualan BETWEEN ? and ?) 
				AND statusPemutihan="Aktif"
				GROUP BY tipePenjualan
				)
				-- UNION ALL 
				-- (	
				-- SELECT 0 as debet, SUM(balistars_pemutihan_piutang.sisaPiutang) as kredit, tipePenjualan as tipe 
				-- FROM balistars_pemutihan_piutang 
				-- inner join balistars_penjualan 
				-- on balistars_pemutihan_piutang.noNota=balistars_penjualan.noNota 
				-- where (tanggalPenjualan BETWEEN ? and ?) 
				-- AND statusPemutihan="Aktif"
				-- GROUP BY tipePenjualan
				-- )
			)
			
			as data2
			GROUP BY tipe
		)
		as dataMain
		ON dataAkumulasi.tipe=dataMain.tipe
		LEFT JOIN 
		(
	    SELECT SUM(nilaiMemorial) AS memorial, kodeNeracaLajur AS kodeACC 
	    FROM balistars_memorial 
	    WHERE (tanggalMemorial BETWEEN ? AND ?) 
	    AND statusMemorial="Aktif"
	    GROUP BY kodeNeracaLajur
		)
		AS dataMemorial
		ON dataAkumulasi.kodeACC=dataMemorial.kodeACC
	');
	$sql->execute($execute);
	$data = $sql->fetchAll();
	var_dump($sql->errorInfo());
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
<?php  
function kasBayarDiMuka($total,$db,$tanggalAwal,$tanggalAkhir,$tipe)
{
	$tanggakPecah 	   = explode('-', $tanggalAwal);
	$tanggalAwalSaldo  = '2015-01-01';
	$tanggalAkhirSaldo = waktuKemarin($tanggalAwal);
	$execute = array(
		'1161','Sewa Dibayar Muka',
		'1161',
		$tanggalAwalSaldo,$tanggalAkhirSaldo,
		$tanggalAwalSaldo,$tanggalAkhirSaldo,

		'1161',
		$tanggalAwal,$tanggalAkhir,
		$tanggalAwal,$tanggalAkhir,

		$tanggalAwal,$tanggalAkhir
	);
	$sql 	 = $db->prepare('
		SELECT saldoAwal, debet, kredit, ? as kodeACC, ? as keterangan, memorial, 0 as laba  
		FROM
		( 
			SELECT SUM(data1.debetAwal-data1.kreditAwal) as saldoAwal , ? as kodeACC
			FROM
			(
				(	
					SELECT  0 as debetAwal, SUM(nilaiPenyusutan) as kreditAwal 
					from balistars_gedung_penyusutan 
					inner join balistars_gedung 
					on balistars_gedung_penyusutan.idGedung=balistars_gedung.idGedung 
					where (tanggalPenyusutan between ? and ?) 
					and statusGedungPenyusutan="Aktif"
				)
				UNION all
				( 
					SELECT SUM(nilaiSewa) as debetAwal, 0 as kreditAwal 
					from balistars_hutang_gedung 
					inner join balistars_gedung 
					on balistars_hutang_gedung.idGedung=balistars_gedung.idGedung 
					where (tanggalSewa between ? and ?) 
					and statusHutangGedung="Aktif"
				)
			) as data1
		) as dataAkumulasi
		LEFT JOIN 
		( 
			SELECT SUM(data2.debet) as debet, SUM(data2.kredit) as kredit, ? as kodeACC
			FROM
			(
				(	
					SELECT  0 as debet, SUM(nilaiPenyusutan) as kredit 
					from balistars_gedung_penyusutan 
					inner join balistars_gedung 
					on balistars_gedung_penyusutan.idGedung=balistars_gedung.idGedung 
					where (tanggalPenyusutan between ? and ?) 
					and statusGedungPenyusutan="Aktif"
				)
				UNION all
				( 
					SELECT SUM(nilaiSewa) as debet, 0 as kredit 
					from balistars_hutang_gedung 
					inner join balistars_gedung 
					on balistars_hutang_gedung.idGedung=balistars_gedung.idGedung 
					where (tanggalSewa between ? and ?) 
					and statusHutangGedung="Aktif"
				)
			) as data2
		) as dataMain
		on dataAkumulasi.kodeACC = dataMain.kodeACC
		LEFT JOIN
		(
		  SELECT SUM(nilaiMemorial) AS memorial, kodeNeracaLajur AS kodeACC 
		  FROM balistars_memorial 
	    WHERE (tanggalMemorial BETWEEN ? AND ?) 
	    And statusMemorial="Aktif"
	    GROUP BY kodeNeracaLajur
		)
		AS dataMemorial
		ON dataAkumulasi.kodeACC = dataMemorial.kodeACC
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
					($row['laba']),
					($row['saldoAwal']+$row['debet']-$row['kredit']+$row['memorial']),
					$total);
	}
	return $total;
}
?>
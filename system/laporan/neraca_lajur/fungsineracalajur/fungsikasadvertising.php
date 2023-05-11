<?php  
function kasAdvertising($total,$db,$tanggalAwal,$tanggalAkhir,$tipe)
{
	$tanggakPecah 	   = explode('-', $tanggalAwal);
	$tanggalAwalSaldo  = '2019-01-01';
	$tanggalAkhirSaldo = waktuKemarin($tanggalAwal);
	$execute = array(
		'1119,2','Kas Advertising',
		'1119,2',
		'Saldo Awal','Advertising',$tanggalAwalSaldo,$tanggalAkhirSaldo,
		'Final','1119,2',$tanggalAwalSaldo,$tanggalAkhirSaldo,
		'Advertising','Final',$tanggalAwalSaldo,$tanggalAkhirSaldo,

		'1119,2',
		'Saldo Awal','Advertising',$tanggalAwal,$tanggalAkhir,
		'Final','1119,2',$tanggalAwal,$tanggalAkhir,
		'Advertising','Final',$tanggalAwal,$tanggalAkhir,

		$tanggalAwal,$tanggalAkhir
	);
	$sql 	 = $db->prepare('
		SELECT saldoAwal, debet, kredit, ? as kodeACC, ? as keterangan, memorial, 0 as laba  
		FROM
		( SELECT SUM(data1.debetAwal-data1.kreditAwal) as saldoAwal , ? as kodeACC
			FROM
			(
				(
					SELECT SUM(nilaiInputNeraca) as debetAwal, 0 AS kreditAwal  
					FROM `balistars_input_neraca` 
					WHERE  jenisInput=? 
					and tipeBiaya=? 
					and ( tanggalInputNeraca BETWEEN ? and  ? )
					and statusInputNeraca="Aktif"
				)
				UNION ALL
				(
					SELECT SUM(nilai) as debetAwal, 0 as kreditAwal 
					FROM `balistars_pengeluaran_lain` 
					WHERE statusFinal=? 
					and kodeAkunting=? 
					and (tanggalPengeluaranLain  BETWEEN ? and ?) 
					and statusPengeluaranLain="Aktif"
				)
				UNION ALL
				( 
					SELECT 0 as debetAwal, SUM(nilai) as kreditAwal 
					FROM balistars_pemasukan_lain 
					INNER JOIN balistars_kode_pemasukan 
					ON balistars_kode_pemasukan.idKodePemasukan=balistars_pemasukan_lain.idKodePemasukan 
					WHERE balistars_kode_pemasukan.tipePemasukan=? 
					and statusFinal=? 
					and (tanggalPemasukanLain  BETWEEN ? and ?) 
					and statusPemasukanLain="Aktif"
				)
			) as data1
		) as dataAkumulasi
		LEFT JOIN 
		( SELECT SUM(data2.debet) as debet, SUM(data2.kredit) as kredit, ? as kodeACC
			FROM
			(
				(
					SELECT SUM(nilaiInputNeraca) as debet, 0 AS kredit  
					FROM `balistars_input_neraca` 
					WHERE  jenisInput=? 
					and tipeBiaya=? 
					and ( tanggalInputNeraca BETWEEN ? and  ? ) 
					and statusInputNeraca="Aktif"
				)
				UNION ALL
				( 
					SELECT SUM(nilai) as debet, 0 as kredit 
					FROM `balistars_pengeluaran_lain` 
					WHERE statusFinal=? 
					and kodeAkunting=? 
					and (tanggalPengeluaranLain  BETWEEN ? and ?) 
					and statusPengeluaranLain="Aktif"
				)
				UNION ALL
				( 
					SELECT 0 as debet, SUM(nilai) as kredit 
					FROM balistars_pemasukan_lain 
					INNER JOIN balistars_kode_pemasukan 
					ON balistars_kode_pemasukan.idKodePemasukan=balistars_pemasukan_lain.idKodePemasukan 
					WHERE balistars_kode_pemasukan.tipePemasukan=? 
					and statusFinal=? 
					and (tanggalPemasukanLain  BETWEEN ? and ?) 
					and statusPemasukanLain="Aktif"
				)
			) as data2
		) as dataMain
		on dataAkumulasi.kodeACC = dataMain.kodeACC
		LEFT JOIN
		(
		  SELECT SUM(nilaiMemorial) AS memorial, kodeNeracaLajur AS kodeACC 
		  FROM balistars_memorial 
		  WHERE (tanggalMemorial BETWEEN ? AND ?) 
		  and statusMemorial="Aktif"
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
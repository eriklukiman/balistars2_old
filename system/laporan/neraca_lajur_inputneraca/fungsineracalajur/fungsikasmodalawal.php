<?php  
function kasModalAwal($total,$db,$tanggalAwal,$tanggalAkhir,$tipe)
{
	$tanggakPecah 	   = explode('-', $tanggalAwal);
	$tanggalAwalSaldo  = '2019-01-01';
	$tanggalAkhirSaldo = waktuKemarin($tanggalAwal);
	$execute = array(
		'Modal Awal',
		'3110', 
		'Debet',$tanggalAwal,$tanggalAkhir,'Modal Awal',
		'Kredit', $tanggalAwal,$tanggalAkhir, 'Modal Awal',

    	'3110', 
		'Saldo Awal',$tanggalAwal,$tanggalAkhir,'Modal Awal',

		$tanggalAwal,$tanggalAkhir,
	);
	$sql = $db->prepare('
		SELECT *, ? as keterangan, dataMain.kodeACC as kodeACC 
		FROM
		(
			SELECT SUM(debet) as debet, SUM(kredit) as kredit, ? as kodeACC 
			FROM
			( 
		    (
					SELECT 0 as kredit, Sum(nilaiInputNeraca) as debet 
					FROM balistars_input_neraca 
					WHERE jenisInput= ?  
					and (tanggalInputNeraca BETWEEN ? and ?) 
					and tipeBiaya= ? 
					and statusInputNeraca="Aktif"
				)
				UNION ALL
				(
					SELECT sum(nilaiInputNeraca) as kredit, 0 as debet 
					FROM balistars_input_neraca 
					WHERE jenisInput= ?  
					and (tanggalInputNeraca BETWEEN ? and ?) 
					and tipeBiaya= ? 
					and statusInputNeraca="Aktif"
				)
      ) as data1
		)
		as dataMain
		LEFT JOIN
		( SELECT SUM(debet-kredit) as saldoAwal, ? as kodeACC 
			FROM 
			(
				SELECT  0 as kredit, sum(nilaiInputNeraca) as debet 
				FROM balistars_input_neraca 
				WHERE jenisInput= ?  
				and (tanggalInputNeraca BETWEEN ? and ?) 
				and tipeBiaya= ? 
				and statusInputNeraca="Aktif"
			) as data2
		) as dataSaldo
		ON dataMain.kodeACC = dataSaldo.kodeACC
		LEFT JOIN 
		(
	    SELECT SUM(nilaiMemorial) AS memorial, kodeNeracaLajur AS kodeACC 
	    FROM balistars_memorial 
	    WHERE (tanggalMemorial BETWEEN ? and ?) 
	    AND statusMemorial="Aktif"
	    GROUP BY kodeNeracaLajur
		)
		AS dataMemorial
		ON dataMain.kodeACC = dataMemorial.kodeACC
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
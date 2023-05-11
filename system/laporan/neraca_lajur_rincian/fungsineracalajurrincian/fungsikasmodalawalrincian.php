<?php  
function kasModalAwal($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total)
{
	$tanggalAwalSaldo  = '2019-01-01';
	$tanggalAkhirSaldo = waktuKemarin($tanggalAwal);
	$executeAwal = array(
		$tanggalAwal 	 ,$tanggalAkhir, $kodeACC,
	);
	$execute = array(
		// $tanggalAwalSaldo 	 ,$tanggalAkhirSaldo,
		// $tanggalAwalSaldo 	 ,$tanggalAkhirSaldo,
		// $tanggalAwalSaldo 	 ,$tanggalAkhirSaldo,

		$tanggalAwal 	 ,$tanggalAkhir, $kodeACC,
		$tanggalAwal 	 ,$tanggalAkhir, $kodeACC,
		$tanggalAwal 	 ,$tanggalAkhir, '6398',
	);

	//$parameter = '0 as saldo';
	$parameter = 'SUM(debet-kredit) as saldo';

	$sqlPropertyAwal = 
	"
		SELECT 
			keterangan, 
			kredit, 
			debet,
			tanggal,
			timeStamp,
			sum(debet-kredit) as saldo
		FROM
		(
			SELECT 'Saldo Awal' as keterangan, 0 as kredit, nilaiInputNeraca as debet, tanggalInputNeraca as tanggal, timeStamp 
			FROM balistars_input_neraca 
			WHERE jenisInput= 'Saldo Awal'  
			and (tanggalInputNeraca BETWEEN ? and ?) 
			and tipeBiaya= ? 
			and statusInputNeraca='Aktif'
		)
		as data
		order by timeStamp ASC
	";


	$sqlProperty = 
	"
		SELECT 
			keterangan, 
			kredit, 
			debet,
			tanggal,
			timeStamp,
			".$parameter."
		FROM
		(
			(
				SELECT 'Debet' as keterangan, 0 as kredit, nilaiInputNeraca as debet, tanggalInputNeraca as tanggal, timeStamp 
				FROM balistars_input_neraca 
				WHERE jenisInput= 'Debet'  
				and (tanggalInputNeraca BETWEEN ? and ?) 
				and tipeBiaya= ? 
				and statusInputNeraca='Aktif'
			)
			UNION ALL
			(
				SELECT 'Kredit' as keterangan, nilaiInputNeraca as kredit, 0 as debet, tanggalInputNeraca as tanggal, timeStamp 
				FROM balistars_input_neraca 
				WHERE jenisInput= 'Kredit'  
				and (tanggalInputNeraca BETWEEN ? and ?) 
				and tipeBiaya= ? 
				and statusInputNeraca='Aktif'
		  )
			UNION ALL
			(
				SELECT 'Hutang Pajak' as keterangan, 0 as kredit, nilai as debet, tanggalPengeluaranLain as tanggal, timeStamp 
				FROM balistars_pengeluaran_lain 
				WHERE (tanggalPengeluaranLain BETWEEN ? and ?) 
				and kodeAkunting= ? 
				and statusPengeluaranLain='Aktif'
		  )
		)
		as data
		order by timeStamp ASC
	";
	$sqlAwal = $sqlPropertyAwal;
	$sqlAwal = $db->prepare($sqlAwal);
	$sqlAwal->execute($executeAwal);
	$dataAwal = $sqlAwal->fetch();

	$total = saldoAwal($dataAwal, $total);

	$sqlProperty = str_replace($parameter,'0 as saldo',$sqlProperty);
	$sql = $sqlProperty;
	$sql = $db->prepare($sql);
	$sql->execute($execute);
	$data = $sql->fetchAll(); 
	//var_dump($sql->errorInfo());
	foreach ($data as $row) {
		$total	  = tampilTable(
					($row['keterangan']),
					($row['tanggal']),
					($row['debet']),
					($row['kredit']),
					($row['saldo']),
					$total);
	}
	return $total;
}
?>
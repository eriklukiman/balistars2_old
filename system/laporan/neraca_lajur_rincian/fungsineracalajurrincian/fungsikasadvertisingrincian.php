<?php  
function kasAdvertising($db,$tanggalAwal,$tanggalAkhir,$total)
{
	$tanggalAwalSaldo  = '2019-01-01';
	$tanggalAkhirSaldo = waktuKemarin($tanggalAwal);
	$executeAwal = array(
		$tanggalAwalSaldo 	 ,$tanggalAkhirSaldo,
		$tanggalAwalSaldo 	 ,$tanggalAkhirSaldo,
		$tanggalAwalSaldo 	 ,$tanggalAkhirSaldo,
	);

	$execute = array(
		$tanggalAwal 	 ,$tanggalAkhir,
		$tanggalAwal 	 ,$tanggalAkhir,
		$tanggalAwal 	 ,$tanggalAkhir,
	);


	//$parameter = '0 as saldo';
	$parameter = 'SUM(debet-kredit) as saldo';
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
				SELECT 'Saldo Awal' as keterangan, 0 as kredit, nilaiInputNeraca as debet, tanggalInputNeraca as tanggal, timeStamp 
				FROM balistars_input_neraca 
				WHERE  jenisInput='Saldo Awal' 
				and tipeBiaya= 'Advertising' 
				and ( tanggalInputNeraca BETWEEN ? and  ?) 
				and statusInputNeraca='Aktif'
			)
			UNION ALL
			(
				SELECT keterangan as keterangan, 0 as kredit, nilai as debet, tanggalPengeluaranLain as tanggal, timeStamp 
				FROM balistars_pengeluaran_lain 
				WHERE statusFinal='Final' 
				and kodeAkunting='1119,2' 
				and (tanggalPengeluaranLain  BETWEEN ? and ?) 
				AND statusPengeluaranLain='Aktif'
			) 
			UNION ALL
			(
				SELECT keterangan as keterangan, nilai as kredit, 0 as debet, tanggalPemasukanLain as tanggal, balistars_pemasukan_lain.timeStamp 
				FROM balistars_pemasukan_lain 
				INNER JOIN balistars_kode_pemasukan 
				ON balistars_kode_pemasukan.idKodePemasukan=balistars_pemasukan_lain.idKodePemasukan 
				WHERE balistars_kode_pemasukan.tipePemasukan='Advertising' 
				and statusFinal='Final' 
				and (tanggalPemasukanLain  BETWEEN ? and ?) 
				AND statusPemasukanLain='Aktif'
			)   
		)
		as data
		order by timeStamp ASC
	";
		$sqlAwal = $sqlProperty;
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
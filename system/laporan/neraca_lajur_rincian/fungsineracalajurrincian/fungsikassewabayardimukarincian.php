<?php  
function kasBayarDiMuka($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total)
{
	$tanggalAwalSaldo  = '2015-01-01';
	$tanggalAkhirSaldo = waktuKemarin($tanggalAwal);

	$executeAwal = array(
		$tanggalAwalSaldo   ,$tanggalAkhirSaldo,
		$tanggalAwalSaldo   ,$tanggalAkhirSaldo,
	);
	$execute = array(
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
				SELECT CONCAT('Penyusutan Gedung ' , namaGedung) as keterangan, 0 as debet, nilaiPenyusutan as kredit, tanggalPenyusutan as tanggal, balistars_gedung_penyusutan.timeStampEdit as timeStamp 
				from balistars_gedung_penyusutan 
				inner join balistars_gedung 
				on balistars_gedung_penyusutan.idGedung=balistars_gedung.idGedung 
				where (tanggalPenyusutan between ? and ?) 
				and statusGedungPenyusutan='Aktif'
			)
			UNION all
			( 
				SELECT CONCAT('Sewa Gedung ' , namaGedung) as keterangan, nilaiSewa as debet, 0 as kredit, tanggalSewa as tanggal, balistars_hutang_gedung.timeStamp 
				from balistars_hutang_gedung 
				inner join balistars_gedung 
				on balistars_hutang_gedung.idGedung=balistars_gedung.idGedung 
				where (tanggalSewa between ? and ?) 
				and statusHutangGedung='Aktif'
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
	// var_dump($sql->errorInfo());
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
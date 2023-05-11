<?php  
function kasPendapatanLain($db,$tanggalAwal,$tanggalAkhir,$total)
{
	$tanggalAwalSaldo  = '2019-01-01';
	$tanggalAkhirSaldo = waktuKemarin($tanggalAwal);
	$execute = array(
		#unlock this for saldo awal
		/*
		$tanggalAwalSaldo,$tanggalAkhirSaldo,
		$tanggalAwalSaldo,$tanggalAkhirSaldo,
		*/
		$tanggalAwal 	 ,$tanggalAkhir,
		$tanggalAwal 	 ,$tanggalAkhir,
	);
	$parameter = '0 as saldo';
		#unlock this for saldo awal
		//$parameter = 'SUM(debet-kredit) as saldo';
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
				SELECT keterangan, tanggalPengeluaranLain as tanggal, 0 as kredit, nilai as debet, timeStamp 
				FROM balistars_pengeluaran_lain 
				WHERE statusFinal='final' 
				and kodeAkunting=0 
				and nilai>0 
				and (tanggalPengeluaranLain BETWEEN ? and ?) 
				and statusPengeluaranLain='Aktif' 
			)
	    UNION ALL
	    (
	    	SELECT keterangan, tanggalPemasukanLain as tanggal, nilai as kredit, 0 as debet, timeStamp 
	    	FROM balistars_pemasukan_lain 
	    	WHERE statusFinal='final' 
	    	and idKodePemasukan=1  
	    	and nilai>0 
	    	and(tanggalPemasukanLain BETWEEN ? and ?) 
	    	and statusPemasukanLain='Aktif'
	    )
		)
		as data
		order by timeStamp ASC		
	";
	$dataAwal['saldo'] = 0;
	saldoAwal($dataAwal, $total);

	$sql = $db->prepare($sqlProperty);
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
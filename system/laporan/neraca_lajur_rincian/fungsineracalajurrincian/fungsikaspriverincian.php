<?php  
function kasPrive($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total)
{
	$tanggalAwalSaldo  = '2019-01-01';
	$tanggalAkhirSaldo = waktuKemarin($tanggalAwal);
	$execute = array(
		// $tanggalAwalSaldo 	 ,$tanggalAkhirSaldo,
		// $tanggalAwalSaldo 	 ,$tanggalAkhirSaldo,
		// $tanggalAwalSaldo 	 ,$tanggalAkhirSaldo,

		$tanggalAwal 	 ,$tanggalAkhir, $kodeACC,
		$tanggalAwal 	 ,$tanggalAkhir, $kodeACC,
	);

	$parameter = '0 as saldo';
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
		    SELECT balistars_biaya.noNota as keterangan, 0 as kredit, grandTotal as debet, tanggalBiaya as tanggal, balistars_biaya.timeStamp 
		    from balistars_biaya 
		    inner join balistars_biaya_detail 
		    on balistars_biaya.noNota=balistars_biaya_detail.noNota 
		    where (tanggalBiaya between ? and ?) 
		    and balistars_biaya_detail.statusCancel='oke' 
		    and kodeAkunting=? 
		    and statusBiaya='Aktif'
	    )
	    UNION ALL
	    (
		    SELECT keterangan as keterangan, 0 as kredit, nilai as debet, tanggalPengeluaranLain as tanggal, timeStamp 
		    from balistars_pengeluaran_lain 
		    where (tanggalPengeluaranLain between  ? and ?) 
		    and statusFinal='Final' 
		    and kodeAkunting=? 
		    and statusPengeluaranLain='Aktif'
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
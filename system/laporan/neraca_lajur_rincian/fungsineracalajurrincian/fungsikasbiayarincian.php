<?php  
function kasBiaya($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total)
{
	$tanggalAwalSaldo  = '2019-01-01';
	$tanggalAkhirSaldo = waktuKemarin($tanggalAwal);
	$execute = array(
		

		$kodeACC,  $tanggalAwal 	 ,$tanggalAkhir,
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
		    SELECT balistars_biaya.noNota as keterangan, nilai as debet, 0 as kredit, tanggalBiaya as tanggal, balistars_biaya.timeStamp 
		    FROM balistars_biaya 
		    INNER JOIN balistars_biaya_detail 
		    ON balistars_biaya.noNota=balistars_biaya_detail.noNota 
		    WHERE balistars_biaya_detail.statusCancel='oke' 
		    and kodeAkunting=? 
		    and (tanggalBiaya BETWEEN ? AND ?) 
		    and statusBiaya='Aktif'
	    )
	    UNION ALL
	    (
		    SELECT keterangan as keterangan, nilai as debet, 0 as kredit, tanggalPengeluaranLain as tanggal, timeStamp 
		    from balistars_pengeluaran_lain 
		    where (tanggalPengeluaranLain between ? AND ?) 
		    and statusFinal='Final' 
		    and kodeAkunting =? 
		    and statusPengeluaranLain='Aktif'
	    )
	    UNION ALL
	    (
		    SELECT noNota as keterangan, (grandTotal-nilaiPPN) as debet, 0 as kredit, tanggalPembelian as tanggal, timeStamp 
		    FROM balistars_pembelian_mesin
		    WHERE (tanggalPembelian BETWEEN ? AND ?) 
		    AND kodeAkunting = ? 
		    AND statusPembelianMesin='Aktif'
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
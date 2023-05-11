<?php  
function persGlobal($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total)
{
	$kode = explode('-', $kodeACC);
	$kodebank = $kode[1];

	$tanggalAwalSaldo  = '2019-01-01';
	$tanggalAkhirSaldo = waktuKemarin($tanggalAwal);
	$executeAwal = array(
		$tanggalAwalSaldo 	 ,$tanggalAkhirSaldo, $kodebank,
		$tanggalAwalSaldo 	 ,$tanggalAkhirSaldo, $kodebank,
		$tanggalAwalSaldo 	 ,$tanggalAkhirSaldo, $kodebank,	
	);
	$execute = array(
		$tanggalAwal 	 ,$tanggalAkhir, $kodebank,
		$tanggalAwal 	 ,$tanggalAkhir, $kodebank,
		$tanggalAwal 	 ,$tanggalAkhir, $kodebank,
		
	);
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
        SELECT noNota as keterangan, 0 as kredit, (grandTotal-nilaiPPN) as debet, tanggalPembelian as tanggal, timeStamp 
        FROM balistars_pembelian 
        where (tanggalPembelian between ? and ?) 	
        AND idCabang = ? 
        AND status='Aktif'
	    )
	    UNION
	    (
	      SELECT 'Persediaan Global' as keterangan, 0 as kredit, nilaiPersediaan as debet, tanggalPersediaan as tanggal, timeStamp 
	      FROM balistars_persediaan_global 
	      where (tanggalPersediaan between ? and ?) 
	      AND nilaiPersediaan>=0 
	      AND idCabang = ? 
	      AND statusPersediaan='Aktif'
	    )
	    UNION
	    (
	      SELECT 'Persediaan Global' as keterangan, (0-nilaiPersediaan) as kredit, 0 as debet, tanggalPersediaan as tanggal, timeStamp 
	      FROM balistars_persediaan_global 
	      where (tanggalPersediaan between ? AND ?) 
	      AND nilaiPersediaan<0 
	      AND idCabang = ? 
	      AND statusPersediaan='Aktif'
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
<?php  
function kasHPP($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total)
{
	$kode = explode('-', $kodeACC);
	$kodeCabang = $kode[1];

	$tanggalAwalSaldo  = '2019-01-01';
	$tanggalAkhirSaldo = waktuKemarin($tanggalAwal);
	$execute = array(
		
		$tanggalAwal 	 ,$tanggalAkhir, $kodeCabang,

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
		  SELECT 'Persediaan Global' as keterangan, 0 as kredit, (0-nilaiPersediaan) as debet, tanggalPersediaan as tanggal, timeStamp 
		  FROM balistars_persediaan_global 
		  where (tanggalPersediaan between ? and ?) 
		  and nilaiPersediaan<0 
		  and idCabang = ?
		  and statusPersediaan='Aktif'     
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
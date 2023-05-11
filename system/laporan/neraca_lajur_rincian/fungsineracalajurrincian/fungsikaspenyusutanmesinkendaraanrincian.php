<?php  
function kasPenyMesin($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total)
{
	$tanggalAwalSaldo  = '2015-01-01';
	$tanggalAkhirSaldo = waktuKemarin($tanggalAwal);

	$kode=explode('-', $kodeACC);
	$kodeAkunting=$kode[1];
	$executeAwal = array(
		$tanggalAwalSaldo   ,$tanggalAkhirSaldo, $kodeAkunting, 'Aktif',
	);
	$execute = array(
		$tanggalAwal 	 ,$tanggalAkhir, $kodeAkunting, 'Aktif',
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
			SELECT noNota as keterangan, nilaiPenyusutan as kredit, 0 AS debet, tanggalPenyusutan as tanggal, timeStamp 
			FROM balistars_mesin_penyusutan
			where (tanggalPenyusutan BETWEEN ? and ?) 
			and kodeAkunting = ? 
			and statusPenyusutan=?
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
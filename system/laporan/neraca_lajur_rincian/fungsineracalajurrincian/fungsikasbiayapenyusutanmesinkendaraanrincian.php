<?php  
function kasBiayaMesin($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total)
{
	$tanggalAwalSaldo  = '2015-01-01';
	$tanggalAkhirSaldo = waktuKemarin($tanggalAwal);
	
	$kode=explode('-', $kodeACC);
	$kodeAkunting=$kode[1];
	$execute = array(
		//$tanggalAwalSaldo   ,$tanggalAkhirSaldo, $kodeAkunting,

		$tanggalAwal 	 ,$tanggalAkhir, $kodeAkunting, 'Aktif',

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
			SELECT CONCAT('Penyusutan ' ,balistars_mesin_penyusutan.noNota) as keterangan, 0 as kredit, nilaiPenyusutan AS debet, tanggalPenyusutan as tanggal, balistars_mesin_penyusutan.timeStamp 
			FROM balistars_mesin_penyusutan 
			where (tanggalPenyusutan BETWEEN ? and ?) 
			and balistars_mesin_penyusutan.kodeAkunting = ? 
			and statusPenyusutan=?
		)
		as data
		order by tanggal ASC
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
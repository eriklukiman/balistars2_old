<?php  
function kasPPH($db,$tanggalAwal,$tanggalAkhir,$total)
{
	$tanggalAwalSaldo  = '2019-01-01';
	$tanggalAkhirSaldo = waktuKemarin($tanggalAwal);
	$execute = array(
		//$tanggalAwalSaldo,$tanggalAkhirSaldo,
		$tanggalAwal 	 ,$tanggalAkhir,
	);
	$parameter = '0 as saldo';
	//$parameter = 'SUM(PPH) as saldo';
	$sqlProperty = 
	"
		SELECT 
			balistars_piutang.noNota as keterangan, 
			0 as kredit, 
			PPH as debet,
			tanggalPembayaran as tanggal,
			balistars_piutang.timeStamp,
			".$parameter." 
		FROM balistars_piutang 
		inner join balistars_penjualan 
		on balistars_piutang.noNota=balistars_penjualan.noNota 
		where (tanggalPembayaran between ? and ?) 
		and PPH>0
		and statusPenjualan='Aktif'
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
<?php  
function kasPPN($db,$tanggalAwal,$tanggalAkhir,$total)
{
	$tanggalAwalSaldo  = '2019-01-01';
	$tanggalAkhirSaldo = waktuKemarin($tanggalAwal);
	$execute = array(
		// $tanggalAwalSaldo 	 ,$tanggalAkhirSaldo,
		// $tanggalAwalSaldo 	 ,$tanggalAkhirSaldo,
		// $tanggalAwalSaldo 	 ,$tanggalAkhirSaldo,
		// $tanggalAwalSaldo 	 ,$tanggalAkhirSaldo,
		// $tanggalAwalSaldo 	 ,$tanggalAkhirSaldo,
		$tanggalAwal 	 ,$tanggalAkhir,
		$tanggalAwal 	 ,$tanggalAkhir,
		$tanggalAwal 	 ,$tanggalAkhir,
		$tanggalAwal 	 ,$tanggalAkhir,
		$tanggalAwal 	 ,$tanggalAkhir
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
			  SELECT balistars_penjualan.noNota as keterangan, tanggalPembayaran as tanggal, 0 as kredit, jumlahPembayaran as debet, balistars_piutang.timeStamp as timeStamp 
			  FROM balistars_piutang 
			  INNER JOIN balistars_penjualan 
			  on balistars_piutang.noNota=balistars_penjualan.noNota 
			  WHERE balistars_piutang.jenisPembayaran='PPN' 
			  and jumlahPembayaran>0 
			  and (tanggalPembayaran between ? and ?) 
			  and statusPenjualan='Aktif'
		  )
		  UNION ALL
		  (
		  	SELECT noNota as keterangan, tanggalPembelian as tanggal, 0 as kredit, nilaiPPN as debet, timeStamp 
		  	FROM balistars_pembelian 
		  	WHERE nilaiPPN>0 
		  	and (tanggalPembelian BETWEEN ? and ?) 
		  	and status='Aktif'
		  )
		  UNION ALL
		  (
		  	SELECT noNota as keterangan, tanggalPenjualan as tanggal, nilaiPPN as kredit, 0 as debet, timeStamp 
		  	FROM balistars_penjualan 
		  	WHERE statusFinalNota='final' 
		  	and nilaiPPN>0 
		  	and(tanggalPenjualan BETWEEN ? and ?) 
		  	and statusPenjualan='Aktif'
		  )
		  UNION ALL
		  (
		  	SELECT noNota as keterangan, tanggalPembelian as tanggal, 0 as kredit, nilaiPPN as debet, timeStamp 
		  	FROM balistars_pembelian_mesin 
		  	WHERE nilaiPPN>0 
		  	and (tanggalPembelian BETWEEN ? and ?) 
		  	and statusPembelianMesin='Aktif'
		  )
		  UNION ALL
		  (
		  	SELECT keterangan as keterangan, tanggalPengeluaranLain as tanggal, 0 as kredit, nilai as debet, timeStamp 
		  	FROM balistars_pengeluaran_lain 
		  	WHERE statusFinal='final' 
		  	and kodeAkunting='6380' 
		  	and nilai>0 
		  	and (tanggalPengeluaranLain  BETWEEN ? and ?)
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
<?php  
function kasHutangA1($db,$tanggalAwal,$tanggalAkhir,$total)
{
	$tanggalAwalSaldo  = '2015-01-01';
	$tanggalAkhirSaldo = waktuKemarin($tanggalAwal);
	$executeAwal = array(
		$tanggalAwalSaldo 	 ,$tanggalAkhirSaldo,
		$tanggalAwalSaldo 	 ,$tanggalAkhirSaldo,
		$tanggalAwalSaldo 	 ,$tanggalAkhirSaldo,
		$tanggalAwalSaldo 	 ,$tanggalAkhirSaldo,
	);
	$execute = array(
		$tanggalAwal 	 ,$tanggalAkhir,
		$tanggalAwal 	 ,$tanggalAkhir,
		$tanggalAwal 	 ,$tanggalAkhir,
		$tanggalAwal 	 ,$tanggalAkhir,
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
				SELECT noNota as keterangan, grandTotal as kredit, 0 as debet, tanggalPembelian as tanggal, timeStamp 
				FROM balistars_pembelian 
				where (tanggalPembelian between ? and ?) 
				and idSupplier>0 
				and tipePembelian='A1' 
				and status='Aktif'
			)
      UNION ALL
      (
      	SELECT balistars_hutang.noNota as keterangan, 0 as kredit, balistars_hutang.jumlahPembayaran as debet, balistars_hutang.tanggalCair as tanggal, balistars_hutang.timeStamp 
      	FROM balistars_hutang 
      	inner join balistars_pembelian 
      	on balistars_hutang.noNota=balistars_pembelian.noNota 
      	where (balistars_hutang.tanggalCair between ? and ?) 
      	and balistars_pembelian.tipePembelian='A1'
      	and statusHutang='Aktif'
      )
      UNION ALL
      (
      	SELECT noNota as keterangan, grandTotal as kredit, 0 as debet, tanggalPembelian as tanggal, timeStamp 
      	FROM balistars_pembelian_mesin 
      	where (tanggalPembelian BETWEEN ? and ?) 
      	and tipePembelian = 'A1' 
      	and statusPembelianMesin='Aktif'
      )
      UNION ALL
      (
      	SELECT balistars_pembelian_mesin.noNota as keterangan, 0 as kredit, jumlahPembayaran as debet, balistars_hutang_mesin.tanggalCair as tanggal, balistars_hutang_mesin.timeStamp 
      	FROM balistars_hutang_mesin 
      	inner join balistars_pembelian_mesin 
      	on balistars_pembelian_mesin.noNota=balistars_hutang_mesin.noNota 
      	where (balistars_hutang_mesin.tanggalCair BETWEEN ? and ?) 
      	and tipePembelian='A1'
      	and statusPembelianMesin='Aktif'
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
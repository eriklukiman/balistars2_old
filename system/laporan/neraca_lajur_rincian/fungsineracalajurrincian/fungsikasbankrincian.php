<?php  
function kasBank($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total)
{
	$kode = explode('-', $kodeACC);
	$kodebank = $kode[1];

	$tanggalAwalSaldo  = '2019-01-01';
	$tanggalAkhirSaldo = waktuKemarin($tanggalAwal);
	$executeAwal = array(
		$tanggalAwalSaldo 	 ,$tanggalAkhirSaldo, $kodebank,
		$tanggalAwalSaldo 	 ,$tanggalAkhirSaldo, $kodebank,
		$tanggalAwalSaldo 	 ,$tanggalAkhirSaldo, $kodebank,
		$tanggalAwalSaldo 	 ,$tanggalAkhirSaldo, $kodebank,
		$tanggalAwalSaldo 	 ,$tanggalAkhirSaldo, $kodebank,
		$tanggalAwalSaldo 	 ,$tanggalAkhirSaldo, $kodebank,
		$tanggalAwalSaldo 	 ,$tanggalAkhirSaldo, $kodebank,
		$tanggalAwalSaldo 	 ,$tanggalAkhirSaldo, $kodebank,
		$tanggalAwalSaldo 	 ,$tanggalAkhirSaldo, $kodebank,
		$tanggalAwalSaldo 	 ,$tanggalAkhirSaldo, $kodebank,
		$tanggalAwalSaldo 	 ,$tanggalAkhirSaldo, $kodebank,
		$tanggalAwalSaldo 	 ,$tanggalAkhirSaldo, $kodebank,
    $tanggalAwalSaldo    ,$tanggalAkhirSaldo, $kodebank,
			
	);
	$execute = array(
		$tanggalAwal 	 ,$tanggalAkhir, $kodebank,
		$tanggalAwal 	 ,$tanggalAkhir, $kodebank,
		$tanggalAwal 	 ,$tanggalAkhir, $kodebank,
		$tanggalAwal 	 ,$tanggalAkhir, $kodebank,
		$tanggalAwal 	 ,$tanggalAkhir, $kodebank,
		$tanggalAwal 	 ,$tanggalAkhir, $kodebank,
		$tanggalAwal 	 ,$tanggalAkhir, $kodebank,
		$tanggalAwal 	 ,$tanggalAkhir, $kodebank,
		$tanggalAwal 	 ,$tanggalAkhir, $kodebank,
		$tanggalAwal 	 ,$tanggalAkhir, $kodebank,
		$tanggalAwal 	 ,$tanggalAkhir, $kodebank,
		$tanggalAwal 	 ,$tanggalAkhir, $kodebank,
    $tanggalAwal   ,$tanggalAkhir, $kodebank,		
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
			(
        SELECT 'Setor Penjualan Cash' as keterangan, 0 AS kredit, jumlahSetor AS debet, tanggalSetor as tanggal, timeStamp 
        FROM balistars_setor_penjualan_cash 
        WHERE (tanggalSetor BETWEEN ? AND ?) 
        AND statusFinal='Final' 
        AND idBank = ? 
        AND statusSetor='Aktif'
      )
      UNION ALL
      (
        SELECT keterangan as keterangan, 0 AS kredit, jumlahSetor AS debet, tanggalSetor as tanggal, timeStamp 
        FROM balistars_kas_kecil_setor 
        WHERE (tanggalSetor BETWEEN ? AND ?) 
        AND statusFinal='Final' 
        AND idBank = ?
        AND statusKasKecilSetor='Aktif'
      )
      UNION ALL
      (
        SELECT 'Transfer antar Bank' as keterangan, 0 as kredit, nilaiTransfer AS debet, tanggalTransfer as tanggal, timeStamp 
        FROM balistars_bank_transfer 
        WHERE (tanggalTransfer BETWEEN ? AND ?) 
        AND statusTransfer='final' 
        AND idBankTujuan = ?
      )
      UNION ALL
      (
        SELECT balistars_piutang.noNota as keterangan, 0 AS kredit, (jumlahPembayaran-biayaAdmin-PPH) AS debet, balistars_piutang.tanggalPembayaran as tanggal, balistars_piutang.timeStamp as timeStamp 
        FROM balistars_piutang 
        inner join balistars_penjualan 
        on balistars_penjualan.noNota=balistars_piutang.noNota 
        WHERE (balistars_piutang.tanggalPembayaran BETWEEN ? AND ?) 
        AND balistars_piutang.bankTujuanTransfer!='0' 
        AND balistars_piutang.bankTujuanTransfer = ? 
        AND statusPenjualan='Aktif'
      )
      UNION ALL
      (
        SELECT keterangan as keterangan, 0 AS kredit, nilai AS debet, tanggalPemasukanLain as tanggal, timeStamp 
        FROM balistars_pemasukan_lain 
        WHERE (tanggalPemasukanLain BETWEEN ? AND ?) 
        AND statusFinal='final' 
        AND idBank = ? 
        AND statusPemasukanLain='Aktif'
      )
      UNION ALL
      (
        SELECT keterangan as keterangan, 0 AS kredit, (dpp+ppn) AS debet, tanggalPenjualan as tanggal, timeStamp 
        FROM balistars_penjualan_mesin 
        WHERE (tanggalPenjualan BETWEEN ? AND ?) 
        AND idBank = ? 
        AND statusPenjualanMesin='Aktif'
      )
      UNION ALL
      (
        SELECT 'Transfer antar Bank' as keterangan, nilaiTransfer AS kredit, 0 AS debet, tanggalTransfer as tanggal, timeStamp FROM balistars_bank_transfer
        WHERE (tanggalTransfer BETWEEN ? AND ?) AND statusTransfer='final' AND idBankAsal = ?
      )
      UNION ALL
      (
        SELECT keterangan as keterangan, nilaiApproved AS kredit, 0 AS debet, tanggalOrder as tanggal, timeStamp 
        FROM balistars_kas_kecil_order 
        WHERE (tanggalOrder BETWEEN ? AND ?) 
        AND statusApproval='approved' 
        AND bankAsalTransfer!='0' 
        AND bankAsalTransfer = ? 
        AND statusKasKecilOrder='Aktif'
      )
      UNION ALL
      (
        SELECT noNota as keterangan, jumlahPembayaran AS kredit, 0 AS debet, tanggalPembayaran as tanggal, timeStamp 
        FROM balistars_hutang_mesin 
        WHERE (tanggalPembayaran BETWEEN ? AND ?) 
        AND jenisPembayaran='Giro' 
        AND statusCair='Cair' 
        AND bankAsalTransfer =?
      )
      UNION ALL
      (
        SELECT keterangan as keterangan, nilai AS kredit, 0 AS debet, tanggalPengeluaranLain as tanggal, timeStamp 
        FROM balistars_pengeluaran_lain 
        WHERE (tanggalPengeluaranLain BETWEEN ? AND ?) 
        AND statusFinal= 'Final' 
        AND idBank = ? 
        AND statusPengeluaranLain='Aktif'
      )
      UNION ALL
      (
          SELECT 'Pembayaran Hutang Gedung' as keterangan, jumlahPembayaran AS kredit, 0 AS debet, tanggalPembayaran as tanggal, timeStamp 
          FROM balistars_hutang_gedung_pembayaran 
          WHERE (tanggalPembayaran BETWEEN ? AND ?) 
          AND jenisPembayaran='Giro' 
          AND statusCair='Cair' 
          AND bankAsalTransfer = ? 
          AND statusPembayaranHutangGedung='Aktif'
      )
      UNION ALL
      (
        SELECT 'DP Pembelian Giro' as keterangan, dp AS kredit, 0 AS debet, tanggalCairDp as tanggal, timeStamp FROM balistars_dpgiro 
        WHERE (tanggalCairDp BETWEEN ? and ?)
        AND idBank = ?
        AND jenisGiro = 'DP'
        AND statusDpGiro = 'Aktif'
      )
      UNION ALL
      (
        SELECT 'Pelunasan Pembelian Giro' as keterangan, dp AS kredit, 0 AS debet, tanggalCairDp as tanggal, timeStamp FROM balistars_dpgiro 
        WHERE (tanggalCairDp BETWEEN ? and ?)
        AND idBank = ?
        AND jenisGiro = 'Pelunasan'
        AND statusDpGiro = 'Aktif'
      )
		)
		as data
		order by timeStamp ASC
	";


	//$sql = sqlSwitch($sqlProperty,$parameter);
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
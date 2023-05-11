<?php  
function generateNoNotaA1($db,$idCabang)
{
	$sql=$db->prepare('SELECT * from balistars_nomor where 
		jenis =? 
		and status=? 
		order by tanggal DESC limit 1');
	$sql->execute([
		'NotaA1',
		'Aktif']);
	$data=$sql->fetch();
	$noNota ='PJ'.$idCabang.'-'.date("Ym").'-'.str_pad($data['nomorUrut'], 5, '0', STR_PAD_LEFT);
	return $noNota;
}

function updateNoNotaA1($db)
{
	$sql=$db->prepare('UPDATE balistars_nomor SET 
		nomorUrut=nomorUrut+1,
		tanggal =?
		where jenis = ? 
		and status = ?
		order by tanggal LIMIT 1 ');
	$sql->execute([
		date('Y-m-d'), 
		'NotaA1',
		'Aktif']);
}

function updateNoNotaBaruA1($db)
{
	// $sql=$db->prepare('UPDATE balistars_nomor SET 
	// 	status =? 
	// 	where jenis = ? 
	// 	order by tanggal LIMIT 1 ');
	// $sql->execute([
	// 	'Non Aktif', 
	// 	'NotaA1']);

	$sql=$db->prepare('UPDATE balistars_nomor SET 
		nomorUrut=1,
		tanggal =? 
		where jenis = ?');
	$sql->execute([
		date('Y-m-d'), 
		'NotaA1']);
}

function generateNoNotaA2($db,$idCabang)
{
	$sql=$db->prepare('SELECT * from balistars_nomor where 
		jenis = ? 
		and status = ? 
		order by tanggal DESC limit 1');
	$sql->execute([
		'NotaA2',
		'Aktif']);
	$data=$sql->fetch();
	$noNota ='JL'.$idCabang.'-'.date("Ym").'-'.str_pad($data['nomorUrut'], 5, '0', STR_PAD_LEFT);
	return $noNota;
}

function updateNoNotaA2($db)
{
	$sql=$db->prepare('UPDATE balistars_nomor SET 
		nomorUrut=nomorUrut+1, 
		tanggal = ? 
		where jenis = ? 
		and status= ?
		order by tanggal LIMIT 1 ');
	$status = $sql->execute([
		date('Y-m-d'), 
		'NotaA2',
		'Aktif']);
	
}

function updateNoNotaBaruA2($db)
{
	// $sql=$db->prepare('UPDATE balistars_nomor SET 
	// 	status =? 
	// 	where jenis = ? 
	// 	order by tanggal LIMIT 1 ');
	// $sql->execute([
	// 	'Non Aktif', 
	// 	'NotaA2']);

	$sql=$db->prepare('UPDATE balistars_nomor SET 
		nomorUrut=1,
		tanggal =?
		where jenis = ?');
	$sql->execute([
		date('Y-m-d'), 
		'NotaA2']);
}
?>
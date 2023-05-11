<?php  
function generateNoNotaA1($db,$idCabang)
{
	$sql=$db->prepare('SELECT * from balistars_nomor where 
		jenis = ? 
		order by tanggal DESC limit 1');
	$sql->execute(['BiayaA1']);
	$data=$sql->fetch();
	$noNota ='KZ'.$idCabang.'-'.date("Ym").'-'.str_pad($data['nomorUrut'], 5, '0', STR_PAD_LEFT);
	return $noNota;
}

function updateNoNotaA1($db)
{
	$digit = 5;
	$sql=$db->prepare('SELECT * from balistars_nomor where 
		jenis = ? 
		order by tanggal DESC limit 1');
	$sql->execute(['BiayaA1']);
	$data=$sql->fetch();
	if (10 ** ($digit) === intval($data['nomorUrut']) + 1) {
		$sql=$db->prepare('UPDATE balistars_nomor SET 
		nomorUrut= 1,
		tanggal= ?  
		where jenis = ? 
		order by tanggal LIMIT 1');
		$sql->execute([
			date('Y-m-d'),
			'BiayaA1']);
	} else {
		$sql=$db->prepare('UPDATE balistars_nomor SET 
		nomorUrut= nomorUrut+1,
		tanggal= ?  
		where jenis = ? 
		order by tanggal LIMIT 1');
		$sql->execute([
			date('Y-m-d'),
			'BiayaA1']);
	}
	
}

function updateNoNotaBaruA1($db)
{

	$sql=$db->prepare('UPDATE balistars_nomor SET 
		nomorUrut=1,
		tanggal =? 
		where jenis = ?');
	$sql->execute([
		date('Y-m-d'), 
		'BiayaA1']);
}

function generateNoNotaA2($db,$idCabang)
{
	$sql=$db->prepare('SELECT * from balistars_nomor where 
		jenis = ? 
		order by tanggal DESC limit 1');
	$sql->execute(['BiayaA2']);
	$data=$sql->fetch();
	$noNota ='KK'.$idCabang.'-'.date("Ym").'-'.str_pad($data['nomorUrut'], 5, '0', STR_PAD_LEFT);
	return $noNota;
}

function updateNoNotaA2($db)
{
	$digit = 5;
	$sql=$db->prepare('SELECT * from balistars_nomor where 
		jenis = ? 
		order by tanggal DESC limit 1');
	$sql->execute(['BiayaA2']);
	$data=$sql->fetch();
	if (10 ** ($digit) === intval($data['nomorUrut']) + 1) {
		$sql=$db->prepare('UPDATE balistars_nomor SET 
		nomorUrut= 1,
		tanggal= ?  
		where jenis = ? 
		order by tanggal LIMIT 1');
		$sql->execute([
			date('Y-m-d'),
			'BiayaA2']);
	} else {
		$sql=$db->prepare('UPDATE balistars_nomor SET 
		nomorUrut= nomorUrut+1,
		tanggal= ?  
		where jenis = ? 
		order by tanggal LIMIT 1');
		$sql->execute([
			date('Y-m-d'),
			'BiayaA2']);
	}
}

function updateNoNotaBaruA2($db)
{
	$sql=$db->prepare('UPDATE balistars_nomor SET 
		nomorUrut=1,
		tanggal =?
		where jenis = ?');
	$sql->execute([
		date('Y-m-d'), 
		'BiayaA2']);
}
?>
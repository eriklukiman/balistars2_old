<?php  
function generateNoNotaA1($db,$idCabang)
{
	$sql=$db->prepare('SELECT * from balistars_nomor where jenis =? order by tanggal DESC limit 1');
	$sql->execute(['NotaA1']);
	$data=$sql->fetch();
	$noNota ='PJ'.$idCabang.'-'.date("Ym").'-'.str_pad($data['nomorUrut'], 5, '0', STR_PAD_LEFT);
	return $noNota;
}

function updateNoNotaA1($db)
{
	$sql=$db->prepare('UPDATE balistars_nomor SET nomorUrut=nomorUrut+1 where jenis = ? order by tanggal LIMIT 1 ');
	$sql->execute(['NotaA1']);
}

function generateNoNotaA2($db,$idCabang)
{
	$sql=$db->prepare('SELECT * from balistars_nomor where jenis = ? order by tanggal DESC limit 1');
	$sql->execute(['NotaA2']);
	$data=$sql->fetch();
	$noNota ='JL'.$idCabang.'-'.date("Ym").'-'.str_pad($data['nomorUrut'], 5, '0', STR_PAD_LEFT);
	return $noNota;
}

function updateNoNotaA2($db)
{
	$sql=$db->prepare('UPDATE balistars_nomor SET nomorUrut= nomorUrut+1 where jenis = ? order by tanggal LIMIT 1 )');
	$sql->execute(['NotaA2']);
}
?>
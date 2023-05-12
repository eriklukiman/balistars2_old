<?php

function detailPrefixNomor(string $jenis)
{
    $prefix = [
        "NotaA1" => "PJ",
        "NotaA2" => "JL",
        "BeliA1" => "PB",
        "BeliA2" => "BL",
        "BiayaA1" => "KZ",
        "BiayaA2" => "KK",
        "PO" => "PO",
        "Penyusutan" => "PN",
        "Karyawan" => "Kar"
    ];

    return $prefix[$jenis];
}

function altvDBConn()
{
    return $GLOBALS['db'];

    $dbHost = "localhost";
    $dbUser = "bintangbali";
    $dbPassword = "b@l!b3Rsin4r";
    $dbName = "balistars";
    /*$dbUser = "u9819928_root";
    $dbPassword = "balistarsgroup0520";
    $dbName = "u9819928_balistarsdb";*/

    try {
        $db = new PDO("mysql:dbhost=$dbHost; dbname=$dbName", "$dbUser", "$dbPassword");
        return $db;
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}

function nomorNotaA1($idCabang)
{

    $db = altvDBConn();

    $sql = $db->prepare('SELECT * FROM balistars_nomor WHERE jenis = ? AND status = ?');
    $sql->execute(['NotaA1', 'Aktif']);

    $data = $sql->fetch();

    if ($data) {
        $prefix = detailPrefixNomor('NotaA1');
        $date = date('Ym');

        $no = str_pad($data['nomorUrut'], 5, '0', STR_PAD_LEFT);
        $format = "{$prefix}{$idCabang}-{$date}-{$no}";

        return $format;
    } else {
        return 'Nomor Urut Tidak Ditemukan';
    }
}

function updateNomorNotaA1()
{
    $db = altvDBConn();

    $sql = $db->prepare('UPDATE balistars_nomor SET nomorUrut = nomorUrut + 1 WHERE jenis = ? AND status = ?');
    $status = $sql->execute(['NotaA1', 'Aktif']);

    return $status;
}

function updateNomorNotaA1BulanBaru()
{
    $db = altvDBConn();

    $sql = $db->prepare('UPDATE balistars_nomor SET nomorUrut = 1 WHERE jenis = ? AND status = ?');
    $status = $sql->execute(['NotaA1', 'Aktif']);

    return $status;
}

function nomorNotaA2($idCabang)
{

    $db = altvDBConn();

    $sql = $db->prepare('SELECT * FROM balistars_nomor WHERE jenis = ? AND status = ?');
    $sql->execute(['NotaA2', 'Aktif']);

    $data = $sql->fetch();

    if ($data) {
        $prefix = detailPrefixNomor('NotaA2');
        $date = date('Ym');

        $no = str_pad($data['nomorUrut'], 5, '0', STR_PAD_LEFT);
        $format = "{$prefix}{$idCabang}-{$date}-{$no}";

        return $format;
    } else {
        return 'Nomor Urut Tidak Ditemukan';
    }
}

function updateNomorNotaA2()
{
    $db = altvDBConn();

    $sql = $db->prepare('UPDATE balistars_nomor SET nomorUrut = nomorUrut + 1 WHERE jenis = ? AND status = ?');
    $status = $sql->execute(['NotaA2', 'Aktif']);

    return $status;
}

function updateNomorNotaA2BulanBaru()
{
    $db = altvDBConn();

    $sql = $db->prepare('UPDATE balistars_nomor SET nomorUrut = 1 WHERE jenis = ? AND status = ?');
    $status = $sql->execute(['NotaA2', 'Aktif']);

    return $status;
}

function nomorNotaPembelianA1($idCabang)
{

    $db = altvDBConn();

    $sql = $db->prepare('SELECT * FROM balistars_nomor WHERE jenis = ? AND status = ?');
    $sql->execute(['BeliA1', 'Aktif']);

    $data = $sql->fetch();

    if ($data) {
        $prefix = detailPrefixNomor('BeliA1');
        $date = date('Ym');

        $no = str_pad($data['nomorUrut'], 5, '0', STR_PAD_LEFT);
        $format = "{$prefix}{$idCabang}-{$date}-{$no}";

        return $format;
    } else {
        return 'Nomor Urut Tidak Ditemukan';
    }
}

function updateNomorNotaPembelianA1()
{
    $db = altvDBConn();

    $sql = $db->prepare('UPDATE balistars_nomor SET nomorUrut = nomorUrut + 1 WHERE jenis = ? AND status = ?');
    $status = $sql->execute(['BeliA1', 'Aktif']);

    return $status;
}

function updateNomorNotaPembelianA1BulanBaru()
{
    $db = altvDBConn();

    $sql = $db->prepare('UPDATE balistars_nomor SET nomorUrut = 1 WHERE jenis = ? AND status = ?');
    $status = $sql->execute(['BeliA1', 'Aktif']);

    return $status;
}

function nomorNotaPembelianA2($idCabang)
{

    $db = altvDBConn();

    $sql = $db->prepare('SELECT * FROM balistars_nomor WHERE jenis = ? AND status = ?');
    $sql->execute(['BeliA2', 'Aktif']);

    $data = $sql->fetch();

    if ($data) {
        $prefix = detailPrefixNomor('BeliA2');
        $date = date('Ym');

        $no = str_pad($data['nomorUrut'], 5, '0', STR_PAD_LEFT);
        $format = "{$prefix}{$idCabang}-{$date}-{$no}";

        return $format;
    } else {
        return 'Nomor Urut Tidak Ditemukan';
    }
}

function updateNomorNotaPembelianA2()
{
    $db = altvDBConn();

    $sql = $db->prepare('UPDATE balistars_nomor SET nomorUrut = nomorUrut + 1 WHERE jenis = ? AND status = ?');
    $status = $sql->execute(['BeliA2', 'Aktif']);

    return $status;
}

function updateNomorNotaPembelianA2BulanBaru()
{
    $db = altvDBConn();

    $sql = $db->prepare('UPDATE balistars_nomor SET nomorUrut = 1 WHERE jenis = ? AND status = ?');
    $status = $sql->execute(['BeliA2', 'Aktif']);

    return $status;
}

function nomorNotaBiayaA1($idCabang)
{

    $db = altvDBConn();

    $sql = $db->prepare('SELECT * FROM balistars_nomor WHERE jenis = ? AND status = ?');
    $sql->execute(['BiayaA1', 'Aktif']);

    $data = $sql->fetch();

    if ($data) {
        $prefix = detailPrefixNomor('BiayaA1');
        $date = date('Ym');

        $no = str_pad($data['nomorUrut'], 5, '0', STR_PAD_LEFT);
        $format = "{$prefix}{$idCabang}-{$date}-{$no}";

        return $format;
    } else {
        return 'Nomor Urut Tidak Ditemukan';
    }
}

function updateNomorNotaBiayaA1()
{
    $db = altvDBConn();

    $sql = $db->prepare('UPDATE balistars_nomor SET nomorUrut = nomorUrut + 1 WHERE jenis = ? AND status = ?');
    $status = $sql->execute(['BiayaA1', 'Aktif']);

    return $status;
}

function updateNomorNotaBiayaA1BulanBaru()
{
    $db = altvDBConn();

    $sql = $db->prepare('UPDATE balistars_nomor SET nomorUrut = 1 WHERE jenis = ? AND status = ?');
    $status = $sql->execute(['BiayaA1', 'Aktif']);

    return $status;
}

function nomorNotaBiayaA2($idCabang)
{

    $db = altvDBConn();

    $sql = $db->prepare('SELECT * FROM balistars_nomor WHERE jenis = ? AND status = ?');
    $sql->execute(['BiayaA2', 'Aktif']);

    $data = $sql->fetch();

    if ($data) {
        $prefix = detailPrefixNomor('BiayaA2');
        $date = date('Ym');

        $no = str_pad($data['nomorUrut'], 5, '0', STR_PAD_LEFT);
        $format = "{$prefix}{$idCabang}-{$date}-{$no}";

        return $format;
    } else {
        return 'Nomor Urut Tidak Ditemukan';
    }
}

function updateNomorNotaBiayaA2()
{
    $db = altvDBConn();

    $sql = $db->prepare('UPDATE balistars_nomor SET nomorUrut = nomorUrut + 1 WHERE jenis = ? AND status = ?');
    $status = $sql->execute(['BiayaA2', 'Aktif']);

    return $status;
}

function updateNomorNotaBiayaA2BulanBaru()
{
    $db = altvDBConn();

    $sql = $db->prepare('UPDATE balistars_nomor SET nomorUrut = 1 WHERE jenis = ? AND status = ?');
    $status = $sql->execute(['BiayaA2', 'Aktif']);

    return $status;
}

function nomorNotaPenyusutan($idCabang)
{

    $db = altvDBConn();

    $sql = $db->prepare('SELECT * FROM balistars_nomor WHERE jenis = ? AND status = ?');
    $sql->execute(['Penyusutan', 'Aktif']);

    $data = $sql->fetch();

    if ($data) {
        $prefix = detailPrefixNomor('Penyusutan');
        $date = date('Ym');

        $no = str_pad($data['nomorUrut'], 5, '0', STR_PAD_LEFT);
        $format = "{$prefix}{$idCabang}-{$date}-{$no}";

        return $format;
    } else {
        return 'Nomor Urut Tidak Ditemukan';
    }
}

function updateNomorNotaPenyusutan()
{
    $db = altvDBConn();

    $sql = $db->prepare('UPDATE balistars_nomor SET nomorUrut = nomorUrut + 1 WHERE jenis = ? AND status = ?');
    $status = $sql->execute(['Penyusutan', 'Aktif']);

    return $status;
}

function nomorPO($idCabang)
{

    $db = altvDBConn();

    $sql = $db->prepare('SELECT * FROM balistars_nomor WHERE jenis = ? AND status = ?');
    $sql->execute(['PO', 'Aktif']);

    $data = $sql->fetch();

    if ($data) {
        $prefix = detailPrefixNomor('PO');
        $date = date('Ym');

        $no = str_pad($data['nomorUrut'], 5, '0', STR_PAD_LEFT);
        $format = "{$prefix}{$idCabang}-{$date}-{$no}";

        return $format;
    } else {
        return 'Nomor Urut Tidak Ditemukan';
    }
}

function updateNomorPO()
{
    $db = altvDBConn();

    $sql = $db->prepare('UPDATE balistars_nomor SET nomorUrut = nomorUrut + 1 WHERE jenis = ? AND status = ?');
    $status = $sql->execute(['PO', 'Aktif']);

    return $status;
}

function nomorIndukKaryawan()
{

    $db = altvDBConn();

    $sql = $db->prepare('SELECT * FROM balistars_nomor WHERE jenis = ? AND status = ?');
    $sql->execute(['Karyawan', 'Aktif']);

    $data = $sql->fetch();

    if ($data) {
        $prefix = detailPrefixNomor('Karyawan');

        $no = str_pad($data['nomorUrut'], 5, '0', STR_PAD_LEFT);
        $format = "{$prefix}-{$no}";

        return $format;
    } else {
        return 'Nomor Urut Tidak Ditemukan';
    }
}

function updateNomorIndukKaryawan()
{
    $db = altvDBConn();

    $sql = $db->prepare('UPDATE balistars_nomor SET nomorUrut = nomorUrut + 1 WHERE jenis = ? AND status = ?');
    $status = $sql->execute(['Karyawan', 'Aktif']);

    return $status;
}

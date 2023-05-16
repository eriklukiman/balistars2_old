<?php

function tanggalTerbilang($date)
{
    $dateEng = date('j F Y', strtotime($date));
    return $dateEng;
}

function tanggalTerbilangSingkat($date)
{
    $dateEng = date('j M Y', strtotime($date));
    return $dateEng;
}

function selisihTanggal($startDate, $endDate)
{
    $start_date = new DateTime($startDate);
    $end_date   = new DateTime($endDate);
    $interval   = $start_date->diff($end_date);
    $selisih    = $interval->days;
    return $selisih;
}

function namaHari($tahun, $bulan, $hari)
{
    $tanggal  = $tahun . "-" . $bulan . "-" . $hari;
    $namaHari = date('D', strtotime($tanggal));
    return $namaHari;
}

function namaBulan($bulan)
{
    switch ($bulan) {
        case "01":
            $bulan = "Januari";
            break;
        case "02":
            $bulan = "Februari";
            break;
        case "03":
            $bulan = "Maret";
            break;
        case "04":
            $bulan = "April";
            break;
        case "05":
            $bulan = "Mei";
            break;
        case "06":
            $bulan = "Juni";
            break;
        case "07":
            $bulan = "Juli";
            break;
        case "08":
            $bulan = "Agustus";
            break;
        case "09":
            $bulan = "September";
            break;
        case "10":
            $bulan = "Oktober";
            break;
        case "11":
            $bulan = "November";
            break;
        case "12":
            $bulan = "Desember";
            break;
    }
    return $bulan;
}

function waktuBesok($waktuSekarang)
{
    $waktuKemarin = date('Y-m-d', strtotime('+1 days', strtotime($waktuSekarang)));
    return $waktuKemarin;
}

function waktuKemarin($waktuSekarang)
{
    $waktuKemarin = date('Y-m-d', strtotime('-1 days', strtotime($waktuSekarang)));
    return $waktuKemarin;
}

function cekTahun($tanggal1, $tanggal2)
{
    $cekTahun1     = new DateTime($tanggal1);
    $tahun1        = $cekTahun1->format('Y');

    $cekTahun2     = new DateTime($tanggal2);
    $tahun2        = $cekTahun2->format('Y');

    $status = 'sama';
    if ($tahun1 != $tahun2) {
        $status = 'beda';
    }
    return $status;
}

function tampilTahun($tanggal)
{
    $cekTahun = new DateTime($tanggal);
    $tahun   = $cekTahun->format('Y');

    return $tahun;
}

function cekPeriodeTahun($tanggal)
{
    $cekTahun     = new DateTime($tanggal);
    $tahun        = $cekTahun->format('Y');
    $tanggalAwal  = $tahun . '-01-01';
    $tanggalAkhir = $tahun . '-12-31';

    $periodeTahun = array('tanggalAwal' => $tanggalAwal, 'tanggalAkhir' => $tanggalAkhir);
    return $periodeTahun;
}

function tampilBulanTahun($tanggal)
{
    $tanggalBaru  = strtotime($tanggal);
    $bulan = date('M', $tanggalBaru);
    $tahun = date('Y', $tanggalBaru);
    $periode = $bulan . ' ' . $tahun;
    return $periode;
}

function umur($tanggalLahir)
{
    $tglSekarang = date("Y-m-d");
    $lahir       = date_create($tanggalLahir);
    $sekarang    = date_create($tglSekarang);
    $jumlahHari  = date_diff($lahir, $sekarang);
    $tahun       = $jumlahHari->format("%y");
    $bulan       = $jumlahHari->format("%m");
    $hari        = $jumlahHari->format("%d");

    $arrayUmur   = array('umur' => $tahun, 'bulan' => $bulan, 'hari' => $hari);
    return $arrayUmur;
}

function ubahTanggalIndo($tanggal)
{
    if (isDate($tanggal)) {
        $tgl = explode("-", $tanggal);
        switch ($tgl[1]) {
            case "01":
                $bulan = "Januari";
                break;
            case "02":
                $bulan = "Februari";
                break;
            case "03":
                $bulan = "Maret";
                break;
            case "04":
                $bulan = "April";
                break;
            case "05":
                $bulan = "Mei";
                break;
            case "06":
                $bulan = "Juni";
                break;
            case "07":
                $bulan = "Juli";
                break;
            case "08":
                $bulan = "Agustus";
                break;
            case "09":
                $bulan = "September";
                break;
            case "10":
                $bulan = "Oktober";
                break;
            case "11":
                $bulan = "November";
                break;
            case "12":
                $bulan = "Desember";
                break;
        }

        $tgl1 = $tgl[2];
        if (substr($tgl[2], 0, 1) == "0") {
            $tgl1 = substr($tgl[2], 1, 1);
        }

        $tglhasil = "$tgl1 $bulan $tgl[0]";
        return $tglhasil;
    } else {
        return '-';
    }
}

function isDate($value)
{
    if (!$value) {
        return false;
    } else {
        $date = date_parse($value);
        if ($date['error_count'] == 0 && $date['warning_count'] == 0) {
            return true;
        } else {
            return false;
        }
    }
}

function konversiTanggal($tanggal)
{
    return join('-', array_reverse(explode('-', $tanggal)));
}

function bulanBesok($waktuSekarang)
{
    $waktuKemarin = date('Y-m-d', strtotime('+1 month', strtotime($waktuSekarang)));
    return $waktuKemarin;
}

function bulanKemarin($waktuSekarang)
{
    $waktuKemarin = date('Y-m-d', strtotime('-1 month', strtotime($waktuSekarang)));
    return $waktuKemarin;
}

function pilihBulan($bulan)
{
    $namaBulan = array("", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");
    for ($i = 1; $i < 13; $i++) {
        if ($i < 10) {
            $bln = "0" . $i;
        } else {
            $bln = $i;
        }
        $selected = selected($bln, $bulan);
?>
        <option value="<?= $bln ?>" <?= $selected ?>><?= $namaBulan[$i] ?></option>
    <?php
    }
}

function pilihTahun($tahun)
{
    $tahunSekarang = date("Y");
    for ($i = $tahunSekarang; $i >= $tahunSekarang - 5; $i--) {
        $selected = selected($i, $tahun);
    ?>
        <option value="<?= $i ?>" <?= $selected ?>><?= $i ?></option>
<?php
    }
}

function dateDiffInTime(DateInterval $diff)
{
    $hours = str_pad(strval(($diff->days * 24) + $diff->h), 2, '0', STR_PAD_LEFT);
    $minutes = str_pad(strval($diff->i), 2, '0', STR_PAD_LEFT);
    $seconds = str_pad(strval($diff->s), 2, '0', STR_PAD_LEFT);

    return "{$hours}:{$minutes}:{$seconds}";
}

function timeInSeconds(string $time)
{
    if (isValidTimeFormat($time)) {
        [$hours, $minutes, $seconds] = explode(':', $time);
        return (intval($hours) * 3600) + (intval($minutes) * 60) + intval($seconds);
    } else {
        return false;
    }
}

function secondsToTime(int $timeInSeconds)
{
    $hours = str_pad(strval(floor($timeInSeconds / 3600)), 2, '0', STR_PAD_LEFT);
    $minutes = str_pad(strval(floor(($timeInSeconds % 3600) / 60)), 2, '0', STR_PAD_LEFT);
    $seconds = str_pad(strval(($timeInSeconds % 3600) % 60), 2, '0', STR_PAD_LEFT);

    return "{$hours}:{$minutes}:{$seconds}";
}

function timeInMinutes(string $time)
{
    if (isValidTimeFormat($time)) {
        [$hours, $minutes, $seconds] = explode(':', $time);
        return (intval($hours) * 60) + intval($minutes);
    } else {
        return false;
    }
}

function minutesToTime(int $timeInMinutes)
{
    $hours = str_pad(strval(floor($timeInMinutes / 60)), 2, '0', STR_PAD_LEFT);
    $minutes = str_pad(strval(floor($timeInMinutes % 60)), 2, '0', STR_PAD_LEFT);

    return "{$hours}:{$minutes}:00";
}

function isValidTimeFormat(string $time)
{
    if (preg_match('/\d{2,}\:\d{2}\:\d{2}/', $time)) {
        return true;
    } else {
        return false;
    }
}

function averageTime(array $listOfTime)
{
    $average = 0;

    foreach ($listOfTime as $index => $time) {
        if (is_int($time)) {
            $timeInSeconds = $time;
        } else if (is_string($time) && isValidTimeFormat($time)) {
            $timeInSeconds = timeInSeconds($time);
        } else {
            continue;
        }

        if ($index === 0) {
            $average = $timeInSeconds;
        } else {
            $average = ($average + $timeInSeconds) / 2;
        }
    }

    return secondsToTime($average);
}

function getTimestamp(string $type, string $date = 'now')
{
    $listDomain = ['legugendong.com'];

    $SERV_NAME = $_SERVER['SERVER_NAME'];
    $REQ_SCHEME = $_SERVER['REQUEST_SCHEME'];

    if ($type === 'LOCALE') {
        $dateTime = new DateTime($date, new DateTimeZone('Asia/Kuala_Lumpur'));
    } else if ($type === 'DATABASE') {
        if (in_array($SERV_NAME, $listDomain) && $REQ_SCHEME === 'https') {
            $dateTime = new DateTime($date, new DateTimeZone('Asia/Jakarta'));
        } else {
            $dateTime = new DateTime($date, new DateTimeZone('Asia/Kuala_Lumpur'));
        }
    } else {
        return '-';
    }


    return $dateTime;
}

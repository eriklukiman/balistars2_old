<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once $BASE_URL_PHP . '/library/konfigurasiurl.php';
// Memanggil Fungsi Exception  di folder PHP Mailer
require_once $BASE_URL_PHP . '/assets/vendor/PHPMailer/src/Exception.php';

// Memanggil File Utama PHPMailer di folder PHP Mailer
require_once $BASE_URL_PHP . '/assets/vendor/PHPMailer/src/PHPMailer.php';

// Memanggil Fungsi SMTP di folder PHP Mailer
require_once $BASE_URL_PHP . '/assets/vendor/PHPMailer/src/SMTP.php';


function sendEmailNotificationPengajuan(
    \PDO $db,
    string $CSRFToken,
    string $emailPenerima,
    string $namaPenerima,
    string $tglPengajuan,
    string $tahapan,
    string $namaPembuat,
    string $jenis
) {

    if (hash_equals($_SESSION['tokenCSRF'], $CSRFToken)) {

        $config = [
            'Additional' => [
                'tabel' => 'balistars_pengajuan_additional',
                'id' => 'idAdditional',
                'status' => 'statusAdditional',
                'listKelanjutan' => [
                    "Disetujui" => [
                        "Kontrol Area" => "Pak Swi",
                        "Pak Swi" => "Headoffice",
                        "Headoffice" => "Payment",
                    ],
                    "Reject" => [
                        "Kontrol Area" => "Reject",
                        "Pak Swi" => "Reject",
                        "Headoffice" => "Kontrol Area",
                    ]
                ]
            ],
            'Partisi' => [
                'tabel' => 'balistars_pengajuan_partisi',
                'id' => 'idPartisi',
                'status' => 'statusPartisi',
                'listKelanjutan' => [
                    "Disetujui" => [
                        "Kontrol Area" => "Pak Swi",
                        "Pak Swi" => "Headoffice",
                        "Headoffice" => "Payment",
                    ],
                    "Reject" => [
                        "Kontrol Area" => "Reject",
                        "Pak Swi" => "Reject",
                        "Headoffice" => "Kontrol Area",
                    ]
                ]
            ],
            'Pengembalian' => [
                'tabel' => 'balistars_pengajuan_pengembalian',
                'id' => 'idPengembalian',
                'status' => 'statusPengembalian',
                'listKelanjutan' => [
                    "Disetujui" => [
                        "Kontrol Area" => "Pak Swi",
                        "Pak Swi" => "Headoffice",
                        "Headoffice" => "Payment",
                    ],
                    "Reject" => [
                        "Kontrol Area" => "Reject",
                        "Pak Swi" => "Reject",
                        "Headoffice" => "Kontrol Area",
                    ]
                ]
            ],
            'Petty Cash' => [
                'tabel' => 'balistars_pengajuan_petty_cash',
                'id' => 'idPettyCash',
                'status' => 'statusPettyCash',
                'listKelanjutan' => [
                    "Disetujui" => [
                        "Headoffice" => "Payment",
                    ],
                    "Reject" => [
                        "Headoffice" => "Reject",
                    ]
                ]
            ],
        ];

        if (isset($config[$jenis])) {

            [
                'id' => $colID,
                'status' => $colStatus,
                'tabel' => $tabel
            ] = $config[$jenis];

            // SET NAMA PENGIRIM 
            $namaPengirim   = 'BSP System';

            // SET USERNAME PENGIRIM
            $emailPengirim  = 'noreply.bspsystem@gmail.com';

            // SET PASSWORD PENGIRIM
            $password       = 'ymzihbmbiwtjmtmy';

            //Instansiasi Class PHPMailer ke variabel object mail
            $mail = new PHPMailer();
            $mail->isSMTP();

            // SET DATA PHPMAILER
            $mail->Host         = 'smtp.gmail.com';
            $mail->Username     = $emailPengirim;
            $mail->Password     = $password;
            $mail->Port         = 587;
            $mail->SMTPAuth     = true;
            $mail->SMTPSecure   = 'tls';

            // SET DETAIL EMAIL
            $mail->setFrom($emailPengirim, $namaPengirim);
            $mail->addAddress($emailPenerima);
            $mail->isHTML(true);

            $mail->Subject = 'Notifikasi Permohonan Penyetujuan (Pengajuan ' . $jenis . ')';
            $mail->Body = '
        <div id="wrapper" style="font-family:Arial, Helvetica, sans-serif">
            <div style="width:100%; text-align:center">
                <h3>
                    NOTIFIKASI PERMOHONAN PENYETUJUAN
                </h3>
                <span style="display:block; text-transform:uppercase;">( PENGAJUAN ' . $jenis . ' )</span>
            </div>
            <div style="margin-top: 20px;">
                <p>Kepada Bapak / Ibu "' . $namaPenerima . '" selaku ' . $tahapan . ', dimohonkan untuk segera memproses Pengajuan Additional yang dibuat oleh "' . $namaPembuat . '" pada tanggal ' . ubahTanggalIndo($tglPengajuan) . '</p>
            </div>
            <div style="margin-top: 60px;">
                <p>Terima kasih atas perhatiannya, <strong>BSP System</strong></p>
                <p style="opacity: .7; font-size:12px"><i>Email ini adalah email yang di generate otomatis oleh system. Jadi email ini tidak perlu untuk di reply.</i></p>
            </div>
        </div>
        ';

            $status = $mail->send();

            if (!$status) {
                $pesan = 'Email tidak berhasil Terkirim';
            } else {
                $pesan = 'Email berhasil terkirim';
            }

            $response = [
                'status' => $status,
                'pesan' => $pesan
            ];
        } else {
            $response = [
                'status' => false,
                'pesan' => 'Jenis Pengajuan Tidak Valid'
            ];
        }
    } else {
        $response = [
            'status' => false,
            'pesan' => 'Token Tidak Valid'
        ];
    }

    return $response;
}

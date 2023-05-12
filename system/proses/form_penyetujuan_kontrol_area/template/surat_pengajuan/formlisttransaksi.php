<?php
include_once '../../../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
include_once $BASE_URL_PHP . '/library/fungsistatement.php';
include_once $BASE_URL_PHP . '/library/fungsitanggal.php';
include_once $BASE_URL_PHP . '/system/fungsinavigasi.php';

session_start();

$idUser    = '';
$tokenCSRF = '';

extract($_SESSION);

//DESKRIPSI ID USER
$idUserAsli = dekripsi($idUser, $kunciRahasia);

//MENGECEK APAKAH ID USER YANG LOGIN ADA PADA DATABASE
$sqlCekUser = $db->prepare('SELECT idUser from balistars_user where idUser=?');
$sqlCekUser->execute([$idUserAsli]);
$dataCekUser = $sqlCekUser->fetch();

//MENGECEK DATA LOGIN PEGAWAI
$sqlLogin  = $db->prepare('SELECT * FROM balistars_pegawai inner join balistars_user on balistars_pegawai.idPegawai=balistars_user.idPegawai inner join balistars_cabang on balistars_pegawai.idCabang=balistars_cabang.idCabang where balistars_user.idUser = ?');
$sqlLogin->execute([$idUserAsli]);
$dataLogin = $sqlLogin->fetch();

//MENGECEK APAKAH USER INI BERHAK MENGAKSES MENU INI
$sqlCekMenu = $db->prepare('SELECT * from balistars_user_detail 
  inner join balistars_menu_sub 
  on balistars_menu_sub.idMenuSub = balistars_user_detail.idMenuSub
  where balistars_user_detail.idUser = ?
  and namaFolder = ?');
$sqlCekMenu->execute([
    $idUserAsli,
    'form_penyetujuan_kontrol_area'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
    header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
} else {

    $sqlInformasi    = $db->query('SELECT * FROM balistars_information');
    $dataInformasi = $sqlInformasi->fetch();
    $logo            = $BASE_URL_HTML . '/assets/images/' . $dataInformasi['logo'];

    $idPengajuan = '';
    $isPreview = '';

    $id = '';

    extract($_POST);

    $isPreview = $isPreview === 'true' ? true : false;

?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th style="width:33%" class="text-center">CUSTOMER</th>
                <th style="width:33%" class="text-center">BANK REKENING</th>
                <th style="width:33%" class="text-center">a.n. REKENING</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $listTransaksi = selectStatement(
                $db,
                'SELECT
                    data_row.row,
                    data_customer.idDataSuratPengajuan as idCustomer,
                    data_customer.data as dataCustomer,
                    data_bank_rekening.idDataSuratPengajuan as idBankRekening,
                    data_bank_rekening.data as dataBankRekening,
                    data_atas_nama.idDataSuratPengajuan as idAtasNamaRekening,
                    data_atas_nama.data as dataAtasNama
                FROM
                (
                    SELECT 
                        `row` 
                    FROM 
                        balistars_data_surat_pengajuan 
                    WHERE 
                        idPengajuan = ?
                        AND kolom IN (?,?,?)
                        GROUP BY `row`
                ) data_row 
                LEFT JOIN (
                    SELECT
                        idDataSuratPengajuan,
                        `row`,
                        `data`
                    FROM
                        balistars_data_surat_pengajuan
                    WHERE
                        idPengajuan = ?
                        AND kolom = ?
                ) data_customer ON data_row.row = data_customer.row
                LEFT JOIN (
                    SELECT
                        idDataSuratPengajuan,
                        `row`,
                        `data`
                    FROM
                        balistars_data_surat_pengajuan
                    WHERE
                        idPengajuan = ?
                        AND kolom = ?
                ) data_bank_rekening ON data_row.row = data_bank_rekening.row
                LEFT JOIN (
                    SELECT
                        idDataSuratPengajuan,
                        `row`,
                        `data`
                    FROM
                        balistars_data_surat_pengajuan
                    WHERE
                        idPengajuan = ?
                        AND kolom = ?
                ) data_atas_nama ON data_row.row = data_atas_nama.row
                ORDER BY data_row.row
                ',
                [
                    $idPengajuan, 'listTransaksi@Customer', 'listTransaksi@BankRekening', 'listTransaksi@AtasNamaRekening',
                    $idPengajuan, 'listTransaksi@Customer',
                    $idPengajuan, 'listTransaksi@BankRekening',
                    $idPengajuan, 'listTransaksi@AtasNamaRekening',
                ]
            );

            $rowTerakhir = selectStatement(
                $db,
                'SELECT COALESCE(MAX(`row`), 0) + 1 as rowTerakhir FROM balistars_data_surat_pengajuan WHERE idPengajuan = ? AND kolom IN (?,?,?)',
                [$idPengajuan, 'listTransaksi@Customer', 'listTransaksi@BankRekening', 'listTransaksi@AtasNamaRekening'],
                'fetch'
            )['rowTerakhir'];


            foreach ($listTransaksi as $index => $list) {
            ?>
                <tr>
                    <td class="text-center">
                        <?php
                        if (is_null($list['idCustomer']) && $isPreview) {
                        ?>
                            <input type="text" class="form-control" data-row="<?= $list['row'] ?>" data-id="<?= $list['idCustomer'] ?>" data-col="listTransaksi@Customer" id="listTransaksi@Customer#<?= $list['row'] ?>" placeholder="Tekan ' Enter ' untuk menyimpan data...">
                        <?php
                        } else if ($id === $list['idCustomer'] && $isPreview) {
                        ?>
                            <input type="text" class="form-control" data-row="<?= $list['row'] ?>" data-id="<?= $list['idCustomer'] ?>" data-col="listTransaksi@Customer" id="listTransaksi@Customer#<?= $list['row'] ?>" value="<?= $list['dataCustomer'] ?>" placeholder="Tekan ' Enter ' untuk menyimpan data...">
                        <?php
                        } else {
                        ?>
                            <span><?= $list['dataCustomer'] ?? '<i style="opacity:.7;">( Data Belum Terisi )</i>'; ?></span>
                            <?php
                            if ($isPreview) {
                            ?>
                                <span class="btn-proses badge badge-danger" onclick="deleteList($(this),'listTransaksi@Customer','<?= $idPengajuan ?>', '<?= $list['idCustomer'] ?>')"><i class="fas fa-trash"></i></span>
                                <span class="btn-proses badge badge-warning" onclick="getFormListTransaksi('<?= $idPengajuan ?>', '<?= $list['idCustomer'] ?>')"><i class="fas fa-edit"></i></span>
                        <?php
                            }
                        }
                        ?>
                    </td>
                    <td class="text-center">
                        <?php
                        if (is_null($list['idBankRekening']) && $isPreview) {
                        ?>
                            <input type="text" class="form-control" data-row="<?= $list['row'] ?>" data-id="<?= $list['idBankRekening'] ?>" data-col="listTransaksi@BankRekening" id="listTransaksi@BankRekening#<?= $list['row'] ?>" placeholder="Tekan ' Enter ' untuk menyimpan data...">
                        <?php
                        } else if ($id === $list['idBankRekening'] && $isPreview) {
                        ?>
                            <input type="text" class="form-control" data-row="<?= $list['row'] ?>" data-id="<?= $list['idBankRekening'] ?>" data-col="listTransaksi@BankRekening" id="listTransaksi@BankRekening#<?= $list['row'] ?>" value="<?= $list['dataBankRekening'] ?>" placeholder="Tekan ' Enter ' untuk menyimpan data...">
                        <?php
                        } else {
                        ?>
                            <span><?= $list['dataBankRekening'] ?? '<i style="opacity:.7;">( Data Belum Terisi )</i>'; ?></span>
                            <?php
                            if ($isPreview) {
                            ?>
                                <span class="btn-proses badge badge-danger" onclick="deleteList($(this),'listTransaksi@BankRekening','<?= $idPengajuan ?>', '<?= $list['idBankRekening'] ?>')"><i class="fas fa-trash"></i></span>
                                <span class="btn-proses badge badge-warning" onclick="getFormListTransaksi('<?= $idPengajuan ?>', '<?= $list['idBankRekening'] ?>')"><i class="fas fa-edit"></i></span>
                        <?php
                            }
                        }
                        ?>
                    </td>
                    <td class="text-center">
                        <?php
                        if (is_null($list['idAtasNamaRekening']) && $isPreview) {
                        ?>
                            <input type="text" class="form-control" data-row="<?= $list['row'] ?>" data-id="<?= $list['idAtasNamaRekening'] ?>" data-col="listTransaksi@AtasNamaRekening" id="listTransaksi@AtasNamaRekening#<?= $list['row'] ?>" placeholder="Tekan ' Enter ' untuk menyimpan data...">
                        <?php
                        } else if ($id === $list['idAtasNamaRekening'] && $isPreview) {
                        ?>
                            <input type="text" class="form-control" data-row="<?= $list['row'] ?>" data-id="<?= $list['idAtasNamaRekening'] ?>" data-col="listTransaksi@AtasNamaRekening" id="listTransaksi@AtasNamaRekening#<?= $list['row'] ?>" value="<?= $list['dataAtasNamaRekening'] ?>" placeholder="Tekan ' Enter ' untuk menyimpan data...">
                        <?php
                        } else {
                        ?>
                            <span><?= $list['dataAtasNama'] ?? '<i style="opacity:.7;">( Data Belum Terisi )</i>'; ?></span>
                            <?php
                            if ($isPreview) {
                            ?>
                                <span class="btn-proses badge badge-danger" onclick="deleteList($(this),'listTransaksi@AtasNamaRekening','<?= $idPengajuan ?>', '<?= $list['idAtasNamaRekening'] ?>')"><i class="fas fa-trash"></i></span>
                                <span class="btn-proses badge badge-warning" onclick="getFormListTransaksi('<?= $idPengajuan ?>', '<?= $list['idAtasNamaRekening'] ?>')"><i class="fas fa-edit"></i></span>
                        <?php
                            }
                        }
                        ?>
                    </td>
                </tr>
            <?php
            }
            if ($isPreview) {
            ?>
                <tr>
                    <td>
                        <input type="text" class="form-control" data-row="<?= $rowTerakhir ?>" data-col="listTransaksi@Customer" id="listTransaksi@Customer#<?= $rowTerakhir ?>" placeholder="Tekan ' Enter ' untuk menyimpan data...">
                    </td>
                    <td>
                        <input type="text" class="form-control" data-row="<?= $rowTerakhir ?>" data-col="listTransaksi@BankRekening" id="listTransaksi@BankRekening#<?= $rowTerakhir ?>" placeholder="Tekan ' Enter ' untuk menyimpan data...">
                    </td>
                    <td>
                        <input type="text" class="form-control" data-row="<?= $rowTerakhir ?>" data-col="listTransaksi@AtasNamaRekening" id="listTransaksi@AtasNamaRekening#<?= $rowTerakhir ?>" placeholder="Tekan ' Enter ' untuk menyimpan data...">
                    </td>
                </tr>
                <?php
            } else {
                if (count($listTransaksi) === 0) {
                ?>
                    <tr>
                        <td colspan="3" class="text-center table-active">
                            <i class="fas fa-info-circle pr-4"></i><strong>TIDAK ADA DATA YANG DIINPUTKAN</strong>
                        </td>
                    </tr>
            <?php
                }
            }
            ?>
        </tbody>
    </table>
<?php
}
?>
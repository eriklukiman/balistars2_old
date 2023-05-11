<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
include_once $BASE_URL_PHP . '/library/fungsistatement.php';
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

//MENGECEK APAKAH USER INI BERHAK MENGAKSES MENU INI
$sqlCekMenu = $db->prepare('SELECT * from balistars_user_detail 
  inner join balistars_menu_sub 
  on balistars_menu_sub.idMenuSub = balistars_user_detail.idMenuSub
  where balistars_user_detail.idUser = ?
  and namaFolder = ?');
$sqlCekMenu->execute([
  $idUserAsli,
  'master_data_user'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
    header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
} else {

    extract($_REQUEST);

?>
    <div style="overflow-x: auto;">
        <input type="hidden" id="idPegawai" value="<?=$idPegawai?>">
        <input type="hidden" id="idUserAccount" value="<?=$idUserAccount?>">
        <table class="table table-hover">
            <tbody>
                <?php
                $i = 1;
                $sqlMenu=$db->prepare('SELECT *
                    FROM balistars_menu
                    WHERE statusMenu = ?
                    ORDER BY namaMenu');
                $sqlMenu->execute(['Aktif']);
                $dataMenu=$sqlMenu->fetchAll();

                foreach ($dataMenu as $row) {
                ?>
                    <tr class="table-active">
                        <th colspan="4"><i class="fas fa-<?= $row['icon'] ?> pr-4"></i><strong class="text-uppercase"><?= $row['namaMenu']; ?></strong></th>
                    </tr>
                    <?php

                    $opsi = [
                        'Edit' => 'EDIT',
                        'Delete' => 'DELETE',
                        'A2' => 'A2',
                    ];


                    $strSQLTipe = join('', array_map(function ($kode) {
                        return 'menu_terpilih.tipe' . $kode . ', ';
                    }, array_keys($opsi)));


                    $sqlMenuSub=$db->prepare(
                        'SELECT 
                            balistars_menu_sub.namaMenuSub, 
                            balistars_menu_sub.idMenuSub, 
                            menu_terpilih.idUserDetail, 
                            ' . $strSQLTipe . '
                            CASE
                                WHEN menu_terpilih.idUserDetail IS NULL THEN \'Tidak Terpilih\'
                                WHEN menu_terpilih.idUserDetail IS NOT NULL THEN \'Terpilih\'
                            END as statusTerpilih
                        FROM balistars_menu_sub
                        LEFT JOIN 
                            (
                                SELECT balistars_user_detail.*
                                FROM balistars_user_detail
                                WHERE balistars_user_detail.idUser = ?
                            ) as menu_terpilih ON balistars_menu_sub.idMenuSub = menu_terpilih.idMenuSub
                        WHERE 
                            balistars_menu_sub.idMenu = ?
                            AND balistars_menu_sub.statusMenuSub = ?
                            ORDER BY balistars_menu_sub.idMenuSub
                        ');
                    $sqlMenuSub->execute([$idUserAccount, $row['idMenu'], 'Aktif']);
                    $dataMenuSub=$sqlMenuSub->fetchAll();

                    $n = 1;
                    foreach ($dataMenuSub as $index => $data) {
                        if ($data['statusTerpilih'] === 'Terpilih') {
                            $checked = 'checked';
                            $flag = 'cancel';
                        } else {
                            $checked = '';
                            $flag = 'tambah';
                        }
                        
                    ?>
                        <tr data-id="<?= $data['idMenuSub'] ?>" class="row-input">
                            <td class="text-center"><?= $n ?></td>
                            <td class="align-middle align-center">
                                <input 
                                    type          = "checkbox" 
                                    name          = "menuSub_<?= $data['idMenuSub'] ?>" 
                                    id            = "menuSub_<?= $data['idMenuSub'] ?>" 
                                    value         = "<?= $data['idMenuSub'] ?>" 
                                    <?= $checked ?> 
                                    onchange      = "event.stopPropagation();prosesMenuUser('<?=$flag?>','<?= $row['idMenu'] ?>','<?= $data['idMenuSub'] ?>', '<?=$data['idUserDetail'] ?? ''?>')" 
                                    data-iterator = "<?= $i ?>"
                                >
                            </td>
                            <td class="text-left"><?= $data['namaMenuSub'] ?></td>
                            <td class="align-middle align-left" id="boxTipe">
                                <?php

                                if ($data['statusTerpilih'] === 'Terpilih') {

                                    foreach ($opsi as $key => $value) {

                                            $buttonClass = '';

                                            if ($data['tipe' . $key] === '1') {
                                                $buttonClass = 'btn btn-success';
                                            } else if ($data['tipe' . $key === '0']) {
                                                $buttonClass = 'btn btn-danger';
                                            }
                                ?>
                                            <button type="button" title="<?= $value ?>" class="<?= $buttonClass ?>" onclick="prosesTipe<?=$key?>('<?= $data['idUserDetail'] ?>')" tabindex="-1">
                                                <i><strong class="text-uppercase"><?= $value; ?></strong></i>
                                            </button>
                                <?php
                                        
                                    }
                                }
                                ?>
                            </td>
                        </tr>
                <?php
                        $n++;
                    }
                    $i++;
                }
                ?>
            </tbody>
        </table>
    </div>
<?php
}
?>
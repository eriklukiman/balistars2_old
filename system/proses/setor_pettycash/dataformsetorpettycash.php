<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
include_once $BASE_URL_PHP . '/library/fungsirupiah.php';
include_once $BASE_URL_PHP . '/library/fungsitanggal.php';
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
  'setor_pettycash'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

$idSetor = '';
$tanggalSetor=date('d-m-Y');
extract($_REQUEST);

$sqlLogin  = $db->prepare('SELECT * FROM balistars_pegawai inner join balistars_user on balistars_pegawai.idPegawai=balistars_user.idPegawai inner join balistars_cabang on balistars_pegawai.idCabang=balistars_cabang.idCabang where balistars_user.idUser = ?');
$sqlLogin->execute([$idUserAsli]);
$dataLogin = $sqlLogin->fetch();

$sqlUpdate  = $db->prepare('
  SELECT * FROM balistars_kas_kecil_setor 
  where idSetor=?
  and statusKasKecilSetor=?');
$sqlUpdate->execute([
  $idSetor,
  'Aktif']);
$dataUpdate = $sqlUpdate->fetch();

if($dataUpdate){
  $dataUpdate['jumlahSetor']=ubahToRp($dataUpdate['jumlahSetor']??'');
  $tanggalSetor=konversiTanggal($dataUpdate['tanggalSetor']??'');
}

?>
<form id="formSetorPettyCash">
  <input type="hidden" name="flag" id="flag" value="<?=$flag?>">
  <input type="hidden" name="idSetor"  value="<?=$dataUpdate['idSetor']?>">
  <input type="hidden" name="parameterOrder" value="<?=$parameterOrder?>">

  <div class="form-group row">
    <div class="col-sm-4">
      <label>Tanggal Setor <?=$idSetor?></label>
      <div class="input-group date"  data-date-autoclose="true" data-provide="datepicker"  data-date-format="dd-mm-yyyy">
        <input type="tanggal" class="form-control form-control-lg" name="tanggalSetor" id="tanggalSetor" value="<?=$tanggalSetor?>"  autocomplete="off">
        <div class="input-group-append">                                            
          <button class="btn btn-outline-secondary" type="button">
            <i class="fa fa-calendar"></i>
          </button>
        </div>
      </div>
    </div>
    <div class="col-sm-4">
      <label>Bank Tujuan</label>
      <select name="idBank" class="form-control select2" id="idBank" style="width: 100%;" required>
        <?php
        $sqlBank=$db->prepare('SELECT * FROM balistars_bank where statusBank=? order by namaBank');
        $sqlBank->execute(['Aktif']);
        $dataBank = $sqlBank->fetchAll();
        foreach($dataBank as $data){
          $selected=selected($data['idBank'],$dataUpdate['idBank']??'');
          ?>
          <option value="<?=$data['idBank']?>" <?=$selected?>><?=$data['namaBank']?></option>
          <?php
        }
        ?>
      </select>
    </div>
    <div class="col-sm-4">
      <label>Jumlah Setor</label>
      <input type="text" class="form-control form-control-lg" placeholder="Nilai Setor" name="jumlahSetor" id="jumlahSetor" onkeyup="ubahToRp('#jumlahSetor')" value="<?=$dataUpdate['jumlahSetor']?>">
    </div>
  </div> 
  <div class="form-group row">
    <div class="col-sm-12">
      <label>Keterangan</label>
      <textarea class="form-control" name="keterangan" placeholder="Keterangan" id="keterangan"><?=$dataUpdate['keterangan']??''?></textarea>
    </div>
  </div>
  <div class="form-group">
    <button type="button" class="btn btn-primary" onclick="prosesSetorPettyCash()">
      <i class="fa fa-save"></i> Save
    </button>
  </div>
</form>
<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
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
  'master_data_pegawai'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

$idPegawai = '';
$readonly      = '';
$tglMulaiKerja = date('d-m-Y');
extract($_REQUEST);

if($flag == 'update'){
  $readonly = 'disabled';
}

$sqlUpdate  = $db->prepare('SELECT * from balistars_pegawai
  where idPegawai = ?');
$sqlUpdate->execute([$idPegawai]);
$dataUpdate = $sqlUpdate->fetch();
if($dataUpdate){
  $tglMulaiKerja = konversiTanggal($dataUpdate['tglMulaiKerja']??'');
}

?>
<form id="formMasterDataPegawai">
  <input type="hidden" name="flag" value="<?=$flag?>">
  <input type="hidden" name="idPegawai"  value="<?=$idPegawai?>">
  <input type="hidden" name="parameterOrder" value="<?=$parameterOrder?>">


  <div class="form-group row">
    <div class="col-sm-6">
      <label>Nama Pegawai</label>
      <input type="text" class="form-control" name="namaPegawai" placeholder="Nama Pegawai" id="namaPegawai" value="<?=$dataUpdate['namaPegawai']??''?>">
      </select>
    </div>
    <div class="col-sm-6">
      <label>NIK</label>
      <input type="text" class="form-control" id="NIK" name="NIK" placeholder="NIK Pegawai" value="<?=$dataUpdate['NIK']??''?>">
    </div>
  </div>  
  <div class="form-group row">
    <div class="col-sm-6">
      <label>No telp</label>
      <input type="text" class="form-control" id="noTelpPegawai" name="noTelpPegawai" placeholder="No Telp Pegawai" value="<?=$dataUpdate['noTelpPegawai']??''?>">
    </div>
    <div class="col-sm-6">
      <label> Jabatan </label>
      <select name="idJabatan" class="form-control select2" style="width: 100%;" required>
        <option value=""> Pilih Jabatan </option>
        <?php
        $sqlJabatan=$db->prepare('SELECT * FROM balistars_jabatan where statusJabatan=? order by namaJabatan');
        $sqlJabatan->execute(['Aktif']);
        $dataJabatan = $sqlJabatan->fetchAll();
        foreach($dataJabatan as $data){
          $selected=selected($data['idJabatan'],$dataUpdate['idJabatan']??'');
          ?>
          <option value="<?=$data['idJabatan']?>" <?=$selected?>><?=$data['namaJabatan']?></option>
          <?php
        }
        ?>
      </select>
    </div>
  </div>
  <div class="form-group row">
    <div class="col-sm-6">
      <label>Cabang Unit Kerja</label>
      <select name="idCabang" class="form-control select2" style="width: 100%;" required>
        <option value=""> Pilih Cabang </option>
        <?php
        $sqlCabang=$db->prepare('SELECT * FROM balistars_cabang where statusCabang=? order by namaCabang');
        $sqlCabang->execute(['Aktif']);
        $dataCabang = $sqlCabang->fetchAll();
        foreach($dataCabang as $data){
          $selected=selected($data['idCabang'],$dataUpdate['idCabang']??'');
          ?>
          <option value="<?=$data['idCabang']?>" <?=$selected?>><?=$data['namaCabang']?></option>
          <?php
        }
        ?>
      </select>
    </div>
    <div class="col-sm-6">
      <label>Cabang Advertising(Bisa dikosongkan)</label>
      <select name="idCabangAdvertising" class="form-control select2" style="width: 100%;" >
        <option value="0"> Pilih Cabang </option>
        <?php
        $sqlCabang=$db->prepare('SELECT * FROM balistars_cabang_advertising where statusCabangAdvertising=? order by namaCabang');
        $sqlCabang->execute(['Aktif']);
        $dataCabang = $sqlCabang->fetchAll();
        foreach($dataCabang as $data){
          $selected=selected($data['idCabang'],$dataUpdate['idCabangAdvertising']??'');
          ?>
          <option value="<?=$data['idCabang']?>" <?=$selected?>><?=$data['namaCabang']?></option>
          <?php
        }
        ?>
      </select>
    </div>
  </div>
  <div class="form-group row">
    <div class="col-sm-6">
      <label>Tanggal Mulai Kerja</label>
      <div class="input-group date"  data-date-autoclose="true" data-provide="datepicker"  data-date-format="dd-mm-yyyy">
        <input type="tanggal" class="form-control" name="tglMulaiKerja" id="tglMulaiKerja" value="<?=$tglMulaiKerja?>"  autocomplete="off" >
        <div class="input-group-append">                                            
          <button class="btn btn-outline-secondary" type="button">
            <i class="fa fa-calendar"></i>
          </button>
        </div>
      </div>
    </div>
    <div class="col-sm-6">
      <label>Status Pegawai</label>
      <select name="statusPegawai" class="form-control select2" style="width: 100%;" required>
        <?php
        $arrayStatus=array('Aktif','DW','Cuti');
        $arrayValue=array('Aktif','DW','Cuti');
        for($i=0; $i<count($arrayStatus); $i++){
          $selected=selected($arrayValue[$i],$dataUpdate['statusPegawai']??'');
          ?>
          <option value="<?=$arrayValue[$i]?>" <?=$selected?>> <?=$arrayStatus[$i]?> </option>
          <?php
        }
        ?>
      </select>
    </div>
  </div>
  <div class="form-group row">
    <div class="col-sm-12">
      <label>Alamat Pegawai</label>
      <textarea class="form-control" name="alamatPegawai" placeholder="alamat Pegawai" id="alamatPegawai"><?=$dataUpdate['alamatPegawai']??''?></textarea>
    </div>
  </div>
  <div class="form-group">
    <button type="button" class="btn btn-primary" onclick="prosesMasterDataPegawai()">
      <i class="fa fa-save"></i> Save
    </button>
  </div>
</form>
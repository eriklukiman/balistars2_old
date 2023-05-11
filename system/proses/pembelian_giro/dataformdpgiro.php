<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
include_once $BASE_URL_PHP . '/system/fungsinavigasi.php';
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
  'pembelian_giro'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

$tanggalCairDp=date('d-m-Y');
  $idDpGiro='';
  $dp='';
  $flag='';
extract($_REQUEST);

$sqlUpdate  = $db->prepare('SELECT * from balistars_dpgiro
  where idDpGiro = ?');
  $sqlUpdate->execute([$idDpGiro]);
  $dataUpdate = $sqlUpdate->fetch();

  if($dataUpdate){
    $tanggalCairDp = konversiTanggal($dataUpdate['tanggalCairDp']??'');
    $dataUpdate['dp']=ubahToRp($dataUpdate['dp']);
    $flag = 'update';
  }

?>

<form id="formTambahDp">
  <input type="hidden" name="flag" value="<?=$flag?>">
  <input type="hidden" name="idSupplier"  value="<?=$idSupplier?>">
  <input type="hidden" name="disabled"  value="<?=$disabled?>">
  <input type="hidden" name="periode"  value="<?=$periode?>">
  <input type="hidden" name="tipePembelianDp"  value="<?=$tipePembelianDp?>">
  <input type="hidden" name="idDpGiro"  value="<?=$idDpGiro?>">
  <div class="form-group row">
    <div class="col-sm-6">
      <label>Supplier<?=$disabled?></label>
      <input type="text" name="namaSupplier" id="namaSupplier" class="form-control" value="<?=$namaSupplier?>" readonly>
    </div>
    <div class="col-sm-6">
      <label>Total Pembelian</label>
      <input type="text" name="total" id="total" class="form-control" value="<?=ubahToRp($total)?>" readonly>
    </div>
  </div>
  <div class="form-group row">
    <div class="col-sm-6">
      <label>Tanggal Cair</label>
      <div class="input-group date"  data-date-autoclose="true" data-provide="datepicker"  data-date-format="dd-mm-yyyy">
        <input type="tanggal" class="form-control" name="tanggalCairDp" id="tanggalCairDp" value="<?=$tanggalCairDp?>"  autocomplete="off" >
        <div class="input-group-append">                                            
          <button class="btn btn-outline-secondary" type="button">
            <i class="fa fa-calendar"></i>
          </button>
        </div>
      </div>
    </div>
    <div class="col-sm-6">
      <label>DP (Rp)</label>
      <input type="text" name="dp" id="dp" class="form-control" placeholder="0" onkeyup="ubahToRp('#dp')" value="<?=$dataUpdate['dp']??''?>" >
      <small id='notifValidasi'></small>
    </div>   
  </div>
  <div class="form-group row">
    <div class="col-sm-6">
      <label>No Giro </label>
      <input type="text" name="noGiroDp" id="noGiroDp" class="form-control" placeholder="Input No Giro" value="<?=$dataUpdate['noGiro']?>" required>
    </div>
    <div class="col-sm-6">
      <label>Bank Asal Transfer</label> <br>
      <select name="bankAsalDp" id="bankAsalDp" class="form-control select2" style="width : 100%" >
        <?php
        $sqlBank=$db->prepare('SELECT * FROM balistars_bank where statusBank=? order by namaBank');
        $sqlBank->execute(['Aktif']);
        $dataBank=$sqlBank->fetchAll();
        foreach($dataBank as $row){
          $selected=selected($row['idBank'],$dataUpdate['idBank']);
          ?>
          <option value="<?=$row['idBank']?>" <?=$selected?>> <?=$row['namaBank']?> </option>
          <?php
        }
        ?>
      </select>
    </div>   
  </div>
  <div class="form-group row">
    <div class="col-sm-6">
      <button type="button" class="btn btn-primary" onclick="prosesTambahDpGiro()" <?=$disabled?>>
        <i class="fa fa-save"></i> <br> Save
      </button>
    </div>
  </div>
</form>

<div class="row">
  <div class="col-sm-12">
    <div class="card card-success">
      <div class="card-header">
        <i class="fa fa-list" style="margin-right: 5px;"></i>
        Pembayaran DP
      </div>
      <div class="card-body" style="overflow-x: auto;">
        <table class="table">
          <thead>
            <th>N0</th>
            <th>DP (Rp)</th>
            <th>Tanggal Cair</th>
            <th>No Giro</th>
            <th>Bank Asal Transfer</th>
            <th>Aksi</th>
          </thead>
          <tbody>
            <?php
            $tanggal=explode('-', $periode);
            $tanggalAkhir=$tanggal[0].'-'.$tanggal[1].'-31';

            $sqlDp=$db->prepare('SELECT * FROM balistars_dpgiro 
              inner join balistars_bank 
              on balistars_dpgiro.idBank=balistars_bank.idBank
              where (periode between ? and ?) 
              and idSupplier=? 
              and tipePembelian=?
              and statusDpGiro=?
              and jenisGiro=?');
            $sqlDp->execute([
              $periode,$tanggalAkhir,
              $idSupplier,
              $tipePembelianDp,
              'Aktif',
              'DP']);
            $dataDp=$sqlDp->fetchAll();
            $n=1;
            foreach($dataDp as $data){
              ?>
              <tr>
                <td><?=$n?></td>
                <td><?=ubahToRp($data['dp'])?></td>
                <td><?=ubahTanggalIndo($data['tanggalCairDp'])?></td>
                <td><?=$data['noGiro']?></td>
                <td><?=$data['namaBank']?></td>
                <td>
                  <button type               = "button" 
                          title              = "Edit"
                          class              = "btn btn-warning tombolEdit" 
                          style              = "color: white;"
                          onclick = "tambahDpGiro('<?=$data['idSupplier']?>','<?=$namaSupplier?>','<?=$total?>','<?=$data['tipePembelian']?>','<?=$data['periode']?>','<?=$disabled?>','<?=$data['idDpGiro']?>')"  <?=$disabled?>>
                    <i class="fa fa-edit"></i>
                  </button>
                  <button type    = "button"
                          title   = "Hapus" 
                          class   = "btn btn-danger" 
                          onclick = "cancelDpGiro('<?=$data['idDpGiro']?>','<?=$data['idSupplier']?>','<?=$namaSupplier?>','<?=$total?>','<?=$data['tipePembelian']?>','<?=$data['periode']?>', '<?=$disabled?>')"  <?=$disabled?>>
                    <i class="fa fa-trash"></i>
                  </button>
                </td>
              </tr>
              <?php
              $n++;
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- pilih tipe -->
<select name="tipe" class="form-control select2" style="width: 100%;" required>
  <?php
  $arrayTipe=array('A1','A2');
  for($i=0; $i<count($arrayTipe); $i++){
    $selected=selected($arrayTipe[$i],$dataUpdate['tipe']??'');
    ?>
    <option value="<?=$arrayTipe[$i]?>" <?=$selected?>> <?=$arrayTipe[$i]?> </option>
    <?php
  }
  ?>
</select>

<!-- pilih database -->
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

<!-- tanggal -->
 $tanggalBiaya = $dataUpdate['tanggalBiaya']??'';

(Taruh di suskses JS tombol tambah supaya tidak bisa milih tanggal mundur)
 $('.date').datepicker({
        startDate: 'd'
        });

<link rel="stylesheet" href="<?=$BASE_URL_HTML?>/assets/vendor/datepicker/datepicker.min.css">
<script src="<?=$BASE_URL_HTML?>/assets/vendor/datepicker/datepicker.min.js"></script>

<div class="input-group date"  data-date-autoclose="true" data-provide="datepicker"  data-date-format="yyyy-mm-dd">
  <input type="tanggal" class="form-control" name="tanggalPenyesuaian" id="tanggalPenyesuaian" value="<?=$tanggalPenyesuaian?>"  autocomplete="off">
  <div class="input-group-append">                                            
    <button class="btn btn-outline-secondary" type="button">
      <i class="fa fa-calendar"></i>
    </button>
  </div>
</div>


<!-- onkeyup rupiah -->
$dataUpdate['jumlahBiaya'] = ubahToRp($dataUpdate['jumlahBiaya']??'');

<input type="text" class="form-control" placeholder="Input Nominal" name="nominal" id="nominal" onkeyup="ubahToRp('#nominal')" value="<?=$dataUpdate['nominal']??''?>">

  <script src="<?=$BASE_URL_HTML?>/assets/custom_js/accounting.min.js"></script>
  <script src="<?=$BASE_URL_HTML?>/assets/custom_js/rupiah.js"></script>

<!-- reset form -->
  function resetForm(sukses){
  if(sukses == 1){
    $('input[type=text]').val('');
    $('#flag').val('');
    $('.select2').val(null).trigger('change');


<!-- script daterange -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
  <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
  <script type="text/javascript">
    $("#rentang").daterangepicker({
      locale: {
          format: 'YYYY-MM-DD'
      }
    });


    $tanggal = explode(' - ', $rentang);
$tanggalAwal = $tanggal[0];
$tanggalAkhir = $tanggal[1]; 

  </script>

  <?php 
      $linkEdit = editPemasukanLain('<?=$row['idPemasukanLain']?>');
      $linkfinal = finalPemasukanLain('<?=$row['idPemasukanLain']?>');
      $linkBuka = bukaPemasukanLain('<?=$row['idPemasukanLain']?>');
      $btnwarning = 'btn-warning';
      $btnprimary = 'btn-primary';
      if($row['statusFinal']=='Final'){
        $linkEdit = '#';
        $linkfinal = '#';
        $btnwarning = 'btn-secondary';
        $btnprimary = 'btn-secondary';
      }
       ?>

<!-- textarea -->
 <textarea class="form-control" name="alamatPegawai" placeholder="alamat Pegawai" id="alamatPegawai"><?=$dataUpdate['alamatPegawai']??''?></textarea>
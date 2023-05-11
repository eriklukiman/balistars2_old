function dataDaftarKonfirmasiKasKecil(){
  //console.log($('#rentang').val());
  $.ajax({
    url:'datadaftarkonfirmasikaskecil.php',
    type:'post',
    data:{
      idCabang : $('#idCabang').val(),
      rentang : $('#rentang').val()
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarKonfirmasiKasKecil').empty().append(data);
      //$('#parameterOrder').val(parameterOrder);
      $('.overlay').hide();
    }
  });
}

function finalKonfirmasiKasKecil(id) {
  //console.log(id);
  $("#modalFormKonfirmasiKasKecil").modal('show');

  $.ajax({
    url:'dataformkonfirmasikaskecil.php',
    type:'post',
    data:{
      idOrderKasKecil : id
    },
    beforeSend:function(){
    },
    success:function(data,status){
      $("#dataFormKonfirmasiKasKecil").html(data);
      $('select.select2').select2();

      CKEDITOR.replace('keterangan');
       }
  });
}

function bukaKonfirmasiKasKecil(id) {
  $.ajax({
    url:'proseskonfirmasikaskecil.php',
    type:'post',
    data:{
      idOrderKasKecil : id,
      flag            : 'cancel'
    },
    beforeSend:function(){
    },
    success:function(data,status){
      dataDaftarKonfirmasiKasKecil();
      $('.overlay').hide();
      }
  });
}

function prosesKonfirmasiKasKecil(){

  let formKonfirmasiKasKecil = document.getElementById('formKonfirmasiKasKecil');
  let dataForm           = new FormData(formKonfirmasiKasKecil);

  const validasi = formValidation(dataForm);
  if (validasi === true) {
    $.ajax({
      url:'proseskonfirmasikaskecil.php',
      type:'post',
      enctype: 'multipart/form-data',
      processData: false,
      contentType: false,
      data:dataForm,

      beforeSend:function(){
      },

      success:function(data,status){
        // console.log(data);
        let dataJSON = JSON.parse(data);
        dataDaftarKonfirmasiKasKecil(dataJSON.parameterOrder);
        notifikasi(dataJSON.notifikasi);
        resetForm(dataJSON.notifikasi);
        if(dataJSON.notifikasi==1){
          $('#modalFormKonfirmasiKasKecil').modal('hide');
        }
      }
    });
  }
  else {
     notifikasi(2, "Proses Gagal, Form Belum Terisi Dengan Lengkap");
  }
}

function resetForm(sukses){
  if(sukses == 1){
    $('input[type=text]').val('');
    $('#flag').val('');
    $('#cabang').val(null).trigger('change');
    $('#jenis').val(null).trigger('change');
    //$('.select2').val(null).trigger('change');
  }
}

function notifikasi(sukses){
  if(sukses == 1){
    toastr.success('Proses Data Berhasil');
  }
  else if(sukses == 2){
    toastr.error('Proses Data Gagal');
  }
  else if(sukses == 3){
    toastr.error('Proses Gagal, nilai Approved Melebihi Order');
  }
}
function dataDaftarPembelianFakturPajak(){
  //console.log($('#parameterOrder').val());
  $.ajax({
    url:'datadaftarpembelianfakturpajak.php',
    type:'post',
    data:{
      idCabang : $('#idCabang').val(),
      rentang : $('#rentang').val(),
      parameterOrder : $('#parameterOrder').val()
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarPembelianFakturPajak').empty().append(data);
      //$('#parameterOrder').val(parameterOrder);
      $('.overlay').hide();
    }
  });
}

function tambahPembelianFakturPajak(id) {
  //console.log(id);
  $("#modalFormPembelianFakturPajak").modal('show');

  $.ajax({
    url:'dataformpembelianfakturpajak.php',
    type:'post',
    data:{
      idPembelian : id
    },
    beforeSend:function(){
    },
    success:function(data,status){
      $("#dataFormPembelianFakturPajak").html(data);
      $('select.select2').select2();
       }
  });
}

// function bukaPembelianFakturPajak(id) {
//   $.ajax({
//     url:'prosesPembelianFakturPajak.php',
//     type:'post',
//     data:{
//       idOrderKasKecil : id,
//       flag            : 'cancel'
//     },
//     beforeSend:function(){
//     },
//     success:function(data,status){
//       dataDaftarPembelianFakturPajak();
//       $('.overlay').hide();
//       }
//   });
// }

function prosesPembelianFaktur(id,noFakturPajak){

    $.ajax({
      url:'prosespembelianfakturpajak.php',
      type:'post',
      data:{
        noFakturPajak : $('#'+noFakturPajak).val(),
        idPembelian : id,
      },

      success:function(data,status){
        // console.log(data);
        let dataJSON = JSON.parse(data);
        dataDaftarPembelianFakturPajak(dataJSON.parameterOrder);
        notifikasi(dataJSON.notifikasi);
        resetForm(dataJSON.notifikasi);
      }
    });
}

function prosesPembelianFakturPajak(){

  let formPembelianFakturPajak = document.getElementById('formPembelianFakturPajak');
  let dataForm           = new FormData(formPembelianFakturPajak);

  const validasi = formValidation(dataForm);
  if (validasi === true) {
    $.ajax({
      url:'prosespembelianfakturpajak.php',
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
        dataDaftarPembelianFakturPajak(dataJSON.parameterOrder);
        notifikasi(dataJSON.notifikasi);
        resetForm(dataJSON.notifikasi);
        if(dataJSON.notifikasi==1){
          $('#modalFormPembelianFakturPajak').modal('hide');
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
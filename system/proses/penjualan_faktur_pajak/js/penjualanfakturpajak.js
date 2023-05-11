function dataDaftarPenjualanFakturPajak(){
  //console.log($('#parameterOrder').val());
  $.ajax({
    url:'datadaftarpenjualanfakturpajak.php',
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
      $('#dataDaftarPenjualanFakturPajak').empty().append(data);
      //$('#parameterOrder').val(parameterOrder);
      $('.overlay').hide();
    }
  });
}

function tambahPenjualanFakturPajak(id) {
  //console.log(id);
  $("#modalFormPenjualanFakturPajak").modal('show');

  $.ajax({
    url:'dataformpenjualanfakturpajak.php',
    type:'post',
    data:{
      idPenjualan : id
    },
    beforeSend:function(){
    },
    success:function(data,status){
      $("#dataFormPenjualanFakturPajak").html(data);
      $('select.select2').select2();
       }
  });
}

// function bukaPenjualanFakturPajak(id) {
//   $.ajax({
//     url:'prosesPenjualanFakturPajak.php',
//     type:'post',
//     data:{
//       idOrderKasKecil : id,
//       flag            : 'cancel'
//     },
//     beforeSend:function(){
//     },
//     success:function(data,status){
//       dataDaftarPenjualanFakturPajak();
//       $('.overlay').hide();
//       }
//   });
// }

function prosesPenjualanFakturPajak(id,faktur){

  $.ajax({
    url:'prosespenjualanfakturpajak.php',
    type:'post',
    data:{
      idPenjualan : id,
      cabangCustomer : $('input[name=cabangCustomer][data-id='+faktur+']').val(),
      noFakturPajak : $('input[name=noFakturPajak][data-id='+faktur+']').val()
    },

    beforeSend:function(){
    },

    success:function(data,status){
      // console.log(data);
      let dataJSON = JSON.parse(data);
      dataDaftarPenjualanFakturPajak(dataJSON.parameterOrder);
      notifikasi(dataJSON.notifikasi);
      resetForm(dataJSON.notifikasi);
    }
  });
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
$("#rentang").daterangepicker({
  locale: {
      format: 'DD-MM-YYYY'
  }
});

// function getDaftarRentang() {
//   var parameterOrder = $('#parameterOrder').val();
//   dataDaftarMasterDataPenyusutan(parameterOrder);
// }

function dataDaftarMasterDataPenyusutan(parameterOrder){
  $.ajax({
    url:'datadaftarmasterdatapenyusutan.php',
    type:'post',
    data:{
      parameterOrder : parameterOrder,
      rentang : $('#rentang').val()
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarMasterDataPenyusutan').empty().append(data);
      $('#parameterOrder').val(parameterOrder);
      $('.overlay').hide();
    }
  });
}

function tambahPenyusutan() {
  $("#modalFormMasterDataPenyusutan").modal('show');

  $.ajax({
    url:'dataformmasterdatapenyusutan.php',
    type:'post',
    data:{
      parameterOrder  : $('#parameterOrder').val(),
      flag            : ''
    },
    beforeSend:function(){
    },
    success:function(data,status){
      $("#dataFormMasterDataPenyusutan").html(data);
      $('select.select2').select2();
    }
  });
}

function editPenyusutan(id) {
  $("#modalFormMasterDataPenyusutan").modal('show');

    $.ajax({
      url:'dataformmasterdatapenyusutan.php',
      type:'post',
      data:{
        parameterOrder  : $('#parameterOrder').val(),
        idPenyusutan       : id,
        flag            : 'update'
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormMasterDataPenyusutan").html(data);
        $('select.select2').select2();
      }
    });
}


function prosesMasterDataPenyusutan(){

  let formMasterDataPenyusutan = document.getElementById('formMasterDataPenyusutan');
  let dataForm           = new FormData(formMasterDataPenyusutan);

  const validasi = formValidation(dataForm);
  if (validasi === true) {
    $.ajax({
      url:'prosesmasterdatapenyusutan.php',
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
        dataDaftarMasterDataPenyusutan(dataJSON.parameterOrder);
        notifikasi(dataJSON.notifikasi);
        resetForm(dataJSON.notifikasi);
        if(dataJSON.flag == 'update'){
          $("#modalFormMasterDataPenyusutan").modal('hide');
        }
      }
    });
  }
  else {
     notifikasi(2, "Proses Gagal, Form Belum Terisi Dengan Lengkap");
  }
}

function cancelPenyusutan(id){
  swal({
    title: "Apakah anda yakin ingin membatalkan data Penyusutan ini?",
    text: "Setelah dibatalkan, proses tidak dapat diulangi!",
    icon: "warning",
    buttons: true,
    dangerMode: true,
  })
  .then((willDelete) => {
    if(willDelete){

      let parameterOrder = $('#parameterOrder').val();
      let flag           = 'cancel';
      
      $.ajax({
        url:'prosesmasterdatapenyusutan.php',
        type:'post',
        data:{
          idPenyusutan  : id,
          parameterOrder : parameterOrder,
          flag           : flag
        },

        success:function(data,status){
          let dataJSON=JSON.parse(data);
          dataDaftarMasterDataPenyusutan(dataJSON.parameterOrder);
          notifikasi(dataJSON.notifikasi);
        }
      });
    } 
    else{
      swal({
        title: "Proses pembatalan Penyusutan dibatalkan!",
        icon: "warning"
      });
    }
  });
}

function resetForm(sukses){
  if(sukses == 1){
    $('input[type=text]').val('');
    $('#flag').val('');
    $('.select2').val(null).trigger('change');
  }
}

function notifikasi(sukses){
  if(sukses == 1){
    toastr.success('Proses Data Penyusutan Berhasil');
  }
  else if(sukses == 2){
    toastr.error('Proses Data Penyusutan Gagal');
  }
}
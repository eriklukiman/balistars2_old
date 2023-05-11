function dataDaftarMasterDataSift(parameterOrder){
  $.ajax({
    url:'datadaftarmasterdatasift.php',
    type:'post',
    data:{
      parameterOrder : parameterOrder
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarMasterDataSift').empty().append(data);
      $('#parameterOrder').val(parameterOrder);
      $('.overlay').hide();
    }
  });
}

function tambahSift() {
  $("#modalFormMasterDataSift").modal('show');

  $.ajax({
    url:'dataformmasterdatasift.php',
    type:'post',
    data:{
      parameterOrder  : $('#parameterOrder').val(),
      flag            : ''
    },
    beforeSend:function(){
    },
    success:function(data,status){
      $("#dataFormMasterDataSift").html(data);
      $('select.select2').select2();
    }
  });
}

function editSift(id) {
  $("#modalFormMasterDataSift").modal('show');

    $.ajax({
      url:'dataformmasterdatasift.php',
      type:'post',
      data:{
        parameterOrder  : $('#parameterOrder').val(),
        idSift       : id,
        flag            : 'update'
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormMasterDataSift").html(data);
        $('select.select2').select2();
      }
    });
}


function prosesMasterDataSift(){

  let formMasterDataSift = document.getElementById('formMasterDataSift');
  let dataForm           = new FormData(formMasterDataSift);

  const validasi = formValidation(dataForm);
  if (validasi === true) {
    $.ajax({
      url:'prosesmasterdatasift.php',
      type:'post',
      enctype: 'multipart/form-data',
      processData: false,
      contentType: false,
      data:dataForm,

      beforeSend:function(){
      },

      success:function(data,status){
         //console.log(data);
        let dataJSON = JSON.parse(data);
        dataDaftarMasterDataSift(dataJSON.parameterOrder);
        notifikasi(dataJSON.notifikasi);
        resetForm(dataJSON.notifikasi);
        if(dataJSON.flag == 'update'){
          $("#modalFormMasterDataSift").modal('hide');
        }
      }
    });
  } 
  else {
    notifikasi(2, "Proses Gagal, Form Belum Terisi Dengan Lengkap");
  }
}

function cencelSift(id){
  swal({
    title: "Apakah anda yakin ingin membatalkan data Sift ini?",
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
        url:'prosesmasterdatasift.php',
        type:'post',
        data:{
          idSift  : id,
          parameterOrder : parameterOrder,
          flag           : flag
        },

        success:function(data,status){
          let dataJSON=JSON.parse(data);
          dataDaftarMasterDataSift(dataJSON.parameterOrder);
          notifikasi(dataJSON.notifikasi);
        }
      });
    } 
    else{
      swal({
        title: "Proses pembatalan Sift dibatalkan!",
        icon: "warning"
      });
    }
  });
}

function resetForm(sukses){
  if(sukses == 1){
    $('input[type=time]').val('');
    $('#flag').val('');
    $('.select2').val(null).trigger('change');
  }
}

function notifikasi(sukses){
  if(sukses == 1){
    toastr.success('Proses Data Sift Berhasil');
  }
  else if(sukses == 2){
    toastr.error('Proses Data Sift Gagal');
  }
}
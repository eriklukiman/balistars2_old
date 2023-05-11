function dataDaftarMasterDataBiayaKlik(parameterOrder){
  $.ajax({
    url:'datadaftarmasterdatabiayaklik.php',
    type:'post',
    data:{
      parameterOrder : parameterOrder
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarMasterDataBiayaKlik').empty().append(data);
      $('#parameterOrder').val(parameterOrder);
      $('.overlay').hide();
    }
  });
}

function tambahBiayaKlik() {
  $("#modalFormMasterDataBiayaKlik").modal('show');

  $.ajax({
    url:'dataformmasterdatabiayaklik.php',
    type:'post',
    data:{
      parameterOrder  : $('#parameterOrder').val(),
      flag            : ''
    },
    beforeSend:function(){
    },
    success:function(data,status){
      $("#dataFormMasterDataBiayaKlik").html(data);
      $('select.select2').select2();
    }
  });
}

function editBiayaKlik(id) {
  $("#modalFormMasterDataBiayaKlik").modal('show');

    $.ajax({
      url:'dataformmasterdatabiayaklik.php',
      type:'post',
      data:{
        parameterOrder  : $('#parameterOrder').val(),
        idBiayaKlik       : id,
        flag            : 'update'
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormMasterDataBiayaKlik").html(data);
        $('select.select2').select2();
      }
    });
}


function prosesMasterDataBiayaKlik(){

  let formMasterDataBiayaKlik = document.getElementById('formMasterDataBiayaKlik');
  let dataForm           = new FormData(formMasterDataBiayaKlik);

  const validasi = formValidation(dataForm);
  if (validasi === true) {
    $.ajax({
      url:'prosesmasterdatabiayaklik.php',
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
        dataDaftarMasterDataBiayaKlik(dataJSON.parameterOrder);
        notifikasi(dataJSON.notifikasi);
        resetForm(dataJSON.notifikasi);
        if(dataJSON.flag == 'update'){
          $("#modalFormMasterDataBiayaKlik").modal('hide');
        }
      }
    });
  }
  else {
     notifikasi(2, "Proses Gagal, Form Belum Terisi Dengan Lengkap");
  }
}

function cancelBiayaKlik(id){
  swal({
    title: "Apakah anda yakin ingin membatalkan data BiayaKlik ini?",
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
        url:'prosesmasterdatabiayaklik.php',
        type:'post',
        data:{
          idBiayaKlik  : id,
          parameterOrder : parameterOrder,
          flag           : flag
        },

        success:function(data,status){
          let dataJSON=JSON.parse(data);
          dataDaftarMasterDataBiayaKlik(dataJSON.parameterOrder);
          notifikasi(dataJSON.notifikasi);
        }
      });
    } 
    else{
      swal({
        title: "Proses pembatalan Biaya Klik dibatalkan!",
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
    toastr.success('Proses Data Biaya Klik Berhasil');
  }
  else if(sukses == 2){
    toastr.error('Proses Data Biaya Klik Gagal');
  }
}
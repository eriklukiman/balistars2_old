function dataDaftarMasterDataCabang(parameterOrder){
  $.ajax({
    url:'datadaftarmasterdatacabang.php',
    type:'post',
    data:{
      parameterOrder : parameterOrder
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarMasterDataCabang').empty().append(data);
      $('#parameterOrder').val(parameterOrder);
      $('.overlay').hide();
    }
  });
}

function tambahCabang() {
  $("#modalFormMasterDataCabang").modal('show');

  $.ajax({
    url:'dataformmasterdatacabang.php',
    type:'post',
    data:{
      parameterOrder  : $('#parameterOrder').val(),
      flag            : ''
    },
    beforeSend:function(){
    },
    success:function(data,status){
      $("#dataFormMasterDataCabang").html(data);
      $('select.select2').select2();
    }
  });
}

function editCabang(id) {
  $("#modalFormMasterDataCabang").modal('show');

    $.ajax({
      url:'dataformmasterdatacabang.php',
      type:'post',
      data:{
        parameterOrder  : $('#parameterOrder').val(),
        idCabang       : id,
        flag            : 'update'
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormMasterDataCabang").html(data);
        $('select.select2').select2();
      }
    });
}


function prosesMasterDataCabang(){

  let formMasterDataCabang = document.getElementById('formMasterDataCabang');
  let dataForm           = new FormData(formMasterDataCabang);

  const validasi = formValidation(dataForm);
  if (validasi === true) {
    $.ajax({
      url:'prosesmasterdatacabang.php',
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
        dataDaftarMasterDataCabang(dataJSON.parameterOrder);
        notifikasi(dataJSON.notifikasi);
        resetForm(dataJSON.notifikasi);
        if(dataJSON.flag == 'update'){
          $("#modalFormMasterDataCabang").modal('hide');
        }
      }
    });
  }
  else {
     notifikasi(2, "Proses Gagal, Form Belum Terisi Dengan Lengkap");
  }
}

function cancelCabang(id){
  swal({
    title: "Apakah anda yakin ingin membatalkan data cabang ini?",
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
        url:'prosesmasterdatacabang.php',
        type:'post',
        data:{
          idCabang  : id,
          parameterOrder : parameterOrder,
          flag           : flag
        },

        success:function(data,status){
          let dataJSON=JSON.parse(data);
          dataDaftarMasterDataCabang(dataJSON.parameterOrder);
          notifikasi(dataJSON.notifikasi);
        }
      });
    } 
    else{
      swal({
        title: "Proses pembatalan bank dibatalkan!",
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
    toastr.success('Proses Data Bank Berhasil');
  }
  else if(sukses == 2){
    toastr.error('Proses Data Bank Gagal');
  }
}
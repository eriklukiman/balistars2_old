function dataDaftarMasterDataJenisBiayaCabang(parameterOrder){
  $.ajax({
    url:'datadaftarmasterdatajenisbiayacabang.php',
    type:'post',
    data:{
      parameterOrder : parameterOrder
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarMasterDataJenisBiayaCabang').empty().append(data);
      $('#parameterOrder').val(parameterOrder);
      $('.overlay').hide();
    }
  });
}

function tambahJenisBiayaCabang() {
  $("#modalFormMasterDataJenisBiayaCabang").modal('show');

  $.ajax({
    url:'dataformmasterdatajenisbiayacabang.php',
    type:'post',
    data:{
      parameterOrder  : $('#parameterOrder').val(),
      flag            : ''
    },
    beforeSend:function(){
    },
    success:function(data,status){
      $("#dataFormMasterDataJenisBiayaCabang").html(data);
      $('select.select2').select2();
    }
  });
}

function editJenisBiayaCabang(id) {
  $("#modalFormMasterDataJenisBiayaCabang").modal('show');

    $.ajax({
      url:'dataformmasterdatajenisbiayacabang.php',
      type:'post',
      data:{
        parameterOrder  : $('#parameterOrder').val(),
        idJenisBiayaCabang       : id,
        flag            : 'update'
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormMasterDataJenisBiayaCabang").html(data);
        $('select.select2').select2();
      }
    });
}


function prosesMasterDataJenisBiayaCabang(){

  let formMasterDataJenisBiayaCabang = document.getElementById('formMasterDataJenisBiayaCabang');
  let dataForm           = new FormData(formMasterDataJenisBiayaCabang);

  const validasi = formValidation(dataForm);
  if (validasi === true) {
    $.ajax({
      url:'prosesmasterdatajenisbiayacabang.php',
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
        dataDaftarMasterDataJenisBiayaCabang(dataJSON.parameterOrder);
        notifikasi(dataJSON.notifikasi);
        resetForm(dataJSON.notifikasi);
        if(dataJSON.flag == 'update'){
          $("#modalFormMasterDataJenisBiayaCabang").modal('hide');
        }
      }
    });
  }
  else {
     notifikasi(2, "Proses Gagal, Form Belum Terisi Dengan Lengkap");
  }
}

function cancelJenisBiayaCabang(id){
  swal({
    title: "Apakah anda yakin ingin membatalkan data JenisBiayaCabang ini?",
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
        url:'prosesmasterdatajenisbiayacabang.php',
        type:'post',
        data:{
          idJenisBiayaCabang : id,
          parameterOrder : parameterOrder,
          flag           : flag
        },

        success:function(data,status){
          let dataJSON=JSON.parse(data);
          dataDaftarMasterDataJenisBiayaCabang(dataJSON.parameterOrder);
          notifikasi(dataJSON.notifikasi);
        }
      });
    } 
    else{
      swal({
        title: "Proses pembatalan JenisBiayaCabang dibatalkan!",
        icon: "warning"
      });
    }
  });
}

function resetForm(sukses){
  if(sukses == 1){
    $('input[type=text]').val('');
    $('#flag').val('');
  }
}

function notifikasi(sukses){
  if(sukses == 1){
    toastr.success('Proses Data JenisBiayaCabang Berhasil');
  }
  else if(sukses == 2){
    toastr.error('Proses Data JenisBiayaCabang Gagal');
  }
}
function dataDaftarMasterDataJenisPenjualan(parameterOrder){
  $.ajax({
    url:'datadaftarmasterdatajenispenjualan.php',
    type:'post',
    data:{
      parameterOrder : parameterOrder
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarMasterDataJenisPenjualan').empty().append(data);
      $('#parameterOrder').val(parameterOrder);
      $('.overlay').hide();
    }
  });
}

function tambahJenisPenjualan() {
  $("#modalFormMasterDataJenisPenjualan").modal('show');

  $.ajax({
    url:'dataformmasterdatajenispenjualan.php',
    type:'post',
    data:{
      parameterOrder  : $('#parameterOrder').val(),
      flag            : ''
    },
    beforeSend:function(){
    },
    success:function(data,status){
      $("#dataFormMasterDataJenisPenjualan").html(data);
      $('select.select2').select2();
    }
  });
}

function editJenisPenjualan(id) {
  $("#modalFormMasterDataJenisPenjualan").modal('show');

    $.ajax({
      url:'dataformmasterdatajenispenjualan.php',
      type:'post',
      data:{
        parameterOrder  : $('#parameterOrder').val(),
        idJenisPenjualan       : id,
        flag            : 'update'
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormMasterDataJenisPenjualan").html(data);
        $('select.select2').select2();
      }
    });
}


function prosesMasterDataJenisPenjualan(){

  let formMasterDataJenisPenjualan = document.getElementById('formMasterDataJenisPenjualan');
  let dataForm           = new FormData(formMasterDataJenisPenjualan);

  const validasi = formValidation(dataForm);
  if (validasi === true) {
    $.ajax({
      url:'prosesmasterdatajenispenjualan.php',
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
        dataDaftarMasterDataJenisPenjualan(dataJSON.parameterOrder);
        notifikasi(dataJSON.notifikasi);
        resetForm(dataJSON.notifikasi);
        if(dataJSON.flag == 'update'){
          $("#modalFormMasterDataJenisPenjualan").modal('hide');
        }
      }
    });
   }
  else {
     notifikasi(2, "Proses Gagal, Form Belum Terisi Dengan Lengkap");
  }
}

function cancelJenisPenjualan(id){
  swal({
    title: "Apakah anda yakin ingin membatalkan data Jenis Penjualan ini?",
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
        url:'prosesmasterdatajenispenjualan.php',
        type:'post',
        data:{
          idJenisPenjualan  : id,
          parameterOrder : parameterOrder,
          flag           : flag
        },

        success:function(data,status){
          let dataJSON=JSON.parse(data);
          dataDaftarMasterDataJenisPenjualan(dataJSON.parameterOrder);
          notifikasi(dataJSON.notifikasi);
        }
      });
    } 
    else{
      swal({
        title: "Proses pembatalan Jenis Penjualan dibatalkan!",
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
    toastr.success('Proses Data Jenis Penjualan Berhasil');
  }
  else if(sukses == 2){
    toastr.error('Proses Data Jenis Penjualan Gagal');
  }
}
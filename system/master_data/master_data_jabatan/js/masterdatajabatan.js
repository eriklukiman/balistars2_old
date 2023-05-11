function dataDaftarMasterDataJabatan(parameterOrder){
  $.ajax({
    url:'datadaftarmasterdatajabatan.php',
    type:'post',
    data:{
      parameterOrder : parameterOrder
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarMasterDataJabatan').empty().append(data);
      $('#parameterOrder').val(parameterOrder);
      $('.overlay').hide();
    }
  });
}

function tambahJabatan() {
  $("#modalFormMasterDataJabatan").modal('show');

  $.ajax({
    url:'dataformmasterdatajabatan.php',
    type:'post',
    data:{
      parameterOrder  : $('#parameterOrder').val(),
      flag            : ''
    },
    beforeSend:function(){
    },
    success:function(data,status){
      $("#dataFormMasterDataJabatan").html(data);
      $('select.select2').select2();
    }
  });
}

function editJabatan(id) {
  $("#modalFormMasterDataJabatan").modal('show');

    $.ajax({
      url:'dataformmasterdatajabatan.php',
      type:'post',
      data:{
        parameterOrder  : $('#parameterOrder').val(),
        idJabatan       : id,
        flag            : 'update'
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormMasterDataJabatan").html(data);
        $('select.select2').select2();
      }
    });
}


function prosesMasterDataJabatan(){

  let formMasterDataJabatan = document.getElementById('formMasterDataJabatan');
  let dataForm           = new FormData(formMasterDataJabatan);

  const validasi = formValidation(dataForm);
  if (validasi === true) {
    $.ajax({
      url:'prosesmasterdatajabatan.php',
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
        dataDaftarMasterDataJabatan(dataJSON.parameterOrder);
        notifikasi(dataJSON.notifikasi);
        resetForm(dataJSON.notifikasi);
        if(dataJSON.flag == 'update'){
          $("#modalFormMasterDataJabatan").modal('hide');
        }
      }
    });
  }
  else {
     notifikasi(2, "Proses Gagal, Form Belum Terisi Dengan Lengkap");
  }
}

function cancelJabatan(id){
  swal({
    title: "Apakah anda yakin ingin membatalkan data jabatan ini?",
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
        url:'prosesmasterdatajabatan.php',
        type:'post',
        data:{
          idJabatan : id,
          parameterOrder : parameterOrder,
          flag           : flag
        },

        success:function(data,status){
          let dataJSON=JSON.parse(data);
          dataDaftarMasterDataJabatan(dataJSON.parameterOrder);
          notifikasi(dataJSON.notifikasi);
        }
      });
    } 
    else{
      swal({
        title: "Proses pembatalan jabatan dibatalkan!",
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
    toastr.success('Proses Data Jabatan Berhasil');
  }
  else if(sukses == 2){
    toastr.error('Proses Data Jabatan Gagal');
  }
}
function dataDaftarMasterDataGedung(parameterOrder){
  $.ajax({
    url:'datadaftarmasterdatagedung.php',
    type:'post',
    data:{
      parameterOrder : parameterOrder
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarMasterDataGedung').empty().append(data);
      $('#parameterOrder').val(parameterOrder);
      $('.overlay').hide();
    }
  });
}

function tambahGedung() {
  $("#modalFormMasterDataGedung").modal('show');

  $.ajax({
    url:'dataformmasterdatagedung.php',
    type:'post',
    data:{
      parameterOrder  : $('#parameterOrder').val(),
      flag            : ''
    },
    beforeSend:function(){
    },
    success:function(data,status){
      $("#dataFormMasterDataGedung").html(data);
      $('select.select2').select2();
    }
  });
}

function editGedung(id) {
  $("#modalFormMasterDataGedung").modal('show');

    $.ajax({
      url:'dataformmasterdatagedung.php',
      type:'post',
      data:{
        parameterOrder  : $('#parameterOrder').val(),
        idGedung       : id,
        flag            : 'update'
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormMasterDataGedung").html(data);
        $('select.select2').select2();
      }
    });
}


function prosesMasterDataGedung(){

  let formMasterDataGedung = document.getElementById('formMasterDataGedung');
  let dataForm           = new FormData(formMasterDataGedung);

  const validasi = formValidation(dataForm);
  if (validasi === true) {
    $.ajax({
      url:'prosesmasterdatagedung.php',
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
        dataDaftarMasterDataGedung(dataJSON.parameterOrder);
        notifikasi(dataJSON.notifikasi);
        resetForm(dataJSON.notifikasi);
        if(dataJSON.flag == 'update'){
          $("#modalFormMasterDataGedung").modal('hide');
        }
      }
    });
  }
  else {
     notifikasi(2, "Proses Gagal, Form Belum Terisi Dengan Lengkap");
  }
}

function cancelGedung(id){
  swal({
    title: "Apakah anda yakin ingin membatalkan data Gedung ini?",
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
        url:'prosesmasterdatagedung.php',
        type:'post',
        data:{
          idGedung  : id,
          parameterOrder : parameterOrder,
          flag           : flag
        },

        success:function(data,status){
          let dataJSON=JSON.parse(data);
          dataDaftarMasterDataGedung(dataJSON.parameterOrder);
          notifikasi(dataJSON.notifikasi);
        }
      });
    } 
    else{
      swal({
        title: "Proses pembatalan Gedung dibatalkan!",
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
    toastr.success('Proses Data Gedung Berhasil');
  }
  else if(sukses == 2){
    toastr.error('Proses Data Gedung Gagal');
  }
}
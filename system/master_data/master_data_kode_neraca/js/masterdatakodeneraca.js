function dataDaftarMasterDataKodeNeraca(parameterOrder){
  
  $.ajax({
    url:'datadaftarmasterdatakodeneraca.php',
    type:'post',
    data:{
      parameterOrder : parameterOrder
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarMasterDataKodeNeraca').empty().append(data);
      $('#parameterOrder').val(parameterOrder);
      $('.overlay').hide();
    }
  });
}

function tambahKodeNeraca() {
  $("#modalFormMasterDataKodeNeraca").modal('show');

  $.ajax({
    url:'dataformmasterdatakodeneraca.php',
    type:'post',
    data:{
      parameterOrder  : $('#parameterOrder').val(),
      flag            : ''
    },
    beforeSend:function(){
    },
    success:function(data,status){
      $("#dataFormMasterDataKodeNeraca").html(data);
      $('select.select2').select2();
    }
  });
}

function editKodeNeraca(id) {
  //console.log(id);
  $("#modalFormMasterDataKodeNeraca").modal('show');

    $.ajax({
      url:'dataformmasterdatakodeneraca.php',
      type:'post',
      data:{
        parameterOrder  : $('#parameterOrder').val(),
        idKodeNeracaLajur       : id,
        flag            : 'update'
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormMasterDataKodeNeraca").html(data);
        $('select.select2').select2();
      }
    });
}


function prosesMasterDataKodeNeraca(){

  let formMasterDataKodeNeraca = document.getElementById('formMasterDataKodeNeraca');
  let dataForm           = new FormData(formMasterDataKodeNeraca);

  const validasi = formValidation(dataForm);
  if (validasi === true) {
    $.ajax({
      url:'prosesmasterdatakodeneraca.php',
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
        dataDaftarMasterDataKodeNeraca(dataJSON.parameterOrder);
        notifikasi(dataJSON.notifikasi);
        resetForm(dataJSON.notifikasi);
        if(dataJSON.flag == 'update'){
          $("#modalFormMasterDataKodeNeraca").modal('hide');
        }
      }
    });
  } 
  else {
    notifikasi(2, "Proses Gagal, Form Belum Terisi Dengan Lengkap");
  }
}

function cencelKodeNeraca(id){
  swal({
    title: "Apakah anda yakin ingin membatalkan data kodeNeraca ini?",
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
        url:'prosesmasterdatakodeneraca.php',
        type:'post',
        data:{
          idKodeNeracaLajur  : id,
          parameterOrder : parameterOrder,
          flag           : flag
        },

        success:function(data,status){
          let dataJSON=JSON.parse(data);
          dataDaftarMasterDataKodeNeraca(dataJSON.parameterOrder);
          notifikasi(dataJSON.notifikasi);
        }
      });
    } 
    else{
      swal({
        title: "Proses pembatalan kodeNeraca dibatalkan!",
        icon: "warning"
      });
    }
  });
}

function resetForm(sukses){
  if(sukses == 1){
    $('input[type=text]').val('');
    $('#flag').val('');
    $('#keterangan').val('');
  }
}

function notifikasi(sukses){
  if(sukses == 1){
    toastr.success('Proses Data  Berhasil');
  }
  else if(sukses == 2){
    toastr.error('Proses Data Gagal');
  }
}
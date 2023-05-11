function dataDaftarMasterDataKodeAkunting(parameterOrder){
  
  $.ajax({
    url:'datadaftarmasterdatakodeakunting.php',
    type:'post',
    data:{
      parameterOrder : parameterOrder
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarMasterDataKodeAkunting').empty().append(data);
      $('#parameterOrder').val(parameterOrder);
      $('.overlay').hide();
    }
  });
}

function tambahKodeAkunting() {
  $("#modalFormMasterDataKodeAkunting").modal('show');

  $.ajax({
    url:'dataformmasterdatakodeakunting.php',
    type:'post',
    data:{
      parameterOrder  : $('#parameterOrder').val(),
      flag            : ''
    },
    beforeSend:function(){
    },
    success:function(data,status){
      $("#dataFormMasterDataKodeAkunting").html(data);
      $('select.select2').select2();
    }
  });
}

function editKodeAkunting(id) {
  $("#modalFormMasterDataKodeAkunting").modal('show');

    $.ajax({
      url:'dataformmasterdatakodeakunting.php',
      type:'post',
      data:{
        parameterOrder  : $('#parameterOrder').val(),
        idKodeAkunting       : id,
        flag            : 'update'
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormMasterDataKodeAkunting").html(data);
        $('select.select2').select2();
      }
    });
}


function prosesMasterDataKodeAkunting(){

  let formMasterDataKodeAkunting = document.getElementById('formMasterDataKodeAkunting');
  let dataForm           = new FormData(formMasterDataKodeAkunting);

  const validasi = formValidation(dataForm);
  if (validasi === true) {
    $.ajax({
      url:'prosesmasterdatakodeakunting.php',
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
        dataDaftarMasterDataKodeAkunting(dataJSON.parameterOrder);
        notifikasi(dataJSON.notifikasi);
        resetForm(dataJSON.notifikasi);
        if(dataJSON.flag == 'update'){
          $("#modalFormMasterDataKodeAkunting").modal('hide');
        }
      }
    });
  } 
  else {
    notifikasi(2, "Proses Gagal, Form Belum Terisi Dengan Lengkap");
  }
}

function cencelKodeAkunting(id){
  swal({
    title: "Apakah anda yakin ingin membatalkan data kodeakunting ini?",
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
        url:'prosesmasterdatakodeakunting.php',
        type:'post',
        data:{
          idKodeAkunting  : id,
          parameterOrder : parameterOrder,
          flag           : flag
        },

        success:function(data,status){
          let dataJSON=JSON.parse(data);
          dataDaftarMasterDataKodeAkunting(dataJSON.parameterOrder);
          notifikasi(dataJSON.notifikasi);
        }
      });
    } 
    else{
      swal({
        title: "Proses pembatalan kodeakunting dibatalkan!",
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
    toastr.success('Proses Data KodeAkunting Berhasil');
  }
  else if(sukses == 2){
    toastr.error('Proses Data KodeAkunting Gagal');
  }
}
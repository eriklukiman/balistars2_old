$('select.select2').select2();
$("#rentang").daterangepicker({
  locale: {
      format: 'DD-MM-YYYY'
  }
});

function dataDaftarMasterDataTarget(parameterOrder){
  $.ajax({
    url:'datadaftarmasterdatatarget.php',
    type:'post',
    data:{
      parameterOrder : parameterOrder,
      idJenisPenjualan : $('#idJenisPenjualan').val(),
      rentang : $('#rentang').val()
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarMasterDataTarget').empty().append(data);
      $('#parameterOrder').val(parameterOrder);
      $('.overlay').hide();
    }
  });
}

function tambahTarget() {
  $("#modalFormMasterDataTarget").modal('show');

  $.ajax({
    url:'dataformmasterdatatarget.php',
    type:'post',
    data:{
      parameterOrder  : $('#parameterOrder').val(),
      flag            : ''
    },
    beforeSend:function(){
    },
    success:function(data,status){
      $("#dataFormMasterDataTarget").html(data);
      $('select.select2').select2();
    }
  });
}

function editTarget(id) {
  $("#modalFormMasterDataTarget").modal('show');

    $.ajax({
      url:'dataformmasterdatatarget.php',
      type:'post',
      data:{
        parameterOrder  : $('#parameterOrder').val(),
        idTarget       : id,
        flag            : 'update'
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormMasterDataTarget").html(data);
        $('select.select2').select2();
      }
    });
}


function prosesMasterDataTarget(){

  let formMasterDataTarget = document.getElementById('formMasterDataTarget');
  let dataForm           = new FormData(formMasterDataTarget);

  const validasi = formValidation(dataForm);
  if (validasi === true) {
    $.ajax({
      url:'prosesmasterdatatarget.php',
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
        dataDaftarMasterDataTarget(dataJSON.parameterOrder);
        notifikasi(dataJSON.notifikasi);
        resetForm(dataJSON.notifikasi);
        if(dataJSON.flag == 'update'){
          $("#modalFormMasterDataTarget").modal('hide');
        }
      }
    });
  }
  else {
     notifikasi(2, "Proses Gagal, Form Belum Terisi Dengan Lengkap");
  }
}

function cancelTarget(id){
  swal({
    title: "Apakah anda yakin ingin membatalkan data Target ini?",
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
        url:'prosesmasterdatatarget.php',
        type:'post',
        data:{
          idTarget  : id,
          parameterOrder : parameterOrder,
          flag           : flag
        },

        success:function(data,status){
          let dataJSON=JSON.parse(data);
          dataDaftarMasterDataTarget(dataJSON.parameterOrder);
          notifikasi(dataJSON.notifikasi);
        }
      });
    } 
    else{
      swal({
        title: "Proses pembatalan Target dibatalkan!",
        icon: "warning"
      });
    }
  });
}

function resetForm(sukses){
  if(sukses == 1){
    $('input[type=text]').val('');
    $('#flag').val('');
    $('#cabang').val(null).trigger('change');
    $('#jenis').val(null).trigger('change');
    //$('.select2').val(null).trigger('change');
  }
}

function notifikasi(sukses){
  if(sukses == 1){
    toastr.success('Proses Data Target Berhasil');
  }
  else if(sukses == 2){
    toastr.error('Proses Data Target Gagal');
  }
}
function dataDaftarMasterDataSupplier(parameterOrder){
  $.ajax({
    url:'datadaftarmasterdatasupplier.php',
    type:'post',
    data:{
      parameterOrder : parameterOrder
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarMasterDataSupplier').empty().append(data);
      $('#parameterOrder').val(parameterOrder);
      $('.overlay').hide();
    }
  });
}

function tambahSupplier() {
  $("#modalFormMasterDataSupplier").modal('show');

  $.ajax({
    url:'dataformmasterdatasupplier.php',
    type:'post',
    data:{
      parameterOrder  : $('#parameterOrder').val(),
      flag            : ''
    },
    beforeSend:function(){
    },
    success:function(data,status){
      $("#dataFormMasterDataSupplier").html(data);
      $('select.select2').select2();
    }
  });
}

function editSupplier(id) {
  $("#modalFormMasterDataSupplier").modal('show');

    $.ajax({
      url:'dataformmasterdatasupplier.php',
      type:'post',
      data:{
        parameterOrder  : $('#parameterOrder').val(),
        idSupplier       : id,
        flag            : 'update'
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormMasterDataSupplier").html(data);
        $('select.select2').select2();
      }
    });
}


function prosesMasterDataSupplier(){

  let formMasterDataSupplier = document.getElementById('formMasterDataSupplier');
  let dataForm           = new FormData(formMasterDataSupplier);

  const validasi = formValidation(dataForm);
  if (validasi === true) {
    $.ajax({
      url:'prosesmasterdatasupplier.php',
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
        dataDaftarMasterDataSupplier(dataJSON.parameterOrder);
        notifikasi(dataJSON.notifikasi);
        resetForm(dataJSON.notifikasi);
        if(dataJSON.flag == 'update'){
          $("#modalFormMasterDataSupplier").modal('hide');
        }
      }
    });
  } 
  else {
    notifikasi(2, "Proses Gagal, Form Belum Terisi Dengan Lengkap");
  }
}

function cancelSupplier(id){
  swal({
    title: "Apakah anda yakin ingin membatalkan data Supplier ini?",
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
        url:'prosesmasterdatasupplier.php',
        type:'post',
        data:{
          idSupplier  : id,
          parameterOrder : parameterOrder,
          flag           : flag
        },

        success:function(data,status){
          let dataJSON=JSON.parse(data);
          dataDaftarMasterDataSupplier(dataJSON.parameterOrder);
          notifikasi(dataJSON.notifikasi);
        }
      });
    } 
    else{
      swal({
        title: "Proses pembatalan Supplier dibatalkan!",
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
    toastr.success('Proses Data Supplier Berhasil');
  }
  else if(sukses == 2){
    toastr.error('Proses Data Supplier Gagal');
  }
}
function dataDaftarMasterDataCustomer(parameterOrder){
  $.ajax({
    url:'datadaftarmasterdatacustomer.php',
    type:'post',
    data:{
      parameterOrder : parameterOrder
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarMasterDataCustomer').empty().append(data);
      $('#parameterOrder').val(parameterOrder);
      $('.overlay').hide();
    }
  });
}

function tambahCustomer() {
  $("#modalFormMasterDataCustomer").modal('show');

  $.ajax({
    url:'dataformmasterdatacustomer.php',
    type:'post',
    data:{
      parameterOrder  : $('#parameterOrder').val(),
      flag            : ''
    },
    beforeSend:function(){
    },
    success:function(data,status){
      $("#dataFormMasterDataCustomer").html(data);
      $('select.select2').select2();
    }
  });
}

function editCustomer(id) {
  $("#modalFormMasterDataCustomer").modal('show');

    $.ajax({
      url:'dataformmasterdatacustomer.php',
      type:'post',
      data:{
        parameterOrder  : $('#parameterOrder').val(),
        idCustomer       : id,
        flag            : 'update'
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormMasterDataCustomer").html(data);
        $('select.select2').select2();
      }
    });
}


function prosesMasterDataCustomer(){

  let formMasterDataCustomer = document.getElementById('formMasterDataCustomer');
  let dataForm           = new FormData(formMasterDataCustomer);

  const validasi = formValidation(dataForm,["NPWP"]);
  if (validasi === true) {
    $.ajax({
      url:'prosesmasterdatacustomer.php',
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
        dataDaftarMasterDataCustomer(dataJSON.parameterOrder);
        notifikasi(dataJSON.notifikasi);
        resetForm(dataJSON.notifikasi);
        if(dataJSON.flag == 'update'){
          $("#modalFormMasterDataCustomer").modal('hide');
        }
      }
    });
  } 
  else {
    notifikasi(2, "Proses Gagal, Form Belum Terisi Dengan Lengkap");
  }
}

function cancelCustomer(id){
  swal({
    title: "Apakah anda yakin ingin membatalkan data Customer ini?",
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
        url:'prosesmasterdatacustomer.php',
        type:'post',
        data:{
          idCustomer  : id,
          parameterOrder : parameterOrder,
          flag           : flag
        },

        success:function(data,status){
          let dataJSON=JSON.parse(data);
          dataDaftarMasterDataCustomer(dataJSON.parameterOrder);
          notifikasi(dataJSON.notifikasi);
        }
      });
    } 
    else{
      swal({
        title: "Proses pembatalan Customer dibatalkan!",
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
    toastr.success('Proses Data Customer Berhasil');
  }
  else if(sukses == 2){
    toastr.error('Proses Data Customer Gagal');
  }
}
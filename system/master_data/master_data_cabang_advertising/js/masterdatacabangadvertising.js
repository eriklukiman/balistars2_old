function dataDaftarMasterDataCabangAdvertising(parameterOrder){
  $.ajax({
    url:'datadaftarmasterdatacabangadvertising.php',
    type:'post',
    data:{
      parameterOrder : parameterOrder
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarMasterDataCabangAdvertising').empty().append(data);
      $('#parameterOrder').val(parameterOrder);
      $('.overlay').hide();
    }
  });
}

function tambahCabangAdvertising() {
  $("#modalFormMasterDataCabangAdvertising").modal('show');

  $.ajax({
    url:'dataformmasterdatacabangadvertising.php',
    type:'post',
    data:{
      parameterOrder  : $('#parameterOrder').val(),
      flag            : ''
    },
    beforeSend:function(){
    },
    success:function(data,status){
      $("#dataFormMasterDataCabangAdvertising").html(data);
      $('select.select2').select2();
    }
  });
}

function editCabangAdvertising(id) {
  $("#modalFormMasterDataCabangAdvertising").modal('show');

    $.ajax({
      url:'dataformmasterdatacabangadvertising.php',
      type:'post',
      data:{
        parameterOrder  : $('#parameterOrder').val(),
        idCabang       : id,
        flag            : 'update'
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormMasterDataCabangAdvertising").html(data);
        $('select.select2').select2();
      }
    });
}


function prosesMasterDataCabangAdvertising(){

  let formMasterDataCabangAdvertising = document.getElementById('formMasterDataCabangAdvertising');
  let dataForm           = new FormData(formMasterDataCabangAdvertising);

  const validasi = formValidation(dataForm);
  if (validasi === true) {
    $.ajax({
      url:'prosesmasterdatacabangadvertising.php',
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
        dataDaftarMasterDataCabangAdvertising(dataJSON.parameterOrder);
        notifikasi(dataJSON.notifikasi);
        resetForm(dataJSON.notifikasi);
        if(dataJSON.flag == 'update'){
          $("#modalFormMasterDataCabangAdvertising").modal('hide');
        }
      }
    });
  }
  else {
     notifikasi(2, "Proses Gagal, Form Belum Terisi Dengan Lengkap");
  }
}

function cancelCabangAdvertising(id){
  swal({
    title: "Apakah anda yakin ingin membatalkan data Cabang Advertising ini?",
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
        url:'prosesmasterdatacabangadvertising.php',
        type:'post',
        data:{
          idCabang  : id,
          parameterOrder : parameterOrder,
          flag           : flag
        },

        success:function(data,status){
          let dataJSON=JSON.parse(data);
          dataDaftarMasterDataCabangAdvertising(dataJSON.parameterOrder);
          notifikasi(dataJSON.notifikasi);
        }
      });
    } 
    else{
      swal({
        title: "Proses pembatalan Cabang Advertising dibatalkan!",
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
    toastr.success('Proses Data Cabang Advertising Berhasil');
  }
  else if(sukses == 2){
    toastr.error('Proses Data Cabang Advertising Gagal');
  }
}
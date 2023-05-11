    $("#rentang").daterangepicker({
      locale: {
          format: 'DD-MM-YYYY'
      }
    });

function dataDaftarTransferBank(parameterOrder){
  $.ajax({
    url:'datadaftartransferbank.php',
    type:'post',
    data:{
      parameterOrder : parameterOrder,
      tipe : $('#tipe').val(),
      rentang : $('#rentang').val()
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarTransferBank').empty().append(data);
      $('#parameterOrder').val(parameterOrder);
      $('.overlay').hide();
    }
  });
}

function tambahTransferBank() {
  $("#modalFormTransferBank").modal('show');

  $.ajax({
    url:'dataformtransferbank.php',
    type:'post',
    data:{
      parameterOrder  : $('#parameterOrder').val(),
      tipe : $('#tipe').val(),
      flag            : ''
    },
    beforeSend:function(){
    },
    success:function(data,status){
      $("#dataFormTransferBank").html(data);
      $('select.select2').select2();
    }
  });
}

function editTransferBank(id) {
  $("#modalFormTransferBank").modal('show');

    $.ajax({
      url:'dataformtransferbank.php',
      type:'post',
      data:{
        parameterOrder  : $('#parameterOrder').val(),
        idTransferBank       : id,
        tipe : $('#tipe').val(),
        flag            : 'update'
      },
      beforeSend:function(){
      },
      success:function(data,status){
        //console.log(data);
        $("#dataFormTransferBank").html(data);
        $('select.select2').select2();
      }
    });
}


function prosesTransferBank(){

  let formTransferBank = document.getElementById('formTransferBank');
  let dataForm           = new FormData(formTransferBank);

  const validasi = formValidation(dataForm);
  if (validasi === true) {
    $.ajax({
      url:'prosestransferbank.php',
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
          dataDaftarTransferBank(dataJSON.parameterOrder);
          notifikasi(dataJSON.notifikasi);
          resetForm(dataJSON.notifikasi);
          if(dataJSON.flag == 'update'){
            $("#modalFormTransferBank").modal('hide');
          }
      }
    });
  }
  else {
     notifikasi(2, "Proses Gagal, Form Belum Terisi Dengan Lengkap");
  }
}

function finalTransferBank(id){
  swal({
    title: "Apakah anda yakin ingin Melakukan Finalisasi Transfer Antar Bank ini?",
    //text: "Setelah dibatalkan, proses tidak dapat diulangi!",
    icon: "warning",
    buttons: true,
    dangerMode: true,
  })
  .then((willDelete) => {
    if(willDelete){

      let parameterOrder = $('#parameterOrder').val();
      let flag           = 'final';
      
      $.ajax({
        url:'prosestransferbank.php',
        type:'post',
        data:{
          idTransferBank  : id,
          parameterOrder : parameterOrder,
          flag           : flag
        },

        success:function(data,status){
          let dataJSON=JSON.parse(data);
          dataDaftarTransferBank(dataJSON.parameterOrder);
          notifikasi(dataJSON.notifikasi);
        }
      });
    } 
    else{
      swal({
        title: "Proses pembatalan Finalisasi!",
        icon: "warning"
      });
    }
  });
}

function bukaTransferBank(id){
  let parameterOrder = $('#parameterOrder').val();
  let flag           = 'buka';
  
  $.ajax({
    url:'prosestransferbank.php',
    type:'post',
    data:{
      idTransferBank  : id,
      parameterOrder : parameterOrder,
      flag           : flag
    },

    success:function(data,status){
      let dataJSON=JSON.parse(data);
      dataDaftarTransferBank(dataJSON.parameterOrder);
      notifikasi(dataJSON.notifikasi);
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
    toastr.success('Proses Data Transfer Antar Bank Berhasil');
  }
  else if(sukses == 2){
    toastr.error('Proses Data Transfer Antar Bank Gagal');
  }
}
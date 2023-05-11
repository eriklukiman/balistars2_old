function dataDaftarMasterDataPegawai(parameterOrder){
  $.ajax({
    url:'datadaftarmasterdatapegawai.php',
    type:'post',
    data:{
      parameterOrder : parameterOrder
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarMasterDataPegawai').empty().append(data);
      $('#parameterOrder').val(parameterOrder);
      $('.overlay').hide();
    }
  });
}

function tambahPegawai() {
  $("#modalFormMasterDataPegawai").modal('show');

  $.ajax({
    url:'dataformmasterdatapegawai.php',
    type:'post',
    data:{
      parameterOrder  : $('#parameterOrder').val(),
      flag            : ''
    },
    beforeSend:function(){
    },
    success:function(data,status){
      $("#dataFormMasterDataPegawai").html(data);
      $('select.select2').select2();
    }
  });
}

function editPegawai(id) {
  $("#modalFormMasterDataPegawai").modal('show');

    $.ajax({
      url:'dataformmasterdatapegawai.php',
      type:'post',
      data:{
        parameterOrder  : $('#parameterOrder').val(),
        idPegawai       : id,
        flag            : 'update'
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormMasterDataPegawai").html(data);
        $('select.select2').select2();
      }
    });
}


function prosesMasterDataPegawai(){

  let formMasterDataPegawai = document.getElementById('formMasterDataPegawai');
  let dataForm           = new FormData(formMasterDataPegawai);

  const validasi = formValidation(dataForm,["idCabangAdvertising"]);
  if (validasi === true) {
    $.ajax({
      url:'prosesmasterdatapegawai.php',
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
        dataDaftarMasterDataPegawai(dataJSON.parameterOrder);
        notifikasi(dataJSON.notifikasi);
        resetForm(dataJSON.notifikasi);
        if(dataJSON.flag == 'update'){
          $("#modalFormMasterDataPegawai").modal('hide');
        }
      }
    });
  }
  else {
     notifikasi(2, "Proses Gagal, Form Belum Terisi Dengan Lengkap");
  }
}

function cencelPegawai(id){
  swal({
    title: "Apakah anda yakin ingin membatalkan data pegawai ini?",
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
        url:'prosesmasterdatapegawai.php',
        type:'post',
        data:{
          idPegawai  : id,
          parameterOrder : parameterOrder,
          flag           : flag
        },

        success:function(data,status){
          let dataJSON=JSON.parse(data);
          dataDaftarMasterDataPegawai(dataJSON.parameterOrder);
          notifikasi(dataJSON.notifikasi);
        }
      });
    } 
    else{
      swal({
        title: "Proses pembatalan pegawai dibatalkan!",
        icon: "warning"
      });
    }
  });
}

function resetForm(sukses){
  if(sukses == 1){
    $('input[type=text]').val('');
    $('#flag').val('');
    $('#alamatPegawai').val('');
    $('.select2').val(null).trigger('change');
  }
}

function notifikasi(sukses){
  if(sukses == 1){
    toastr.success('Proses Data Pegawai Berhasil');
  }
  else if(sukses == 2){
    toastr.error('Proses Data Pegawai Gagal');
  }
}
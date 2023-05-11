function dataDaftarMasterDataKodePemasukan(parameterOrder){
  $.ajax({
    url:'datadaftarmasterdatakodepemasukan.php',
    type:'post',
    data:{
      parameterOrder : parameterOrder
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarMasterDataKodePemasukan').empty().append(data);
      $('#parameterOrder').val(parameterOrder);
      $('.overlay').hide();
    }
  });
}

function tambahKodePemasukan() {
  $("#modalFormMasterDataKodePemasukan").modal('show');

  $.ajax({
    url:'dataformmasterdatakodepemasukan.php',
    type:'post',
    data:{
      parameterOrder  : $('#parameterOrder').val(),
      flag            : ''
    },
    beforeSend:function(){
    },
    success:function(data,status){
      $("#dataFormMasterDataKodePemasukan").html(data);
      $('select.select2').select2();
    }
  });
}

function editKodePemasukan(id) {
  $("#modalFormMasterDataKodePemasukan").modal('show');

    $.ajax({
      url:'dataformmasterdatakodepemasukan.php',
      type:'post',
      data:{
        parameterOrder  : $('#parameterOrder').val(),
        idKodePemasukan       : id,
        flag            : 'update'
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormMasterDataKodePemasukan").html(data);
        $('select.select2').select2();
      }
    });
}


function prosesMasterDataKodePemasukan(){

  let formMasterDataKodePemasukan = document.getElementById('formMasterDataKodePemasukan');
  let dataForm           = new FormData(formMasterDataKodePemasukan);

  const validasi = formValidation(dataForm);
  if (validasi === true) {
    $.ajax({
      url:'prosesmasterdatakodepemasukan.php',
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
        dataDaftarMasterDataKodePemasukan(dataJSON.parameterOrder);
        notifikasi(dataJSON.notifikasi);
        resetForm(dataJSON.notifikasi);
        if(dataJSON.flag == 'update'){
          $("#modalFormMasterDataKodePemasukan").modal('hide');
        }
      }
    });
  } 
  else {
    notifikasi(2, "Proses Gagal, Form Belum Terisi Dengan Lengkap");
  }
}

function cancelKodePemasukan(id){
  swal({
    title: "Apakah anda yakin ingin membatalkan data Kode Pemasukan ini?",
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
        url:'prosesmasterdatakodepemasukan.php',
        type:'post',
        data:{
          idKodePemasukan  : id,
          parameterOrder : parameterOrder,
          flag           : flag
        },

        success:function(data,status){
          let dataJSON=JSON.parse(data);
          dataDaftarMasterDataKodePemasukan(dataJSON.parameterOrder);
          notifikasi(dataJSON.notifikasi);
        }
      });
    } 
    else{
      swal({
        title: "Proses pembatalan Kode Pemasukan dibatalkan!",
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
    toastr.success('Proses Data Kode Pemasukan Berhasil');
  }
  else if(sukses == 2){
    toastr.error('Proses Data Kode Pemasukan Gagal');
  }
}
$("#rentang").daterangepicker({
      locale: {
          format: 'DD-MM-YYYY'
      }
    });

function dataDaftarMasterDataProduktivity(parameterOrder){
  $.ajax({
    url:'datadaftarmasterdataproduktivity.php',
    type:'post',
    data:{
      parameterOrder : parameterOrder,
      rentang : $('#rentang').val()
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarMasterDataProduktivity').empty().append(data);
      $('#parameterOrder').val(parameterOrder);
      $('.overlay').hide();
    }
  });
}

function tambahProduktivity() {
  $("#modalFormMasterDataProduktivity").modal('show');

  $.ajax({
    url:'dataformmasterdataproduktivity.php',
    type:'post',
    data:{
      parameterOrder  : $('#parameterOrder').val(),
      flag            : ''
    },
    beforeSend:function(){
    },
    success:function(data,status){
      $("#dataFormMasterDataProduktivity").html(data);
      $('select.select2').select2();
      $('.dateMulti').datepicker({
        format: 'yyyy-mm-dd',
        multidate: true
        });
      getJenisInput();
    }
  });
}


function prosesMasterDataProduktivity(){

  let formMasterDataProduktivity = document.getElementById('formMasterDataProduktivity');
  let dataForm           = new FormData(formMasterDataProduktivity);

  const validasi = formValidation(dataForm, ["hariLibur"]);
  if (validasi === true) {
    $.ajax({
      url:'prosesmasterdataproduktivity.php',
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
        dataDaftarMasterDataProduktivity(dataJSON.parameterOrder);
        notifikasi(dataJSON.notifikasi);
        resetForm(dataJSON.notifikasi);
        if(dataJSON.flag == 'update'){
          $("#modalFormMasterDataProduktivity").modal('hide');
        }
      }
    });
   }
  else {
     notifikasi(2, "Proses Gagal, Form Belum Terisi Dengan Lengkap");
  }
}

function editProduktivity(id) {
  $("#modalFormMasterDataProduktivity").modal('show');

    $.ajax({
      url:'dataformmasterdataproduktivity.php',
      type:'post',
      data:{
        parameterOrder  : $('#parameterOrder').val(),
        idProduktivity       : id,
        flag            : 'update'
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormMasterDataProduktivity").html(data);
        $('select.select2').select2();
        $('.dateMulti').datepicker({
        format: 'yyyy-mm-dd',
        multidate: true
        });
      }
    });
}

function cancelProduktivity(id){
  swal({
    title: "Apakah anda yakin ingin membatalkan data Produktivity ini?",
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
        url:'prosesmasterdataproduktivity.php',
        type:'post',
        data:{
          idProduktivity  : id,
          parameterOrder : parameterOrder,
          flag           : flag
        },

        success:function(data,status){
          let dataJSON=JSON.parse(data);
          dataDaftarMasterDataProduktivity(dataJSON.parameterOrder);
          notifikasi(dataJSON.notifikasi);
        }
      });
    } 
    else{
      swal({
        title: "Proses pembatalan Produktivity dibatalkan!",
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
    toastr.success('Proses Data Produktivity Berhasil');
  }
  else if(sukses == 2){
    toastr.error('Proses Data Produktivity Gagal');
  }
}

function getJenisInput() {
  var jenisInput = $('#jenisInput').val();
  if(jenisInput=='Penambahan'){
    $('#formPenambahan').show();
  }
  else{
    $('#formPenambahan').hide();
  }
}
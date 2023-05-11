function getDaftarRentang() {
  var parameterOrder = $('#parameterOrder').val();
  dataDaftarMasterDataAchievement(parameterOrder);
}

function dataDaftarMasterDataAchievement(parameterOrder){
  $.ajax({
    url:'datadaftarmasterdataachievementarea.php',
    type:'post',
    data:{
      parameterOrder : parameterOrder,
      rentang : $('#rentang').val()
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarMasterDataAchievement').empty().append(data);
      $('#parameterOrder').val(parameterOrder);
      $('.overlay').hide();
    }
  });
}

function tambahAchievement() {
  $("#modalFormMasterDataAchievement").modal('show');

  $.ajax({
    url:'dataformmasterdataachievementarea.php',
    type:'post',
    data:{
      parameterOrder  : $('#parameterOrder').val(),
      flag            : ''
    },
    beforeSend:function(){
    },
    success:function(data,status){
      $("#dataFormMasterDataAchievement").html(data);
      $('select.select2').select2();
      getJenisInput();
    }
  });
}


function prosesMasterDataAchievement(){

  let formMasterDataAchievement = document.getElementById('formMasterDataAchievement');
  let dataForm           = new FormData(formMasterDataAchievement);

  const validasi = formValidation(dataForm);
  if (validasi === true) {
    $.ajax({
      url:'prosesmasterdataachievementarea.php',
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
        dataDaftarMasterDataAchievement(dataJSON.parameterOrder);
        notifikasi(dataJSON.notifikasi);
        resetForm(dataJSON.notifikasi);
        if(dataJSON.flag == 'update'){
          $("#modalFormMasterDataAchievement").modal('hide');
        }
      }
    });
   }
  else {
     notifikasi(2, "Proses Gagal, Form Belum Terisi Dengan Lengkap");
  }
}

function editAchievement(id) {
  $("#modalFormMasterDataAchievement").modal('show');

    $.ajax({
      url:'dataformmasterdataachievementarea.php',
      type:'post',
      data:{
        parameterOrder  : $('#parameterOrder').val(),
        idAchievement       : id,
        flag            : 'update'
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormMasterDataAchievement").html(data);
        $('select.select2').select2();
      }
    });
}

function cancelAchievement(id){
  swal({
    title: "Apakah anda yakin ingin membatalkan data Achievement ini?",
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
        url:'prosesmasterdataachievementarea.php',
        type:'post',
        data:{
          idAchievement  : id,
          parameterOrder : parameterOrder,
          flag           : flag
        },

        success:function(data,status){
          let dataJSON=JSON.parse(data);
          dataDaftarMasterDataAchievement(dataJSON.parameterOrder);
          notifikasi(dataJSON.notifikasi);
        }
      });
    } 
    else{
      swal({
        title: "Proses pembatalan Achievement dibatalkan!",
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
    toastr.success('Proses Data Achievement Berhasil');
  }
  else if(sukses == 2){
    toastr.error('Proses Data Achievement Gagal');
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

$("#rentang").daterangepicker({
      locale: {
          format: 'DD-MM-YYYY'
      }
    });

function dataDaftarBiayaSub(){ 
  //console.log($('#tipe').val());
  $.ajax({
    url:'datadaftarbiayasub.php',
    type:'post',
    data:{
      rentang : $('#rentang').val()
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      //console.log(tipe);
      $('#dataDaftarBiayaSub').empty().append(data);
      $('.overlay').hide();
    }
  });
}

function inputBiayaSub(id) {
  //console.log($('#tipe').val());
   $("#modalFormBiayaSub").modal('show');
    $.ajax({
      url:'dataforminputbiayasub.php',
      type:'post',
      data:{
        idPenjualanDetail : id,
        rentang : $('#rentang').val(),
        parameterOrder : $('#parameterOrder').val(),
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormBiayaSub").html(data);
        $('select.select2').select2();
        getBiayaSubTersimpan(id);
      }
    });
}


function editInputBiayaSub(idBiaya,id) {
   $("#modalFormBiayaSub").modal('show');
    $.ajax({
      url:'dataforminputbiayasub.php',
      type:'post',
      data:{
        idBiaya: idBiaya,
        idPenjualanDetail : id,
        flag : 'update'
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormBiayaSub").empty().html(data);
        $('select.select2').select2();
        
        getBiayaSubTersimpan(id);
        //inputBiayaSub(id);
      }
    });
}

function deleteInputBiayaSub(idBiaya,id) {
   $("#modalFormBiayaSub").modal('show');
    $.ajax({
      url:'prosesbiayasub.php',
      type:'post',
      data:{
        idBiaya: idBiaya,
        idPenjualanDetail : id,
        flag : 'cancel'
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormBiayaSub").html(data);
        $('select.select2').select2();
        inputBiayaSub(id);
        
      }
    });
}



function getBiayaSubTersimpan(id){
  $.ajax({
    url:'datadaftarbiayasubtersimpan.php',
    type:'post',
    data:{
      idPenjualanDetail : id,
    },
    success:function(data,status){
      $('#dataDaftarBiayaSubTersimpan').empty().append(data);
      $('select.select2').select2();
      //$('#keterangan').focus();
    }
  });
}


function prosesInputBiayaSub(){

  let formBiayaSub = document.getElementById('formBiayaSub');
  let dataForm           = new FormData(formBiayaSub);

  const validasi = formValidation(dataForm);
  if (validasi === true) {
    $.ajax({
      url:'prosesbiayasub.php',
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
        notifikasi(dataJSON.notifikasi);
        inputBiayaSub(dataJSON.idPenjualanDetail);
      }
    });
  }
  else {
     notifikasi(2, "Proses Gagal, Form Belum Terisi Dengan Lengkap");
  }
}

function notifikasi(sukses){
  if(sukses == 1){
    toastr.success('Proses Data Berhasil');
  }
  else if(sukses == 2){
    toastr.error('Proses Data Gagal');
  }
}

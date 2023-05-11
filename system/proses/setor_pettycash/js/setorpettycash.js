$('select.select2').select2();

function dataDaftarSetorPettyCash(parameterOrder){
  console.log($('#idCabangSearch').val());
  $.ajax({
    url:'datadaftarsetorpettycash.php',
    type:'post',
    data:{
      parameterOrder : parameterOrder,
      rentang : $('#rentang').val(),
      idCabang : $('#idCabangSearch').val()
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarSetorPettyCash').empty().append(data);
      $('#parameterOrder').val(parameterOrder);
      $('.overlay').hide();
    }
  });
}

function bukaSetorPettyCash(id){
  //console.log(id);
  $.ajax({
    url:'prosessetorpettycash.php',
    type:'post',
    data:{
      idSetor : id,
      flag    : 'buka'
    },

    beforeSend:function(){
    },

    success:function(data,status){
      //console.log(data);
      let dataJSON = JSON.parse(data);
      dataDaftarSetorPettyCash(dataJSON.parameterOrder);
      notifikasi(dataJSON.notifikasi);
    }
  });
}

function finalSetorPettyCash(id){
  $.ajax({
    url:'prosessetorpettycash.php',
    type:'post',
    data:{
      idSetor : id,
      flag    : 'finalisasi'
    },

    beforeSend:function(){
    },

    success:function(data,status){
      //console.log(data);
      let dataJSON = JSON.parse(data);
      dataDaftarSetorPettyCash(dataJSON.parameterOrder);
      notifikasi(dataJSON.notifikasi);
    }
  });
}

function editSetorPettyCash(id) {
  //console.log(id);
  $("#modalFormSetorPettyCash").modal('show');

    $.ajax({
      url:'dataformsetorpettycash.php',
      type:'post',
      data:{
        idSetor : id,
        flag    : 'update'
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormSetorPettyCash").html(data);
        $('select.select2').select2();
      }
    });
}


function prosesSetorPettyCash(){

  let formSetorPettyCash = document.getElementById('formSetorPettyCash');
  let dataForm           = new FormData(formSetorPettyCash);

  const validasi = formValidation(dataForm);
  if (validasi === true) {
    $.ajax({
      url:'prosessetorpettycash.php',
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
        dataDaftarSetorPettyCash(dataJSON.parameterOrder);
        notifikasi(dataJSON.notifikasi);
        if(dataJSON.notifikasi == 1){
          $("#modalFormSetorPettyCash").modal('hide');
        }
      }
    });
   }
  else {
     notifikasi(2, "Proses Gagal, Form Belum Terisi Dengan Lengkap");
  }
}



function notifikasi(sukses){
  if(sukses == 1){
    toastr.success('Proses Data SetorPettyCash Berhasil');
  }
  else if(sukses == 2){
    toastr.error('Proses Data SetorPettyCash Gagal');
  }
}


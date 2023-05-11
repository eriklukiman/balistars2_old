$('select.select2').select2();

$("#rentang").daterangepicker({
      locale: {
          format: 'DD-MM-YYYY'
      }
    });

function dataDaftarPersediaanGlobal(parameterOrder){
  $.ajax({
    url:'datadaftarpersediaanglobal.php',
    type:'post',
    data:{
      parameterOrder : parameterOrder,
      idCabang   : $('#idCabangSearch').val(),
      rentang : $('#rentang').val()
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarPersediaanGlobal').empty().append(data);
      $('#parameterOrder').val(parameterOrder);
      $('.overlay').hide();
    }
  });
}

function tambahPersediaanGlobal() {
  $("#modalFormPersediaanGlobal").modal('show');

  $.ajax({
    url:'dataformpersediaanglobal.php',
    type:'post',
    data:{
      parameterOrder  : $('#parameterOrder').val(),
      flag            : ''
    },
    beforeSend:function(){
    },
    success:function(data,status){
      $("#dataFormPersediaanGlobal").html(data);
      $('select.select2').select2();
    }
  });
}


function prosesPersediaanGlobal(){

  let formPersediaanGlobal = document.getElementById('formPersediaanGlobal');
  let dataForm           = new FormData(formPersediaanGlobal);

  const validasi = formValidation(dataForm);
  if (validasi === true) {
    $.ajax({
      url:'prosespersediaanglobal.php',
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
        dataDaftarPersediaanGlobal(dataJSON.parameterOrder);
        notifikasi(dataJSON.notifikasi);
        if(dataJSON.flag == 'update'){
          $("#modalFormPersediaanGlobal").modal('hide');
        }
      }
    });
   }
  else {
     notifikasi(2, "Proses Gagal, Form Belum Terisi Dengan Lengkap");
  }
}

function editPersediaanGlobal(id) {
  //console.log(id);
  $("#modalFormPersediaanGlobal").modal('show');

    $.ajax({
      url:'dataformpersediaanglobal.php',
      type:'post',
      data:{
        parameterOrder  : $('#parameterOrder').val(),
        idPersediaan : id,
        flag            : 'update'
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormPersediaanGlobal").html(data);
        $('select.select2').select2();
      }
    });
}

function cancelPersediaanGlobal(id){
  swal({
    title: "Apakah anda yakin ingin membatalkan data PersediaanGlobal ini?",
    text: "Setelah dibatalkan, proses tidak dapat diulangi!",
    icon: "warning",
    buttons: true,
    dangerMode: true,
  })
  .then((willDelete) => {
    if(willDelete){
      
      $.ajax({
        url:'prosespersediaanglobal.php',
        type:'post',
        data:{
          idPersediaan  : id,
          parameterOrder : $('#parameterOrder').val(),
          flag           : 'cancel'
        },

        success:function(data,status){
          let dataJSON=JSON.parse(data);
          dataDaftarPersediaanGlobal(dataJSON.parameterOrder);
          notifikasi(dataJSON.notifikasi);
        }
      });
    } 
    else{
      swal({
        title: "Proses pembatalan Setor Petty Cash dibatalkan!",
        icon: "warning"
      });
    }
  });
}


function notifikasi(sukses){
  if(sukses == 1){
    toastr.success('Proses Data PersediaanGlobal Berhasil');
  }
  else if(sukses == 2){
    toastr.error('Proses Data PersediaanGlobal Gagal');
  }
}


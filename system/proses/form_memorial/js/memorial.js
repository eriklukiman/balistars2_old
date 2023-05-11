
function dataDaftarMemorial(parameterOrder){
  $.ajax({
    url:'datadaftarmemorial.php',
    type:'post',
    data:{
      parameterOrder : parameterOrder,
      rentang : $('#rentang').val()
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarMemorial').empty().append(data);
      $('#parameterOrder').val(parameterOrder);
      $('.overlay').hide();
    }
  });
}

function tambahMemorial() {
  $("#modalFormMemorial").modal('show');

  $.ajax({
    url:'dataformmemorial.php',
    type:'post',
    data:{
      parameterOrder  : $('#parameterOrder').val(),
      flag            : ''
    },
    beforeSend:function(){
    },
    success:function(data,status){
      $("#dataFormMemorial").html(data);
      $('select.select2').select2();
    }
  });
}


function prosesMemorial(){

  let formMemorial = document.getElementById('formMemorial');
  let dataForm           = new FormData(formMemorial);

  const validasi = formValidation(dataForm,["keterangan"]);
  if (validasi === true) {
    $.ajax({
      url:'prosesmemorial.php',
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
        dataDaftarMemorial(dataJSON.parameterOrder);
        notifikasi(dataJSON.notifikasi);
        resetForm(dataJSON.notifikasi);
        if(dataJSON.flag == 'update'){
          $("#modalFormMemorial").modal('hide');
        }
      }
    });
   }
  else {
     notifikasi(2, "Proses Gagal, Form Belum Terisi Dengan Lengkap");
  }
}

function editMemorial(id) {
  //console.log(id);
  $("#modalFormMemorial").modal('show');

    $.ajax({
      url:'dataformmemorial.php',
      type:'post',
      data:{
        parameterOrder  : $('#parameterOrder').val(),
        idMemorial : id,
        flag            : 'update'
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormMemorial").html(data);
        $('select.select2').select2();
      }
    });
}

function cancelMemorial(id){
  swal({
    title: "Apakah anda yakin ingin membatalkan data Memorial ini?",
    text: "Setelah dibatalkan, proses tidak dapat diulangi!",
    icon: "warning",
    buttons: true,
    dangerMode: true,
  })
  .then((willDelete) => {
    if(willDelete){
      
      $.ajax({
        url:'prosesmemorial.php',
        type:'post',
        data:{
          idMemorial  : id,
          parameterOrder : $('#parameterOrder').val(),
          flag           : 'cancel'
        },

        success:function(data,status){
          let dataJSON=JSON.parse(data);
          dataDaftarMemorial(dataJSON.parameterOrder);
          notifikasi(dataJSON.notifikasi);
        }
      });
    } 
    else{
      swal({
        title: "Proses pembatalan Memorial dibatalkan!",
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
    toastr.success('Proses Data Memorial Berhasil');
  }
  else if(sukses == 2){
    toastr.error('Proses Data Memorial Gagal');
  }
}


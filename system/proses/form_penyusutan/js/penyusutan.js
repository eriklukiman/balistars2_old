$("#rentang").daterangepicker({
  locale : {
    format : 'DD-MM-YYYY'
  }
});


function dataDaftarPenyusutan(parameterOrder){
  $.ajax({
    url:'datadaftarpenyusutan.php',
    type:'post',
    data:{
      parameterOrder : parameterOrder,
      rentang        : $('#rentang').val()
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarPenyusutan').empty().append(data);
      $('#parameterOrder').val(parameterOrder);
      $('.overlay').hide();
    }
  });
}

function tambahPenyusutan() {
  $("#modalFormPenyusutan").modal('show');

  $.ajax({
    url:'dataformpenyusutan.php',
    type:'post',
    data:{
      parameterOrder  : $('#parameterOrder').val(),
      flag            : ''
    },
    beforeSend:function(){
    },
    success:function(data,status){
      $("#dataFormPenyusutan").html(data);
      $('select.select2').select2();
    }
  });
}

function editPenyusutan(id) {
  $("#modalFormPenyusutan").modal('show');

    $.ajax({
      url:'dataformpenyusutan.php',
      type:'post',
      data:{
        parameterOrder  : $('#parameterOrder').val(),
        idPenyusutan       : id,
        flag            : 'update'
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormPenyusutan").html(data);
        $('select.select2').select2();
      }
    });
}


function prosesPenyusutan(){

  let formPenyusutan = document.getElementById('formPenyusutan');
  let dataForm           = new FormData(formPenyusutan);

  const validasi = formValidation(dataForm);
  if (validasi === true) {
    $.ajax({
      url:'prosespenyusutan.php',
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
        dataDaftarPenyusutan(dataJSON.parameterOrder);
        notifikasi(dataJSON.notifikasi);
        tambahPenyusutan();
        if(dataJSON.flag == 'update'){
          $("#modalFormPenyusutan").modal('hide');
        }
      }
    });
  }
  else {
     notifikasi(2, "Proses Gagal, Form Belum Terisi Dengan Lengkap");
  }
}

function cancelPenyusutan(id){
  swal({
    title: "Apakah anda yakin ingin membatalkan data Penyusutan ini?",
    text: "Setelah dibatalkan, proses tidak dapat diulangi!",
    icon: "warning",
    buttons: true,
    dangerMode: true,
  })
  .then((willDelete) => {
    if(willDelete){
      
      $.ajax({
        url:'prosespenyusutan.php',
        type:'post',
        data:{
          idPenyusutan  : id,
          parameterOrder : $('#parameterOrder').val(),
          flag           : 'cancel'
        },

        success:function(data,status){
          let dataJSON=JSON.parse(data);
          dataDaftarPenyusutan(dataJSON.parameterOrder);
          notifikasi(dataJSON.notifikasi);
        }
      });
    } 
    else{
      swal({
        title: "Proses pembatalan Penyusutan dibatalkan!",
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
    toastr.success('Proses Data Berhasil');
  }
  else if(sukses == 2){
    toastr.error('Proses Data Gagal');
  }
}
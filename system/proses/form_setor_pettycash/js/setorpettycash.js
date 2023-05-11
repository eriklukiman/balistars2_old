
function dataDaftarSetorPettyCash(parameterOrder){
  $.ajax({
    url:'datadaftarsetorpettycash.php',
    type:'post',
    data:{
      parameterOrder : parameterOrder,
      rentang : $('#rentang').val()
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

function tambahSetorPettyCash() {
  $("#modalFormSetorPettyCash").modal('show');

  $.ajax({
    url:'dataformsetorpettycash.php',
    type:'post',
    data:{
      parameterOrder  : $('#parameterOrder').val(),
      flag            : ''
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

  const validasi = formValidation(dataForm,["keterangan"]);
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
        resetForm(dataJSON.notifikasi);
        if(dataJSON.flag == 'update'){
          $("#modalFormSetorPettyCash").modal('hide');
        }
      }
    });
   }
  else {
     notifikasi(2, "Proses Gagal, Form Belum Terisi Dengan Lengkap");
  }
}

function editSetorPettyCash(id) {
  //console.log(id);
  $("#modalFormSetorPettyCash").modal('show');

    $.ajax({
      url:'dataformsetorpettycash.php',
      type:'post',
      data:{
        parameterOrder  : $('#parameterOrder').val(),
        idSetorKasKecil : id,
        flag            : 'update'
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormSetorPettyCash").html(data);
        $('select.select2').select2();
      }
    });
}

function cancelSetorPettyCash(id){
  swal({
    title: "Apakah anda yakin ingin membatalkan data SetorPettyCash ini?",
    text: "Setelah dibatalkan, proses tidak dapat diulangi!",
    icon: "warning",
    buttons: true,
    dangerMode: true,
  })
  .then((willDelete) => {
    if(willDelete){
      
      $.ajax({
        url:'prosessetorpettycash.php',
        type:'post',
        data:{
          idSetorKasKecil  : id,
          parameterOrder : $('#parameterOrder').val(),
          flag           : 'cancel'
        },

        success:function(data,status){
          let dataJSON=JSON.parse(data);
          dataDaftarSetorPettyCash(dataJSON.parameterOrder);
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

function resetForm(sukses){
  if(sukses == 1){
    $('input[type=text]').val('');
    $('#flag').val('');
    $('.select2').val(null).trigger('change');
    
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


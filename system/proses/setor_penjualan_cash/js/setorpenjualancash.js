$("#rentang").daterangepicker({
  locale: {
      format: 'DD-MM-YYYY'
  }
});

$('select.select2').select2();

function dataDaftarSetorPenjualanCash(parameterOrder){
  $.ajax({
    url:'datadaftarsetorpenjualancash.php',
    type:'post',
    data:{
      parameterOrder : parameterOrder,
      rentang : $('#rentang').val(),
      idCabang : $('#idCabang').val(),
      tipe     : $('#tipe').val()
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarSetorPenjualanCash').empty().append(data);
      $('#parameterOrder').val(parameterOrder);
      $('.overlay').hide();
    }
  });
}

function bukaSetorPenjualanCash(id){
  //console.log(id);
  $.ajax({
    url:'prosessetorpenjualancash.php',
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
      dataDaftarSetorPenjualanCash(dataJSON.parameterOrder);
      notifikasi(dataJSON.notifikasi);
    }
  });
}

function finalSetorPenjualanCash(id){
  $.ajax({
    url:'prosessetorpenjualancash.php',
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
      dataDaftarSetorPenjualanCash(dataJSON.parameterOrder);
      notifikasi(dataJSON.notifikasi);
    }
  });
}

function editSetorPenjualanCash(id) {
  //console.log(id);
  $("#modalFormSetorPenjualanCash").modal('show');

    $.ajax({
      url:'dataformsetorpenjualancash.php',
      type:'post',
      data:{
        idSetor : id,
        flag            : 'update'
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormSetorPenjualanCash").html(data);
        $('select.select2').select2();
      }
    });
}

function prosesSetorPenjualanCash(){

  let formSetorPenjualanCash = document.getElementById('formSetorPenjualanCash');
  let dataForm           = new FormData(formSetorPenjualanCash);

  const validasi = formValidation(dataForm,["keterangan"]);
  if (validasi === true) {
    $.ajax({
      url:'prosessetorpenjualancash.php',
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
        dataDaftarSetorPenjualanCash(dataJSON.parameterOrder);
        notifikasi(dataJSON.notifikasi);
        if(dataJSON.notifikasi==1){
          $("#modalFormSetorPenjualanCash").modal('hide');
        }
      }
    });
   }
  else {
     notifikasi(2, "Proses Gagal, Form Belum Terisi Dengan Lengkap");
  }
}


function cancelSetorPenjualanCash(id){
  swal({
    title: "Apakah anda yakin ingin membatalkan data SetorPenjualanCash ini?",
    text: "Setelah dibatalkan, proses tidak dapat diulangi!",
    icon: "warning",
    buttons: true,
    dangerMode: true,
  })
  .then((willDelete) => {
    if(willDelete){
      
      $.ajax({
        url:'prosessetorpenjualancash.php',
        type:'post',
        data:{
          idOrderKasKecil  : id,
          parameterOrder : $('#parameterOrder').val(),
          flag           : 'cancel'
        },

        success:function(data,status){
          let dataJSON=JSON.parse(data);
          dataDaftarSetorPenjualanCash(dataJSON.parameterOrder);
          notifikasi(dataJSON.notifikasi);
        }
      });
    } 
    else{
      swal({
        title: "Proses pembatalan Order Petty Cash dibatalkan!",
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
    toastr.success('Proses Data SetorPenjualanCash Berhasil');
  }
  else if(sukses == 2){
    toastr.error('Proses Data SetorPenjualanCash Gagal');
  }
}


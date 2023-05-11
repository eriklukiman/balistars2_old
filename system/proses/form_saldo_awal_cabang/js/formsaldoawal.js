function dataDaftarSaldoAwal(parameterOrder){
  $.ajax({
    url:'datadaftarsaldoawal.php',
    type:'post',
    data:{
      parameterOrder : parameterOrder
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarSaldoAwal').empty().append(data);
      $('#parameterOrder').val(parameterOrder);
      $('.overlay').hide();
    }
  });
}

function tambahSaldoAwal() {
  $("#modalFormSaldoAwal").modal('show');

  $.ajax({
    url:'dataformsaldoawal.php',
    type:'post',
    data:{
      parameterOrder  : $('#parameterOrder').val(),
      flag            : ''
    },
    beforeSend:function(){
    },
    success:function(data,status){
      $("#dataFormSaldoAwal").html(data);
      $('select.select2').select2();
    }
  });
}

function editSaldoAwal(id) {
  $("#modalFormSaldoAwal").modal('show');

    $.ajax({
      url:'dataformSaldoAwal.php',
      type:'post',
      data:{
        parameterOrder  : $('#parameterOrder').val(),
        idCabangCash       : id,
        flag            : 'update'
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormSaldoAwal").html(data);
        $('select.select2').select2();
      }
    });
}


function prosesSaldoAwal(){

  let formSaldoAwal = document.getElementById('formSaldoAwal');
  let dataForm           = new FormData(formSaldoAwal);

  const validasi = formValidation(dataForm);
  if (validasi === true) {
    $.ajax({
      url:'prosessaldoawal.php',
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
        dataDaftarSaldoAwal(dataJSON.parameterOrder);
        notifikasi(dataJSON.notifikasi);
        resetForm(dataJSON.notifikasi);
        if(dataJSON.flag == 'update'){
          $("#modalFormSaldoAwal").modal('hide');
        }
      }
    });
  }
  else {
     notifikasi(2, "Proses Gagal, Form Belum Terisi Dengan Lengkap");
  }
}

function finalSaldoAwal(id){
  swal({
    title: "Apakah anda yakin ingin Melakukan Finalisasi Saldo Awal ini?",
    //text: "Setelah dibatalkan, proses tidak dapat diulangi!",
    icon: "warning",
    buttons: true,
    dangerMode: true,
  })
  .then((willDelete) => {
    if(willDelete){

      let parameterOrder = $('#parameterOrder').val();
      let flag           = 'final';
      
      $.ajax({
        url:'prosessaldoawal.php',
        type:'post',
        data:{
          idCabangCash  : id,
          parameterOrder : parameterOrder,
          flag           : flag
        },

        success:function(data,status){
          let dataJSON=JSON.parse(data);
          dataDaftarSaldoAwal(dataJSON.parameterOrder);
          notifikasi(dataJSON.notifikasi);
        }
      });
    } 
    else{
      swal({
        title: "Proses pembatalan Finalisasi!",
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
    toastr.success('Proses Data Saldo Awal Berhasil');
  }
  else if(sukses == 2){
    toastr.error('Proses Data Saldo Awal Gagal');
  }
}
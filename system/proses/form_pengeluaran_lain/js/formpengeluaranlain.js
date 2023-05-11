$("#rentang").daterangepicker({
      locale: {
          format: 'YYYY-MM-DD'
      }
    });

function dataDaftarPengeluaranLain(){
  $.ajax({
    url:'datadaftarpengeluaranlain.php',
    type:'post',
    data:{
      tipe : $('#tipe').val(),
      rentang : $('#rentang').val()
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarPengeluaranLain').empty().append(data);
      $('.overlay').hide();
    }
  });
}

function tambahPengeluaranLain() {
  $("#modalFormPengeluaranLain").modal('show');

  $.ajax({
    url:'dataformpengeluaranlain.php',
    type:'post',
    data:{
      parameterOrder  : $('#parameterOrder').val(),
      tipe : $('#tipe').val(),
      flag            : ''
    },
    beforeSend:function(){
    },
    success:function(data,status){
      $("#dataFormPengeluaranLain").html(data);
      $('select.select2').select2();
    }
  });
}

function editPengeluaranLain(id) {
  console.log($('#tipe').val());
  $("#modalFormPengeluaranLain").modal('show');

    $.ajax({
      url:'dataformpengeluaranlain.php',
      type:'post',
      data:{
        parameterOrder  : $('#parameterOrder').val(),
        tipe : $('#tipe').val(),
        idPengeluaranLain       : id,
        flag            : 'update'
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormPengeluaranLain").html(data);
        $('select.select2').select2();
      }
    });
}


function prosesPengeluaranLain(){

  let formPengeluaranLain = document.getElementById('formPengeluaranLain');
  let dataForm           = new FormData(formPengeluaranLain);

  const validasi = formValidation(dataForm);
  if (validasi === true) {
    $.ajax({
      url:'prosespengeluaranlain.php',
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
        dataDaftarPengeluaranLain(dataJSON.parameterOrder);
        notifikasi(dataJSON.notifikasi);
        resetForm(dataJSON.notifikasi);
        if(dataJSON.flag == 'update'){
          $("#modalFormPengeluaranLain").modal('hide');
        }
      }
    });
  }
  else {
     notifikasi(2, "Proses Gagal, Form Belum Terisi Dengan Lengkap");
  }
}

function finalPengeluaranLain(id){
  swal({
    title: "Apakah anda yakin ingin Melakukan Finalisasi Pengeluaran Lain ini?",
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
        url:'prosespengeluaranlain.php',
        type:'post',
        data:{
          idPengeluaranLain  : id,
          parameterOrder : parameterOrder,
          flag           : flag
        },

        success:function(data,status){
          let dataJSON=JSON.parse(data);
          dataDaftarPengeluaranLain(dataJSON.parameterOrder);
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

function bukaPengeluaranLain(id){
  let parameterOrder = $('#parameterOrder').val();
  let flag           = 'buka';
  
  $.ajax({
    url:'prosespengeluaranlain.php',
    type:'post',
    data:{
      idPengeluaranLain  : id,
      parameterOrder : parameterOrder,
      flag           : flag
    },

    success:function(data,status){
      let dataJSON=JSON.parse(data);
      dataDaftarPengeluaranLain(dataJSON.parameterOrder);
      notifikasi(dataJSON.notifikasi);
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
    toastr.success('Proses Data Pengeluaran Lain Berhasil');
  }
  else if(sukses == 2){
    toastr.error('Proses Data Pengeluaran Lain Gagal');
  }
}
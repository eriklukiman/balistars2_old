$("#rentang").daterangepicker({
      locale: {
          format: 'DD-MM-YYYY'
      }
    });

function dataDaftarPemasukanLain(){
  //console.log(tipe);
  $.ajax({
    url:'datadaftarpemasukanlain.php',
    type:'post',
    data:{
      tipe : $('#tipe').val(),
      rentang : $('#rentang').val()
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarPemasukanLain').empty().append(data);
      $('.overlay').hide();
    }
  });
}

function tambahPemasukanLain() {
  $("#modalFormPemasukanLain").modal('show');

  $.ajax({
    url:'dataformpemasukanlain.php',
    type:'post',
    data:{
      tipe : $('#tipe').val(),
      flag            : ''
    },
    beforeSend:function(){
    },
    success:function(data,status){
      $("#dataFormPemasukanLain").html(data);
      $('select.select2').select2();
    }
  });
}

function editPemasukanLain(id) {
  $("#modalFormPemasukanLain").modal('show');

    $.ajax({
      url:'dataformpemasukanlain.php',
      type:'post',
      data:{
        parameterOrder  : $('#parameterOrder').val(),
        idPemasukanLain       : id,
        tipe : $('#tipe').val(),
        flag            : 'update'
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormPemasukanLain").html(data);
        $('select.select2').select2();
      }
    });
}


function prosesPemasukanLain(){

  let formPemasukanLain = document.getElementById('formPemasukanLain');
  let dataForm           = new FormData(formPemasukanLain);

  const validasi = formValidation(dataForm);
  if (validasi === true) {
    $.ajax({
      url:'prosespemasukanlain.php',
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
        dataDaftarPemasukanLain(dataJSON.tipe);
        notifikasi(dataJSON.notifikasi);
        resetForm(dataJSON.notifikasi);
        if(dataJSON.flag == 'update'){
          $("#modalFormPemasukanLain").modal('hide');
        }
      }
    });
  }
  else {
     notifikasi(2, "Proses Gagal, Form Belum Terisi Dengan Lengkap");
  }
}

function finalPemasukanLain(id){
  swal({
    title: "Apakah anda yakin ingin Melakukan Finalisasi Pemasukan Lain ini?",
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
        url:'prosespemasukanlain.php',
        type:'post',
        data:{
          idPemasukanLain  : id,
          parameterOrder : parameterOrder,
          flag           : flag
        },

        success:function(data,status){
          let dataJSON=JSON.parse(data);
          dataDaftarPemasukanLain(dataJSON.parameterOrder);
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

function bukaPemasukanLain(id){
  let parameterOrder = $('#parameterOrder').val();
  let flag           = 'buka';
  
  $.ajax({
    url:'prosespemasukanlain.php',
    type:'post',
    data:{
      idPemasukanLain  : id,
      parameterOrder : parameterOrder,
      flag           : flag
    },

    success:function(data,status){
      let dataJSON=JSON.parse(data);
      dataDaftarPemasukanLain(dataJSON.parameterOrder);
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
    toastr.success('Proses Data Pemasukan Lain Berhasil');
  }
  else if(sukses == 2){
    toastr.error('Proses Data Pemasukan Lain Gagal');
  }
}
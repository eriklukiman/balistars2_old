$("select.select2").select2();
$("#rentang").daterangepicker({
  locale: {
      format: 'DD-MM-YYYY'
  }
});

function dataDaftarAbsensiDatang(parameterOrder){
  $.ajax({
    url:'datadaftarabsensidatang.php',
    type:'post',
    data:{
      parameterOrder : parameterOrder,
      rentang        : $('#rentang').val(),
      idCabang       : $('#idCabang').val()
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarAbsensiDatang').empty().append(data);
      $('#parameterOrder').val(parameterOrder);
      $('.overlay').hide();
    }
  });
}

function tambahAbsensiDatang() {
  $("#modalFormAbsensiDatang").modal('show');

  $.ajax({
    url:'dataformabsensidatang.php',
    type:'post',
    data:{
      parameterOrder  : $('#parameterOrder').val(),
      flag            : ''
    },
    beforeSend:function(){
    },
    success:function(data,status){
      $("#dataFormAbsensiDatang").html(data);
      $('select.select2').select2();
    }
  });
}

function showDataPegawai(id) {
  $("#modalFormAbsensiDatang").modal('show');

    $.ajax({
      url:'datapegawai.php',
      type:'post',
      data:{
        NIK  : $('#NIK').val()
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataPegawai").html(data);
        $('select.select2').select2();
      }
    });
}

function prosesAbsensiDatang(){

  let formAbsensiDatang = document.getElementById('formAbsensiDatang');
  let dataForm           = new FormData(formAbsensiDatang);

  const validasi = formValidation(dataForm);
  if (validasi === true) {
    $.ajax({
      url:'prosesabsensidatang.php',
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
        dataDaftarAbsensiDatang(dataJSON.parameterOrder);
        notifikasi(dataJSON.notifikasi);
        tambahAbsensiDatang();
        if(dataJSON.flag == 'update'){
          $("#modalFormAbsensiDatang").modal('hide');
        }
      }
    });
  }
  else {
     notifikasi(2, "Proses Gagal, Form Belum Terisi Dengan Lengkap");
  }
}

function cancelAbsensi(id){
  swal({
    title: "Apakah anda yakin ingin membatalkan data Absensi ini?",
    text: "Setelah dibatalkan, proses tidak dapat diulangi!",
    icon: "warning",
    buttons: true,
    dangerMode: true,
  })
  .then((willDelete) => {
    if(willDelete){

      let parameterOrder = $('#parameterOrder').val();
      let flag           = 'cancel';
      
      $.ajax({
        url:'prosesabsensidatang.php',
        type:'post',
        data:{
          idAbsensi  : id,
          parameterOrder : parameterOrder,
          flag           : flag
        },

        success:function(data,status){
          let dataJSON=JSON.parse(data);
          dataDaftarAbsensiDatang(dataJSON.parameterOrder);
          notifikasi(dataJSON.notifikasi);
        }
      });
    } 
    else{
      swal({
        title: "Proses pembatalan Absensi dibatalkan!",
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
    toastr.success('Proses Data Absensi Berhasil');
  }
  else if(sukses == 2){
    toastr.error('Proses Data Absensi Gagal, Data Telah Tersimpan');
  }
  else if(sukses == 3){
    toastr.error('Proses Data Gagal, sfit Belum Dipilih');
  }
}
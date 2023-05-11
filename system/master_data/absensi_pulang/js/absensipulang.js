$("select.select2").select2();
$("#rentang").daterangepicker({
  locale: {
      format: 'DD-MM-YYYY'
  }
});

function dataDaftarAbsensiPulang(parameterOrder){
  $.ajax({
    url:'datadaftarabsensipulang.php',
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
      $('#dataDaftarAbsensiPulang').empty().append(data);
      $('#parameterOrder').val(parameterOrder);
      $('.overlay').hide();
    }
  });
}

function tambahAbsensiPulang() {
  $("#modalFormAbsensiPulang").modal('show');

  $.ajax({
    url:'dataformabsensipulang.php',
    type:'post',
    data:{
      parameterOrder  : $('#parameterOrder').val(),
      flag            : ''
    },
    beforeSend:function(){
    },
    success:function(data,status){
      $("#dataFormAbsensiPulang").html(data);
      $('select.select2').select2();
    }
  });
}

function showDataPegawai(id) {
  $("#modalFormAbsensiPulang").modal('show');

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

function prosesAbsensiPulang(){

  let formAbsensiPulang = document.getElementById('formAbsensiPulang');
  let dataForm           = new FormData(formAbsensiPulang);

  const validasi = formValidation(dataForm);
  if (validasi === true) {
    $.ajax({
      url:'prosesabsensipulang.php',
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
        dataDaftarAbsensiPulang(dataJSON.parameterOrder);
        notifikasi(dataJSON.status,dataJSON.pesan);
        tambahAbsensiPulang();
        if(dataJSON.flag == 'update'){
          $("#modalFormAbsensiPulang").modal('hide');
        }
      }
    });
  }
  else {
     notifikasi(false, "Proses Gagal, Form Belum Terisi Dengan Lengkap");
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
        url:'prosesabsensipulang.php',
        type:'post',
        data:{
          idAbsensi  : id,
          parameterOrder : parameterOrder,
          flag           : flag
        },

        success:function(data,status){
          let dataJSON=JSON.parse(data);
          dataDaftarAbsensiPulang(dataJSON.parameterOrder);
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

function notifikasi(sukses, pesan){
  if(sukses == true){
    toastr.success(pesan);
  }
  else if(sukses == false){
    toastr.error(pesan);
  }
  
}
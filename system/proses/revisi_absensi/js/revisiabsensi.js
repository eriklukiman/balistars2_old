$('#rentang').daterangepicker({
  locale : {
    format : 'DD-MM-YYYY'
  }
});

$('select.select2').select2();

function dataDaftarRevisiAbsensi(parameterOrder){
  $.ajax({
    url:'datadaftarrevisiabsensi.php',
    type:'post',
    data:{
      rentang         : $('#rentang').val(),
      idCabang        : $('#idCabangSearch').val(),
      parameterOrder : parameterOrder
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarRevisiAbsensi').empty().append(data);
      $('#parameterOrder').val(parameterOrder);
      $('.overlay').hide();
    }
  });
}



function prosesRevisiAbsensi(){

  $.ajax({
    url:'prosesrevisiabsensi.php',
    type:'post',
    data:{
      rentang         : $('#rentang').val(),
      idCabang        : $('#idCabangSearch').val(),
    },
    beforeSend:function(){
    },

    success:function(data,status){
      // console.log(data);
      let dataJSON = JSON.parse(data);
      dataDaftarRevisiAbsensi();
      notifikasi(dataJSON.notifikasi);
    }
  });
}


function notifikasi(sukses){
  if(sukses == 1){
    toastr.success('Proses Data Berhasil');
  }
  else if(sukses == 2){
    toastr.error('Proses Data Gagal');
  }
}
$('#rentang').daterangepicker({
  locale : {
    format : 'DD-MM-YYYY'
  }
});

$('select.select2').select2();

function printLaporan(){ 
  let rentang = $('#rentang').val();
  let idCabang = $('#idCabangSearch').val();
  window.open('print_laporan/?rentang='+rentang+'&idCabang='+idCabang,'_blank');
}

function dataDaftarPenjualanSub(parameterOrder){ 
  $.ajax({
    url:'datadaftarpenjualansub.php',
    type:'post',
    data:{
      rentang : $('#rentang').val(),
      idCabang : $('#idCabangSearch').val(),
      parameterOrder : parameterOrder
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarPenjualanSub').empty().append(data);
      $('.overlay').hide();
    }
  });
}



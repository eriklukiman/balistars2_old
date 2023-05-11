$('#rentang').daterangepicker({
  locale : {
    format : 'DD-MM-YYYY'
  }
});

$('select.select2').select2();

function printLaporan(){ 
  let rentang = $('#rentang').val();
  window.open('print_laporan/?rentang='+rentang,'_blank');
}

function dataDaftarKunjungan(parameterOrder){ 
  $.ajax({
    url:'datadaftarkunjungan.php',
    type:'post',
    data:{
      rentang : $('#rentang').val(),
      parameterOrder : parameterOrder
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarKunjungan').empty().append(data);
      $('.overlay').hide();
    }
  });
}



$('#rentang').daterangepicker({
  locale : {
    format : 'DD-MM-YYYY'
  }
});

$('select.select2').select2();

function dataDaftarPengeluaranLain(parameterOrder){ 
  $.ajax({
    url:'datadaftarpengeluaranlain.php',
    type:'post',
    data:{
      rentang : $('#rentang').val(),
      kodeAkunting : $('#kodeAkuntingSearch').val(),
      parameterOrder : parameterOrder
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




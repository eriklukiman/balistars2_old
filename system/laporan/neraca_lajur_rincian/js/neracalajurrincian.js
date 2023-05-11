$('#rentang').daterangepicker({
  locale : {
    format : 'DD-MM-YYYY'
  }
});

$('select.select2').select2();


function dataDaftarNeracaLajurRincian(parameterOrder){ 
  $.ajax({
    url:'datadaftarneracalajurrincian.php',
    type:'post',
    data:{
      rentang : $('#rentang').val(),
      kodeACC : $('#kodeACCSearch').val(),
      parameterOrder : parameterOrder
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarNeracaLajurRincian').empty().append(data);
      $('.overlay').hide();
    }
  });
}



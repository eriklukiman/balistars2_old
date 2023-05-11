$('#rentang').daterangepicker({
  locale : {
    format : 'DD-MM-YYYY'
  }
});

$('select.select2').select2();


function dataDaftarNeracaLajur(parameterOrder){ 
  $.ajax({
    url:'datadaftarneracalajur.php',
    type:'post',
    data:{
      rentang : $('#rentang').val(),
      parameterOrder : parameterOrder
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarNeracaLajur').empty().append(data);
      $('.overlay').hide();
    }
  });
}



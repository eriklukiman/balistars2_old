$('#rentang').daterangepicker({
  locale : {
    format : 'DD-MM-YYYY'
  }
});

function dataDaftarFakturPajak(parameterOrder){ 
  $.ajax({
    url:'datadaftarfakturpajak.php',
    type:'post',
    data:{
      rentang : $('#rentang').val(),
      parameterOrder : parameterOrder
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarFakturPajak').empty().append(data);
      $('.overlay').hide();
    }
  });
}



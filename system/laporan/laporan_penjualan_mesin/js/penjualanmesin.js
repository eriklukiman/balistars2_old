$('#rentang').daterangepicker({
  locale : {
    format : 'DD-MM-YYYY'
  }
});

$('select.select2').select2();

function dataDaftarPenjualanMesin(parameterOrder){ 
  $.ajax({
    url:'datadaftarpenjualanmesin.php',
    type:'post',
    data:{
      rentang : $('#rentang').val(),
      parameterOrder : parameterOrder
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarPenjualanMesin').empty().append(data);
      $('.overlay').hide();
    }
  });
}



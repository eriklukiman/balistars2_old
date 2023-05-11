$('#rentang').daterangepicker({
  locale : {
    format : 'DD-MM-YYYY'
  }
});

$('select.select2').select2();

function dataDaftarPembelianCash(parameterOrder){ 
  $.ajax({
    url:'datadaftarpembeliancash.php',
    type:'post',
    data:{
      rentang : $('#rentang').val(),
      parameterOrder : parameterOrder
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarPembelianCash').empty().append(data);
      $('.overlay').hide();
    }
  });
}



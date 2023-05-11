$('#rentang').daterangepicker({
  locale : {
    format : 'DD-MM-YYYY'
  }
});

$('select.select2').select2();

function dataDaftarPenjualanRatarata(parameterOrder){ 
  $.ajax({
    url:'datadaftarpenjualanratarata.php',
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
      $('#dataDaftarPenjualanRatarata').empty().append(data);
      $('.overlay').hide();
    }
  });
}



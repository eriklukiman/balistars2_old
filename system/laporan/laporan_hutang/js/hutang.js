$('#rentang').daterangepicker({
  locale : {
    format : 'DD-MM-YYYY'
  }
});

$('select.select2').select2();

function dataDaftarHutang(parameterOrder){ 
  $.ajax({
    url:'datadaftarhutang.php',
    type:'post',
    data:{
      rentang : $('#rentang').val(),
      tipe : $('#tipeSearch').val(),
      parameterOrder : parameterOrder
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarHutang').empty().append(data);
      $('.overlay').hide();
    }
  });
}



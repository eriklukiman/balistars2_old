$('#rentang').daterangepicker({
  locale : {
    format : 'DD-MM-YYYY'
  }
});

$('select.select2').select2();

function dataDaftarKomparation(parameterOrder){ 
  $.ajax({
    url:'datadaftarkomparation.php',
    type:'post',
    data:{
      rentang : $('#rentang').val(),
      jenis : $('#jenis').val(),
      parameterOrder : parameterOrder
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarKomparation').empty().append(data);
      $('.overlay').hide();
    }
  });
}



$('#rentang').daterangepicker({
  locale : {
    format : 'DD-MM-YYYY'
  }
});

$('select.select2').select2();

function dataDaftarPembelianMesin(parameterOrder){ 
  $.ajax({
    url:'datadaftarpembelianmesin.php',
    type:'post',
    data:{
      rentang : $('#rentang').val(),
      idCabang : $('#idCabangSearch').val(),
      tipe : $('#tipeSearch').val(),
      parameterOrder : parameterOrder
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarPembelianMesin').empty().append(data);
      $('.overlay').hide();
    }
  });
}



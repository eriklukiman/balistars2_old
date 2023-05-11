$('#rentang').daterangepicker({
  locale : {
    format : 'DD-MM-YYYY'
  }
});

$('select.select2').select2();

function dataDaftarPphPpn(parameterOrder){ 
  $.ajax({
    url:'datadaftarpphppn.php',
    type:'post',
    data:{
      rentang : $('#rentang').val(),
      idCabang : $('#idCabangSearch').val(),
      jenisBiaya : $('#jenisBiaya').val(),
      parameterOrder : parameterOrder
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarPphPpn').empty().append(data);
      $('.overlay').hide();
    }
  });
}



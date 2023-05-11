$('#rentang').daterangepicker({
  locale : {
    format : 'DD-MM-YYYY'
  }
});

$('select.select2').select2();

function dataDaftarPiutangBerjalan(parameterOrder){ 
  $.ajax({
    url:'datadaftarpiutangberjalan.php',
    type:'post',
    data:{
      rentang : $('#rentang').val(),
      idCabang : $('#idCabangSearch').val(),
      tipe    : $('#tipeSearch').val(),
      idCustomer : $('#idCustomerSearch').val(),
      parameterOrder : parameterOrder
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarPiutangBerjalan').empty().append(data);
      $('.overlay').hide();
    }
  });
}



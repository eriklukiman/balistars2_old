$('#rentang').daterangepicker({
  locale : {
    format : 'DD-MM-YYYY'
  }
});

$('select.select2').select2();


function dataDaftarKasPiutang(parameterOrder){ 
  $.ajax({
    url:'datadaftarkaspiutang.php',
    type:'post',
    data:{
      rentang : $('#rentang').val(),
      idCabang : $('#idCabangSearch').val(),
      tipe : $('#tipeSearch').val(),
      //jenis : $('#jenisPembayaranSearch').val(),
      parameterOrder : parameterOrder
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarKasPiutang').empty().append(data);
      $('.overlay').hide();
    }
  });
}



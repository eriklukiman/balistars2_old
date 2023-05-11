
$('select.select2').select2();

function dataDaftarProduktivity(parameterOrder){ 
  $.ajax({
    url:'datadaftarproduktivity.php',
    type:'post',
    data:{
      bulan : $('#bulan1').val(),
      tahun : $('#tahun1').val(),
      jenisPenjualan : $('#jenisPenjualan').val(),
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarProduktivity').empty().append(data);
      $('.overlay').hide();
    }
  });
}




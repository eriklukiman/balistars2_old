
$('select.select2').select2();

function dataDaftarDailyMesin(parameterOrder){ 
  $.ajax({
    url:'datadaftardailymesin.php',
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
      $('#dataDaftarDailyMesin').empty().append(data);
      $('.overlay').hide();
    }
  });
}




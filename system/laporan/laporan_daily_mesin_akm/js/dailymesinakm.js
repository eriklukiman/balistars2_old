
$('select.select2').select2();

function dataDaftarDailyMesinAkm(parameterOrder){ 
  $.ajax({
    url:'datadaftardailymesinakm.php',
    type:'post',
    data:{
      bulanAkhir : $('#bulan1').val(),
      tahunAkhir : $('#tahun1').val(),
      jenisPenjualan : $('#jenisPenjualan').val(),
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarDailyMesinAkm').empty().append(data);
      $('.overlay').hide();
    }
  });
}




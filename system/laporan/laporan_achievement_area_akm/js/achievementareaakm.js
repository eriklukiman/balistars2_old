
$('select.select2').select2();

function dataDaftarAchievementAreaAkm(parameterOrder){ 
  $.ajax({
    url:'datadaftarachievementareaakm.php',
    type:'post',
    data:{
      bulan : $('#bulan1').val(),
      tahun : $('#tahun1').val(),
      area : $('#area').val(),
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarAchievementAreaAkm').empty().append(data);
      $('.overlay').hide();
    }
  });
}



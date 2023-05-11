
$('select.select2').select2();

function dataDaftarAchievementArea(parameterOrder){ 
  $.ajax({
    url:'datadaftarachievementarea.php',
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
      $('#dataDaftarAchievementArea').empty().append(data);
      $('.overlay').hide();
    }
  });
}



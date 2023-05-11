$('select.select2').select2();

function dataDaftarRankingCabang(parameterOrder){ 
  $.ajax({
    url:'datadaftarrankingcabang.php',
    type:'post',
    data:{
      bulan : $('#bulan').val(),
      tahun : $('#tahun').val(),
      parameterOrder : parameterOrder
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarRankingCabang').empty().append(data);
      $('.overlay').hide();
    }
  });
}



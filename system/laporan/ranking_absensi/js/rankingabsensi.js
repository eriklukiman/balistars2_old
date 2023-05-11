$(function(){
  $('.date').datepicker()
})

function dataDaftarRankingAbsensi(parameterOrder){ 
  $.ajax({
    url:'datadaftarrankingabsensi.php',
    type:'post',
    data:{
      tanggal : $('#tanggal').val(),
      parameterOrder : parameterOrder
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      //console.log(tipe);
      $('#dataDaftarRankingAbsensi').empty().append(data);
      $('.overlay').hide();
    }
  });
}

function printLaporan(){ 
  let tanggal = $('#tanggal').val();
  //console.log($('#tanggal').val());
  window.open('print_nota/?tanggal='+tanggal, '_blank');
}


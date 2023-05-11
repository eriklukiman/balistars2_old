$(function(){
  $('.date').datepicker()
})

$('select.select2').select2();

function dataDaftarAkumulasi(parameterOrder){ 
  $.ajax({
    url:'datadaftarakumulasi.php',
    type:'post',
    data:{
      tanggal : $('#tanggal').val(),
      tipe : $('#tipeSearch').val(),
      parameterOrder : parameterOrder
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      //console.log(tipe);
      $('#dataDaftarAkumulasi').empty().append(data);
      $('.overlay').hide();
    }
  });
}

//setInterval(dataDaftarAkumulasi,3000);


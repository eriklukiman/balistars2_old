
$('select.select2').select2();

function dataDaftarLabaRugiKotor(parameterOrder){ 
  $.ajax({
    url:'datadaftarlabarugikotor.php',
    type:'post',
    data:{
      bulan : $('#bulan1').val(),
      tahun : $('#tahun1').val(),
      idCabang : $('#idCabangSearch').val(),
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarLabaRugiKotor').empty().append(data);
      $('.overlay').hide();
    }
  });
}

setInterval(dataDaftarLabaRugi,3000);


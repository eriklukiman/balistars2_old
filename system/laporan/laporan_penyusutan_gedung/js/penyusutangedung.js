
$('select.select2').select2();

function dataDaftarPenyusutanGedung(parameterOrder){ 
  $.ajax({
    url:'datadaftarpenyusutangedung.php',
    type:'post',
    data:{
      bulan : $('#bulan1').val(),
      tahun : $('#tahun1').val(),
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarPenyusutanGedung').empty().append(data);
      $('.overlay').hide();
    }
  });
}


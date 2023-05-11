
$('select.select2').select2();

function dataDaftarPointSDM(parameterOrder){ 
  $.ajax({
    url:'datadaftarpointsdm.php',
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
      $('#dataDaftarPointSDM').empty().append(data);
      $('.overlay').hide();
    }
  });
}

function printLaporan(){
  let bulan = $('#bulan1').val();
  let tahun = $('#tahun1').val();
  let idCabang = $('#idCabangSearch').val();
  window.open('print_laporan/?bulan='+bulan+'&tahun='+tahun+'&idCabang='+idCabang,'_blank');
}



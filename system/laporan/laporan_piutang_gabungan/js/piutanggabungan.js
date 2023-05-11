
$('select.select2').select2();

function dataDaftarPiutangGabungan(parameterOrder){ 
  $.ajax({
    url:'datadaftarpiutanggabungan.php',
    type:'post',
    data:{
      bulan1 : $('#bulan1').val(),
      bulan2 : $('#bulan2').val(),
      tahun1 : $('#tahun1').val(),
      tahun2 : $('#tahun2').val(),
      tipePenjualan    : $('#tipeSearch').val(),
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarPiutangGabungan').empty().append(data);
      $('.overlay').hide();
    }
  });
}

function printLaporan(noNota){
  let bulan1 = $('#bulan1').val();
  let bulan2 = $('#bulan2').val();
  let tahun1 = $('#tahun1').val();
  let tahun2 = $('#tahun2').val();
  let tipePenjualan = $('#tipeSearch').val();
  window.open('print_laporan/?bulan1='+bulan1+'&bulan2='+bulan2+'&tahun1='+tahun1+'&tahun2='+tahun2+'&tipePenjualan='+tipePenjualan,'_blank');
}


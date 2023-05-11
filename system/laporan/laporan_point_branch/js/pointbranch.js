
$('select.select2').select2();

function dataDaftarPointBranch(parameterOrder){ 
  $.ajax({
    url:'datadaftarpointbranch.php',
    type:'post',
    data:{
      bulan : $('#bulan1').val(),
      tahun : $('#tahun1').val(),
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarPointBranch').empty().append(data);
      $('.overlay').hide();
    }
  });
}

function printLaporan(){
  let bulan = $('#bulan1').val();
  let tahun = $('#tahun1').val();
  window.open('print_laporan/?bulan='+bulan+'&tahun='+tahun,'_blank');
}



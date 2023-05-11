$('#rentang').daterangepicker({
  locale : {
    format : 'DD-MM-YYYY'
  }
});

$('select.select2').select2();

function dataDaftarUangMasuk(parameterOrder){ 
  $.ajax({
    url:'datadaftaruangmasuk.php',
    type:'post',
    data:{
      rentang : $('#rentang').val(),
      idCabang : $('#idCabangSearch').val(),
      parameterOrder : parameterOrder
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarUangMasuk').empty().append(data);
      $('.overlay').hide();
    }
  });
}


function printLaporan(){ 
  let rentang = $('#rentang').val();
  let idCabang = $('#idCabangSearch').val();
  window.open('print_laporan/?rentang='+rentang+'&idCabang='+idCabang,'_blank');
}



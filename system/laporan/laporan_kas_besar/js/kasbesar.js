$('#rentang').daterangepicker({
  locale : {
    format : 'DD-MM-YYYY'
  }
});

$('select.select2').select2();

function printLaporan(){
  let rentang = $('#rentang').val();
  let idCabang = $('#idCabangSearch').val();
  let tipe = $('#tipeSearch').val();
  let jenis = $('#jenisPembayaranSearch').val();
  window.open('print_laporan/?rentang='+rentang+'&idCabang='+idCabang+'&tipe='+tipe+'&jenis='+jenis,'_blank');
}

function exportExcel(){
  let rentang = $('#rentang').val();
  let idCabang = $('#idCabangSearch').val();
  let tipe = $('#tipeSearch').val();
  let jenis = $('#jenisPembayaranSearch').val();
  window.open('excel/?rentang='+rentang+'&idCabang='+idCabang+'&tipe='+tipe+'&jenis='+jenis,'_blank');
}

function dataDaftarKasBesar(parameterOrder){ 
  $.ajax({
    url:'datadaftarkasbesar.php',
    type:'post',
    data:{
      rentang : $('#rentang').val(),
      idCabang : $('#idCabangSearch').val(),
      tipe : $('#tipeSearch').val(),
      jenis : $('#jenisPembayaranSearch').val(),
      parameterOrder : parameterOrder
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarKasBesar').empty().append(data);
      $('.overlay').hide();
    }
  });
}



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
  let idCustomer = $('#idCustomerSearch').val();
  window.open('print_laporan/?rentang='+rentang+'&idCabang='+idCabang+'&tipe='+tipe+'&idCustomer='+idCustomer,'_blank');
}

function exportExcel(){ 
  let rentang = $('#rentang').val();
  let idCabang = $('#idCabangSearch').val();
  let tipe = $('#tipeSearch').val();
  let idCustomer = $('#idCustomerSearch').val();
  window.open('excel/?rentang='+rentang+'&idCabang='+idCabang+'&tipe='+tipe+'&idCustomer='+idCustomer,'_blank');
}

function dataDaftarPiutang(parameterOrder){ 
  $.ajax({
    url:'datadaftarpiutang.php',
    type:'post',
    data:{
      rentang : $('#rentang').val(),
      idCabang : $('#idCabangSearch').val(),
      tipe    : $('#tipeSearch').val(),
      idCustomer : $('#idCustomerSearch').val(),
      parameterOrder : parameterOrder
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarPiutang').empty().append(data);
      $('.overlay').hide();
    }
  });
}



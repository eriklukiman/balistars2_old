$('#rentang').daterangepicker({
  locale : {
    format : 'DD-MM-YYYY'
  }
});

$('select.select2').select2();

function printLaporan(){
  let rentang = $('#rentang').val();
  let idBank = $('#idBankSearch').val();
  window.open('print_laporan/?rentang='+rentang+'&idBank='+idBank,'_blank');
}

function exportExcel(){
  let rentang = $('#rentang').val();
  let idBank = $('#idBankSearch').val();
  window.open('excel/?rentang='+rentang+'&idBank='+idBank,'_blank');
}

function dataDaftarReportBank(parameterOrder){ 
  $.ajax({
    url:'datadaftarreportbank.php',
    type:'post',
    data:{
      rentang : $('#rentang').val(),
      idBank : $('#idBankSearch').val(),
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarReportBank').empty().append(data);
      $('.overlay').hide();
    }
  });
}



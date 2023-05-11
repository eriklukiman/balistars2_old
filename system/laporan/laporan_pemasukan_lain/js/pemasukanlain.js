$('#rentang').daterangepicker({
  locale : {
    format : 'DD-MM-YYYY'
  }
});

$('select.select2').select2();

function dataDaftarPemasukanLain(parameterOrder){ 
  $.ajax({
    url:'datadaftarpemasukanlain.php',
    type:'post',
    data:{
      rentang : $('#rentang').val(),
      idKodePemasukan : $('#idKodePemasukanSearch').val(),
      parameterOrder : parameterOrder
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarPemasukanLain').empty().append(data);
      $('.overlay').hide();
    }
  });
}




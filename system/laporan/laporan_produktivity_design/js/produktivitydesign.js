$('#rentang').daterangepicker({
  locale : {
    format : 'DD-MM-YYYY'
  }
})

$('select.select2').select2();

function dataDaftarProduktivityDesign(parameterOrder){ 
  $.ajax({
    url:'datadaftarproduktivitydesign.php',
    type:'post',
    data:{
      rentang : $('#rentang').val(),
      idCabang : $('#idCabangSearch').val(),
      tipe : $('#tipeSearch').val(),
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarProduktivityDesign').empty().append(data);
      $('.overlay').hide();
    }
  });
}




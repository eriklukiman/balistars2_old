$('#rentang').daterangepicker({
  locale : {
    format : 'DD-MM-YYYY'
  }
});

$('select.select2').select2();

function dataDaftarAchievement(parameterOrder){ 
  $.ajax({
    url:'datadaftarachievement.php',
    type:'post',
    data:{
      rentang : $('#rentang').val(),
      tipe : $('#tipeSearch').val(),
      parameterOrder : parameterOrder
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarAchievement').empty().append(data);
      $('.overlay').hide();
    }
  });
}

function jamDinding(){
  let d = new Date();
  let n = d.toLocaleTimeString();
  $('#jamAnalog').text(n);
}

// setInterval(dataDaftarAchievement,3000);
 setInterval(jamDinding,1000);



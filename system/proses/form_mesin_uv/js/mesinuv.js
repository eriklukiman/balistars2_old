function dataDaftarMesinUV(){
  //console.log($('#parameterOrder').val());
  $.ajax({
    url:'datadaftarmesinuv.php',
    type:'post',
    data:{
      idCabang : $('#idCabang').val(),
      rentang : $('#rentang').val(),
      parameterOrder : $('#parameterOrder').val()
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarMesinUV').empty().append(data);
      //$('#parameterOrder').val(parameterOrder);
      $('.overlay').hide();
    }
  });
}


function editMesinUV(id){

    $.ajax({
      url:'prosesmesinuv.php',
      type:'post',
      data:{
        flag : 'cancel',
        idPerformaUV : id,
      },

      success:function(data,status){
        // console.log(data);
        let dataJSON = JSON.parse(data);
        dataDaftarMesinUV(dataJSON.parameterOrder);
        notifikasi(dataJSON.notifikasi);
        resetForm(dataJSON.notifikasi);
      }
    });
}

function prosesMesinUV(id){

  $.ajax({
    url:'prosesmesinuv.php',
    type:'post',
    data:{
      noNota : $('#noNota'+id).val(),
      idCabang : $('#idCabang'+id).val(),
      namaBahan : $('#namaBahan'+id).val(),
      flag : $('#flag'+id).val(),
      idPenjualanDetail : $('#idPenjualanDetail'+id).val(),
      tanggalPerforma : $('#tanggalPerforma'+id).val(),
      ukuran : $('#ukuran'+id).val(),
      luas : $('#luas'+id).val(),
    },

    beforeSend:function(){
    },

    success:function(data,status){
      // console.log(data);
      let dataJSON = JSON.parse(data);
      dataDaftarMesinUV(dataJSON.parameterOrder);
      notifikasi(dataJSON.notifikasi);
      resetForm(dataJSON.notifikasi);
    }
  }); 
}

function showLuas(id) {

  var qty = $('#qty'+id).val();
  var ukuran = $('#ukuran'+id).val();
  var sisi = ukuran.split('x');
  var panjang = parseInt(sisi[0]);
  var lebar = parseInt(sisi[1]);

  qty = qty || '0';
  qty =  accounting.unformat(qty, ",");
  qty = parseInt(qty);

  var luas = parseFloat((qty*panjang*lebar/10000).toFixed(2));
  //luas = accounting.formatNumber(luas,{thousand : ".", decimal  : ","});
  $('#luas'+id).val(luas);
} 

function resetForm(sukses){
  if(sukses == 1){
    $('input[type=text]').val('');
    $('#flag').val('');
    $('#cabang').val(null).trigger('change');
    $('#jenis').val(null).trigger('change');
    //$('.select2').val(null).trigger('change');
  }
}

function notifikasi(sukses){
  if(sukses == 1){
    toastr.success('Proses Data Berhasil');
  }
  else if(sukses == 2){
    toastr.error('Proses Data Gagal');
  }
}
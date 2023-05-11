$("#rentang").daterangepicker({
      locale: {
          format: 'DD-MM-YYYY'
      }
    });

function dataDaftarHutangGedung(){
  $.ajax({
    url:'datadaftarhutanggedung.php',
    type:'post',
    data:{
      rentang : $('#rentang').val()
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarHutangGedung').empty().append(data);
      $('#parameterOrder').val(parameterOrder);
      $('.overlay').hide();
    }
  });
}

function bayarHutangGedung(idHutangGedung) {
  //console.log($('#tipe').val());
   $("#modalFormHutangGedung").modal('show');
    $.ajax({
      url:'dataformbayarhutanggedung.php',
      type:'post',
      data:{
        idHutangGedung : idHutangGedung
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormHutangGedung").html(data);
        $('select.select2').select2();
        getPembayaranHutangGedungTersimpan(idHutangGedung);
      }
    });
}

function editBayarHutangGedung(idHutangGedung,idPembayaran) {
  //console.log($('#tipe').val());
   $("#modalFormHutangGedung").modal('show');
    $.ajax({
      url:'dataformbayarhutanggedung.php',
      type:'post',
      data:{
        idHutangGedung : idHutangGedung,
        idPembayaran : idPembayaran,
        flag   : 'update',
        parameterOrder : $('#parameterOrder').val(),
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormHutangGedung").html(data);
        $('select.select2').select2();
        getPembayaranHutangGedungTersimpan(idHutangGedung);
      }
    });
}

function finalisasiBayarHutangGedung(idHutangGedung,idPembayaran) {
  //console.log($('#tipe').val());
   $("#modalFormHutangGedung").modal('show');
    $.ajax({
      url:'prosesbayarhutanggedung.php',
      type:'post',
      data:{
        idHutangGedung : idHutangGedung,
        idPembayaran : idPembayaran,
        flag   : 'finalisasi'
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormHutangGedung").html(data);
        let dataJSON = JSON.parse(data);
        $('select.select2').select2();
        notifikasi(dataJSON.status,dataJSON.notifikasi);
        bayarHutangGedung(idHutangGedung);
      }
    });
}

function cancelBayarHutangGedung(idHutangGedung,idPembayaran) {
  //console.log($('#tipe').val());
   $("#modalFormHutangGedung").modal('show');
    $.ajax({
      url:'prosesbayarhutanggedung.php',
      type:'post',
      data:{
        idHutangGedung : idHutangGedung,
        idPembayaran : idPembayaran,
        flag   : 'cancel'
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormHutangGedung").html(data);
        let dataJSON = JSON.parse(data);
        $('select.select2').select2();
        notifikasi(dataJSON.status,dataJSON.notifikasi);
        bayarHutangGedung(idHutangGedung);
      }
    });
}
function getPembayaranHutangGedungTersimpan(idHutangGedung){
  $.ajax({
    url:'datadaftarpembayaranhutanggedung.php',
    type:'post',
    data:{
      idHutangGedung : idHutangGedung,
    },
    success:function(data,status){
      $('#dataDaftarPembayaranHutangGedung').empty().append(data);
      $('select.select2').select2();
      //$('#keterangan').focus();
    }
  });
}

function showSisaHutang() {
  var sisaHutangAwal = $('#sisaHutangAwal').val();
  var jumlahPembayaran = $('#jumlahPembayaran').val();


  sisaHutangAwal = sisaHutangAwal || '0';
  sisaHutangAwal =  accounting.unformat(sisaHutangAwal, ",");
  sisaHutangAwal = parseInt(sisaHutangAwal);

  jumlahPembayaran = jumlahPembayaran || '0';
  jumlahPembayaran =  accounting.unformat(jumlahPembayaran, ",");
  jumlahPembayaran = parseInt(jumlahPembayaran);

  var sisaHutang = (sisaHutangAwal-jumlahPembayaran);
  if(jumlahPembayaran>sisaHutangAwal){
    sisaHutang = 0;
  }
  sisaHutang = accounting.formatNumber(sisaHutang,{thousand : ".", decimal  : ","});
  $('#sisaHutang').val(sisaHutang);
} 

function prosesBayarHutangGedung(){

  let formBayarHutangGedung = document.getElementById('formBayarHutangGedung');
  let dataForm           = new FormData(formBayarHutangGedung);

  const validasi = formValidation(dataForm,['noGiro']);
  if (validasi === true) {
    $.ajax({
      url:'prosesbayarhutanggedung.php',
      type:'post',
      enctype: 'multipart/form-data',
      processData: false,
      contentType: false,
      data:dataForm,

      beforeSend:function(){
      },

      success:function(data,status){
         //console.log(data);
        let dataJSON = JSON.parse(data);
        notifikasi(dataJSON.status,dataJSON.notifikasi);
        bayarHutangGedung(dataJSON.idHutangGedung);
      }
    });
  }
}



function resetForm(sukses){
  if(sukses == 1){
    $('input[type=text]').val('');
    $('#flag').val('');
    $('.select2').val(null).trigger('change');
    $('#keterangan').val('');
  }
}

function notifikasi(sukses,pesan){
  if(sukses){
    toastr.success(pesan);
  }
  else {
    toastr.error(pesan);
  }
}
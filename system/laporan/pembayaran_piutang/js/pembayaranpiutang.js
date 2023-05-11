$('select.select2').select2();

$("#rentang").daterangepicker({
      locale: {
          format: 'DD-MM-YYYY'
      }
    });

function printPembayaranPiutang(noNota){
   window.open('print_nota/?noNota='+noNota,'_blank');
}

function printPembayaranPiutangHistory(noNota,idPiutang){
   window.open('print_nota_history/?noNota='+noNota+'&idPiutang='+idPiutang,'_blank');
}

function dataDaftarPembayaranPiutang(){ 
  $.ajax({
    url:'datadaftarpembayaranpiutang.php',
    type:'post',
    data:{
      rentang : $('#rentang').val()
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      //console.log(tipe);
      $('#dataDaftarPembayaranPiutang').empty().append(data);
      $('.overlay').hide();
    }
  });
}

function bayarPembayaranPiutang(noNota) {
  //console.log($('#tipe').val());
   $("#modalBoxPembayaranPiutang").modal('show');
    $.ajax({
      url:'dataformbayarpembayaranpiutang.php',
      type:'post',
      data:{
        noNota : noNota,
        parameterOrder : $('#parameterOrder').val(),
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#boxPembayaranPiutang").html(data);
        $('select.select2').select2();
        getPembayaranPiutangTersimpan(noNota);
      }
    });
}

function showJenisPembayaran() {
    $.ajax({
        url: "dataformjenispembayaran.php",
        type: "post",
        data: {
            jenisPembayaran: $("#jenisPembayaran").val(),
        },
        beforeSend: function () {},
        success: function (data, status) {
            $("#boxJenisPembayaran").html(data);
            $("select.select2").select2();

        },
    });
}

function showSemua(){
  getKembalian();
  statusKembalian();
  showSisaPiutang()
}

function getKembalian() {
  var grandTotal = $('#sisaPiutangAwal').val();
  var jumlahPembayaran = $('#jumlahPembayaran').val();

  grandTotal = grandTotal || '0';
  jumlahPembayaran = jumlahPembayaran || '0';


  jumlahPembayaran = accounting.unformat(jumlahPembayaran, ",");
  jumlahPembayaran = parseInt(jumlahPembayaran);

  grandTotal = accounting.unformat(grandTotal, ",");
  grandTotal = parseInt(grandTotal);

  var kembalian = jumlahPembayaran-grandTotal;
  if(kembalian<0){
    kembalian=0;
  }
  
  kembalian = accounting.formatNumber(kembalian,{thousand : ".", decimal  : ","});
  $('#kembalian').val(kembalian);
}

function statusKembalian() {
  var grandTotal = $('#sisaPiutangAwal').val();
  var jumlahPembayaran = $('#jumlahPembayaran').val();

  grandTotal = grandTotal || '0';
  jumlahPembayaran = jumlahPembayaran || '0';


  jumlahPembayaran = accounting.unformat(jumlahPembayaran, ",");
  jumlahPembayaran = parseInt(jumlahPembayaran);

  grandTotal = accounting.unformat(grandTotal, ",");
  grandTotal = parseInt(grandTotal);

  var kembalian = jumlahPembayaran-grandTotal;
  if(kembalian<0){
    statusPembayaran='Belum Lunas';
  }
  else{
    statusPembayaran='Lunas';
  }
 
  $('#statusPembayaran').val(statusPembayaran);
}

function showSisaPiutang() {
  var sisaPiutangAwal = $('#sisaPiutangAwal').val();
  var jumlahPembayaran = $('#jumlahPembayaran').val();

  sisaPiutangAwal = sisaPiutangAwal || '0';
  sisaPiutangAwal =  accounting.unformat(sisaPiutangAwal, ",");
  sisaPiutangAwal = parseInt(sisaPiutangAwal);

  jumlahPembayaran = jumlahPembayaran || '0';
  jumlahPembayaran =  accounting.unformat(jumlahPembayaran, ",");
  jumlahPembayaran = parseInt(jumlahPembayaran);

  var sisaPiutang = (sisaPiutangAwal-jumlahPembayaran);

  if(jumlahPembayaran>sisaPiutangAwal){
    sisaPiutang = 0;
  }
  sisaPiutang = accounting.formatNumber(sisaPiutang,{thousand : ".", decimal  : ","});
  $('#sisaPiutang').val(sisaPiutang);
} 

function getPembayaranPiutangTersimpan(noNota){
  $.ajax({
    url:'datadaftarpembayaranpiutangtersimpan.php',
    type:'post',
    data:{
      noNota : noNota
    },
    success:function(data,status){
      $('#dataDaftarPembayaranPiutangTersimpan').empty().append(data);
      $('select.select2').select2();
    }
  });
}


function prosesPembayaranPiutang(){

  let formPembayaranPiutang = document.getElementById('formPembayaranPiutang');
  let dataForm           = new FormData(formPembayaranPiutang);

  const validasi = formValidation(dataForm);
  if (validasi === true) {
    $.ajax({
      url:'prosespembayaranpiutang.php',
      type:'post',
      enctype: 'multipart/form-data',
      processData: false,
      contentType: false,
      data:dataForm,

      beforeSend:function(){
      },

      success:function(data,status){
        // console.log(data);
        let dataJSON = JSON.parse(data);
        notifikasi(dataJSON.notifikasi);
        bayarPembayaranPiutang(dataJSON.noNota);
        dataDaftarPembayaranPiutang();
      }
    });
  }
  else {
     notifikasi(2, "Proses Gagal, Form Belum Terisi Dengan Lengkap");
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


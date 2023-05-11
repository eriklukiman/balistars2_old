$('select.select2').select2();

$("#rentang").daterangepicker({
      locale: {
          format: 'DD-MM-YYYY'
      }
    });

function dataDaftarPembelianMesin(){ 
  //console.log($('#tipe').val());
  $.ajax({
    url:'datadaftarpembelianmesin.php',
    type:'post',
    data:{
      tipe : $('#tipeSearch').val(),
      rentang : $('#rentang').val()
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      //console.log(tipe);
      $('#dataDaftarPembelianMesin').empty().append(data);
      $('.overlay').hide();
    }
  });
}

function bayarPembelianMesin(noNota) {
  //console.log($('#tipe').val());
   $("#modalBoxPembelianMesin").modal('show');
        $('.modal-title').empty().html('Form Bayar Pembelian Mesin');
    $.ajax({
      url:'dataformbayarpembelianmesin.php',
      type:'post',
      data:{
        noNota : noNota
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#boxPembelianMesin").html(data);
        $('select.select2').select2();
        getPembayaranMesinTersimpan(noNota);
      }
    });
}

function editPembelianMesin(noNota) {
  //console.log($('#tipe').val());
   $("#modalBoxPembelianMesin").modal('show');
        $('.modal-title').empty().html('Form Pembelian Mesin');
    $.ajax({
      url:'dataformpembelianmesin.php',
      type:'post',
      data:{
        noNota : noNota,
        rentang : $('#rentang').val(),
        flag    : 'update',
        parameterOrder : $('#parameterOrder').val(),
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#boxPembelianMesin").html(data);
        $('select.select2').select2();
        formItemPembelianMesin();
        getPembelianMesinTersimpan(noNota);  
      }
    });
}

function editBarang(idPembelianDetail) {
    $.ajax({
      url:'dataformitempembelianmesin.php',
      type:'post',
      data:{
        idPembelianDetail : idPembelianDetail,
        flagDetail        : 'update'
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormItemPembelianMesin").html(data);
        $('select.select2').select2();
        getPembelianMesinTersimpan();
      }
    });
}

function formItemPembelianMesin() {
    $.ajax({
      url:'dataformitempembelianmesin.php',
      type:'post',
      data:{
        noNota : $('#noNota').val(),
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormItemPembelianMesin").html(data);
        $('select.select2').select2();
      }
    });
}

function editBayarPembelianMesin(noNota,idHutangMesin) {
  //console.log($('#tipe').val());
   $("#modalBoxPembelianMesin").modal('show');
        $('.modal-title').empty().html('Form Bayar Pembelian Mesin');
    $.ajax({
      url:'dataformbayarpembelianmesin.php',
      type:'post',
      data:{
        noNota : noNota,
        idHutangMesin: idHutangMesin,
        flag : 'update',
        parameterOrder : $('#parameterOrder').val(),
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#boxPembelianMesin").html(data);
        $('select.select2').select2();
        getPembayaranMesinTersimpan(noNota);
        
      }
    });
}

function finalisasiBayarPembelianMesin(noNota,idHutangMesin) {
  //console.log($('#tipe').val());
   $("#modalBoxPembelianMesin").modal('show');
        $('.modal-title').empty().html('Form Bayar Pembelian Mesin');
    $.ajax({
      url:'prosesbayarpembelianmesin.php',
      type:'post',
      data:{
        noNota : noNota,
        idHutangMesin: idHutangMesin,
        flag : 'finalisasi'
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#boxPembelianMesin").html(data);
        $('select.select2').select2();
        bayarPembelianMesin(noNota);
        
      }
    });
}

function cancelBarang(idPembelianDetail){
  //console.log(idPembelianMesinDetail);
   swal({
    title: "Apakah anda yakin ingin membatalkan item ini?",
    text: "Setelah dibatalkan, proses tidak dapat diulangi!",
    icon: "warning",
    buttons: true,
    dangerMode: true,
  })
  .then((willDelete) => {
    if(willDelete){
      $.ajax({
        url:'prosespembelianmesindetail.php',
        type:'post',
        data:{
          idPembelianDetail : idPembelianDetail,
          flagDetail : 'cancel'
        },
        success:function(data,status){   
          let dataJSON = JSON.parse(data);
          notifikasi(dataJSON.notifikasi);
          formItemPembelianMesin();
          getPembelianMesinTersimpan();
        }
      });
    } 
    else{
      swal({
        title: "Proses pembatalan item dibatalkan!",
        icon: "warning"
      });
    }
  });
}

function cancelPembelianMesin(noNota){
   swal({
    title: "Apakah anda yakin ingin membatalkan PembelianMesin ini?",
    text: "Setelah dibatalkan, proses tidak dapat diulangi!",
    icon: "warning",
    buttons: true,
    dangerMode: true,
  })
  .then((willDelete) => {
    if(willDelete){
      $.ajax({
        url:'prosespembelianmesin.php',
        type:'post',
        data:{
          noNota : noNota,
          tipePembelian : $('#tipeSearch').val(),
          flag : 'cancel'
        },
        success:function(data,status){   
          //console.log(data);
          let dataJSON = JSON.parse(data);
          console.log(dataJSON);
          notifikasi(dataJSON.notifikasi);
          var parameterOrder = $('#parameterOrder').val(); 
          dataDaftarPembelianMesin(parameterOrder);
        }
      });
    } 
    else{
      swal({
        title: "Proses pembatalan PembelianMesin dibatalkan!",
        icon: "warning"
      });
    }
  });
}

function getNilai() {
  var hargaSatuan = $('#hargaSatuan').val();
  var qty = $('#qty').val();


  hargaSatuan = hargaSatuan || '0';
  hargaSatuan =  accounting.unformat(hargaSatuan, ",");
  hargaSatuan = parseInt(hargaSatuan);

  diskon = diskon || '0';
  diskon =  accounting.unformat(diskon, ",");
  diskon = parseInt(diskon);

  var nilai = (qty*(hargaSatuan-diskon));
  nilai = accounting.formatNumber(nilai,{thousand : ".", decimal  : ","});
  $('#nilai').val(nilai);
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

function prosesPembelianMesinDetail() {
  const dataForm = new FormData();
  dataForm.append('noNota',$('#noNota').val());
  dataForm.append('namaBarang',$('#namaBarang').val());
  dataForm.append('hargaSatuan',$('#hargaSatuan').val());
  dataForm.append('qty',$('#qty').val());
  dataForm.append('diskon',$('#diskon').val());
  dataForm.append('nilai',$('#nilai').val());
  dataForm.append('flagDetail',$('#flagDetail').val());
  dataForm.append('idPembelianDetail',$('#idPembelianDetail').val());

  const validasi = formValidation(dataForm);
  if ( validasi=== true){
     $.ajax({
      url:'prosespembelianmesindetail.php',
      type:'post',
      enctype: 'multipart/form-data',
      processData: false,
      contentType: false,
      data:dataForm,
      success:function(data,status){  
        //console.log(data);      
        let dataJSON = JSON.parse(data);
        notifikasi(dataJSON.notifikasi);
        resetFormDetail(dataJSON.notifikasi); 
        formItemPembelianMesin();
        getPembelianMesinTersimpan();
      }
    });
  }
  else {
     notifikasi(2, "Proses Gagal, Form Belum Terisi Dengan Lengkap");
  }
}


function getPembelianMesinTersimpan(){
  //console.log($('#jenisPPN').val(),$('#persenPPN').val())
  $.ajax({
    url:'datadaftarpembelianmesintersimpan.php',
    type:'post',
    data:{
      noNota : $('#noNota').val(),
      jenisPPN : $('#jenisPPN').val(),
      persenPPN : $('#persenPPN').val() || 11,
      tipePembelian : $('#tipePembelian').val()
    },
    success:function(data,status){
      $('#dataDaftarPembelianMesinTersimpan').empty().append(data);
      $('select.select2').select2();
      //$('#keterangan').focus();
    }
  });
}

function getPembayaranMesinTersimpan(noNota){
  //console.log($('#jenisPPN').val(),$('#persenPPN').val())
  $.ajax({
    url:'datadaftarpembayaranmesintersimpan.php',
    type:'post',
    data:{
      noNota : noNota,
    },
    success:function(data,status){
      $('#dataDaftarPembayaranMesinTersimpan').empty().append(data);
      $('select.select2').select2();
      //$('#keterangan').focus();
    }
  });
}

function showPersen(){ 
  //console.log($('#jenisPPN').val())
  var jenisPPN = $('#jenisPPN').val();
  if(jenisPPN=='Non PPN'){
    $('#boxPersenPPN').hide();
    $('#persenPPN').val('');
  }
  else{
    $('#boxPersenPPN').show();
    $('#persenPPN').val('11').trigger('change');
  }
}

function prosesPembelianMesin() {
  let formPembelianMesin = document.getElementById('formPembelianMesin');
  let dataForm  = new FormData(formPembelianMesin);

  const validasi = formValidation(dataForm);
  if ( validasi=== true){
    $.ajax({
      url:'prosespembelianmesin.php',
      type:'post',
      data:{
        noNota : $('#noNota').val(),
        idPembelianMesin : $('#idPembelianMesin').val(),
        namaSupplier : $('#namaSupplier').val(),
        lamaPenyusutan : $('#lamaPenyusutan').val(),
        flag    : $('#flag').val(),
        tipePembelian    : $('#tipePembelian').val(),
        tanggalPembelian : $('#tanggalPembelian').val(),
        noNotaVendor  : $('#noNotaVendor').val(),
        idCabang : $('#idCabang').val(),
        jenisPPN : $('#jenisPPN').val(),
        persenPPN : $('#persenPPN').val(),
        nilaiPPN : $('#nilaiPPN').val(),
        grandTotal : $('#grandTotal').val(),
        kodeAkunting : $('#kodeAkunting').val(),
      },
      success:function(data,status){    
        //console.log(data);    
        let dataJSON = JSON.parse(data);
        console.log(dataJSON);
        notifikasi(dataJSON.notifikasi);
        //tambahPembelianMesin(dataJSON.tipePembelian);
        dataDaftarPembelianMesin('noNota');
        if(dataJSON.flag == 'update'){
          $("#modalBoxPembelianMesin").modal('hide');
        }
      }
    });
  }
  else {
     notifikasi(2, "Proses Gagal, Form Belum Terisi Dengan Lengkap");
  }
}

function prosesBayarPembelianMesin(){

  let formBayarPembelianMesin = document.getElementById('formBayarPembelianMesin');
  let dataForm           = new FormData(formBayarPembelianMesin);

  const validasi = formValidation(dataForm,['noGiro']);
  if (validasi === true) {
    $.ajax({
      url:'prosesbayarpembelianmesin.php',
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
        bayarPembelianMesin(dataJSON.noNota);
      }
    });
  }
  else {
     notifikasi(2, "Proses Gagal, Form Belum Terisi Dengan Lengkap");
  }
}

function notifikasi(sukses){
  if(sukses == 1){
    toastr.success('Proses Data Barang Berhasil');
  }
  else if(sukses == 2){
    toastr.error('Proses Data Barang Gagal');
  }
}

function resetFormDetail(sukses){
  if(sukses == 1){
    $('#namaBarang').val('');
    $('#hargaSatuan').val('');
    $('#qty').val('');
    $('#nilai').val('');
  }
}

$('select.select2').select2();

function showSupplier(){ 
  console.log($('#idSupplierSearch').val())
  var jenisPembelian = $('#jenisPembelianSearch').val();
  if(jenisPembelian=='Cash'){
    $('#boxSupplier').hide();
    $('#idSupplierSearch').val('0').trigger('change');
  }
  else{
    $('#boxSupplier').show();
    $('#idSupplierSearch').val('0').trigger('change');
  }
  dataDaftarPembelian();
}

function printLaporan(){ 
  let rentang = $('#rentang').val();
  let idCabang = $('#idCabangSearch').val();
  let tipe = $('#tipeSearch').val();
  let jenisPembelian = $('#jenisPembelianSearch').val();
  let idSupplier = $('#idSupplierSearch').val();
  window.open('print_laporan/?rentang='+rentang+'&idCabang='+idCabang+'&tipe='+tipe+'&jenisPembelian='+jenisPembelian+'&idSupplier='+idSupplier,'_blank');
}

function exportExcel(){ 
  let rentang = $('#rentang').val();
  let idCabang = $('#idCabangSearch').val();
  let tipe = $('#tipeSearch').val();
  let jenisPembelian = $('#jenisPembelianSearch').val();
  let idSupplier = $('#idSupplierSearch').val();
  window.open('excel/?rentang='+rentang+'&idCabang='+idCabang+'&tipe='+tipe+'&jenisPembelian='+jenisPembelian+'&idSupplier='+idSupplier,'_blank');
}

function dataDaftarPembelian(parameterOrder){ 
  //console.log($('#tipe').val());
  $.ajax({
    url:'datadaftarpembelian.php',
    type:'post',
    data:{
      tipe : $('#tipeSearch').val(),
      rentang : $('#rentang').val(),
      idCabang : $('#idCabangSearch').val(),
      idSupplier : $('#idSupplierSearch').val(),
      jenisPembelian : $('#jenisPembelianSearch').val(),
      parameterOrder : parameterOrder
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      //console.log(tipe);
      $('#dataDaftarPembelian').empty().append(data);
      $('.overlay').hide();
    }
  });
}


function editPembelian(noNota) {
   $("#modalFormPembelian").modal('show');
    $.ajax({
      url:'dataformpembelian.php',
      type:'post',
      data:{
        noNota : noNota,
        parameterOrder : $('#parameterOrder').val(),
        flag           : 'update'
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormPembelian").html(data);
        formItemPembelian();
        getPembelianTersimpan();
      }
    });
}

function formItemPembelian() {
    $.ajax({
      url:'dataformitempembelian.php',
      type:'post',
      data:{

      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormItemPembelian").html(data);
        $('select.select2').select2();
      }
    });
}

function getPembelianTersimpan(){
  $.ajax({
    url:'datadaftarpembeliantersimpan.php',
    type:'post',
    data:{
      noNota : $('#noNota').val(),
      jenisPPN : $('#jenisPPN').val(),
      persenPPN : $('#persenPPN').val(),
      konsumen : $('#konsumen').val()
    },
    success:function(data,status){
      $('#dataDaftarPembelianTersimpan').empty().append(data);
      $('select.select2').select2();
    }
  });
}

function editBarang(idPembelianDetail,konsumen) {
    $.ajax({
      url:'dataformitempembelian.php',
      type:'post',
      data:{
        idPembelianDetail : idPembelianDetail,
        flagDetail        : 'update',
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormItemPembelian").html(data);
        $('select.select2').select2();
        getPembelianTersimpan();
      }
    });
}


function cancelBarang(idPembelianDetail,konsumen){
  //console.log(idPembelianDetail);
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
        url:'prosespembeliandetail.php',
        type:'post',
        data:{
          idPembelianDetail : idPembelianDetail,
          flagDetail : 'cancel'
        },
        success:function(data,status){   
          let dataJSON = JSON.parse(data);
          console.log(dataJSON);
          notifikasi(dataJSON.notifikasi);
          getPembelianTersimpan();
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

function cancelPembelian(noNota){
   swal({
    title: "Apakah anda yakin ingin membatalkan Pembelian ini?",
    text: "Setelah dibatalkan, proses tidak dapat diulangi!",
    icon: "warning",
    buttons: true,
    dangerMode: true,
  })
  .then((willDelete) => {
    if(willDelete){
      $.ajax({
        url:'prosespembelian.php',
        type:'post',
        data:{
          noNota : noNota,
          flag : 'cancel'
        },
        success:function(data,status){
        //console.log(data);   
          let dataJSON = JSON.parse(data);
          console.log(dataJSON);
          notifikasi(dataJSON.notifikasi);
          var parameterOrder = $('#parameterOrder').val(); 
          dataDaftarPembelian(parameterOrder);
        }
      });
    } 
    else{
      swal({
        title: "Proses pembatalan Pembelian dibatalkan!",
        icon: "warning"
      });
    }
  });
}

function getNilai() {
  var hargaSatuan = $('#hargaSatuan').val();
  var qty = $('#qty').val();
  var diskon = $('#diskon').val();

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

function prosesPembelianDetail(konsumen) {
  const dataForm = new FormData();
  dataForm.append('noNota',$('#noNota').val());
  dataForm.append('jenisOrder',$('#jenisOrder').val());
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
    url:'prosespembeliandetail.php',
    type:'post',
    enctype: 'multipart/form-data',
    processData: false,
    contentType: false,
    data:dataForm,
    success:function(data,status){  
      //console.log(data);      
      let dataJSON = JSON.parse(data);
      console.log(dataJSON);
      notifikasi(dataJSON.notifikasi);
      resetFormDetail(dataJSON.notifikasi);
      getPembelianTersimpan();
    }
  });
 }
}


function showPersenPPN(){ 
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

function prosesPembelian() {
  //console.log($('#noNotaVendor').val());
  let formPembelian = document.getElementById('formPembelian');
  let dataForm  = new FormData(formPembelian);

  const validasi = formValidation(dataForm,["noFakturPajak"]);
  if ( validasi=== true){
    $.ajax({
      url:'prosespembelian.php',
      type:'post',
      data:{
        noNota : $('#noNota').val(),
        idPembelian : $('#idPembelian').val(),
        idCabang : $('#idCabang').val(),
        flag    : $('#flag').val(),
        tanggalPembelian : $('#tanggalPembelian').val(),
        idSupplier : $('#idSupplier').val(),
        namaSupplier : $('#namaSupplier').val(),
        noTelpSupplier : $('#noTelpSupplier').val(),
        noFakturPajak : $('#noFakturPajak').val(),
        tipePembelian : $('#tipe').val(),
        konsumen : $('#konsumen').val(),
        noNotaVendor  : $('#noNotaVendor').val(),
        idCabang : $('#idCabang').val(),
        jenisPPN : $('#jenisPPN').val(),
        persenPPN : $('#persenPPN').val(),
        nilaiPPN : $('#ppn').val(),
        jatuhTempo : $('#jatuhTempo').val(),
        grandTotal : $('#grandTotal').val(),
        jumlahPembayaran : $('#jumlahPembayaran').val(),
        bankAsalTransfer : $('#bankAsalTransfer').val(),
        jenisPembayaran : $('#jenisPembayaran').val(),
      },
      success:function(data,status){          
        let dataJSON = JSON.parse(data);
        console.log(dataJSON);
        notifikasi(dataJSON.notifikasi);
        dataDaftarPembelian();
        $('#modalFormPembelian').modal('hide');
      }
    });
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

function resetFormDetail(sukses){
  if(sukses == 1){
    $('#namaBarang').val('');
    $('#hargaSatuan').val('');
    $('#qty').val('');
    $('#nilai').val('');
  }
}

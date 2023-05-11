$('select.select2').select2();

$('#rentang').daterangepicker({
  locale : {
    format : 'DD-MM-YYYY'
  }
})

function printNota(noNota){
  window.open('print_nota/?noNota='+noNota, '_blank');
}

function printLaporan(){
  let rentang = $('#rentang').val();
  let idCabang = $('#idCabangSearch').val();
  let tipe = $('#tipeSearch').val();
  window.open('print_laporan/?rentang='+rentang+'&idCabang='+idCabang+'&tipe='+tipe,'_blank');
}

function exportExcel(){
  let rentang = $('#rentang').val();
  let idCabang = $('#idCabangSearch').val();
  let tipe = $('#tipeSearch').val();
  window.open('excel/?rentang='+rentang+'&idCabang='+idCabang+'&tipe='+tipe,'_blank');
}

function dataDaftarPenjualan(parameterOrder){ 
  $.ajax({
    url:'datadaftarpenjualan.php',
    type:'post',
    data:{
      tipe : $('#tipeSearch').val(),
      rentang : $('#rentang').val(),
      idCabang : $('#idCabangSearch').val(),
      parameterOrder : parameterOrder
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      //console.log(tipe);
      $('#dataDaftarPenjualan').empty().append(data);
      $('.overlay').hide();
    }
  });
}


function finalisasiPenjualan(noNota) {
    $.ajax({
    url:'prosesfinalisasi.php',
    type:'post',
    data:{
      noNota : noNota
    },

    beforeSend:function(){
    },
    success:function(data,status){
      //console.log(data);
      let dataJSON = JSON.parse(data);
      console.log(dataJSON);
      notifikasi(dataJSON.notifikasi);
      if(dataJSON.notifikasi=='1'){
        dataDaftarFinalisasi();
     }  
    }
  });
}

function editPenjualan(noNota,tipe,konsumen) {
  //console.log($('#tipe').val());
   $("#modalFormPenjualan").modal('show');
    $.ajax({
      url:'dataformkasir.php',
      type:'post',
      data:{
        noNota : noNota,
        tipe : tipe,
        konsumen : konsumen,
        flag           : 'update'
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormPenjualan").html(data);
        $('select.select2').select2();
        formItemKasir();
        getBarangTersimpan();
      }
    });
}

function formKasir(tipe,konsumen,noNota) {
  //console.log(tipe,konsumen,noNota);
   //$("#modalFormKasir").modal('show');
    $.ajax({
      url:'dataformkasir.php',
      type:'post',
      data:{
        tipe : tipe,
        konsumen : konsumen,
        noNota : noNota,
        flag           : ''
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormKasir").html(data);

        $('select.select2').select2();
        formItemKasir();
        getBarangTersimpan();
      }
    });
}


function formItemKasir() {
    $.ajax({
      url:'dataformitemkasir.php',
      type:'post',
      data:{
        noNota : $('#noNota').val(),
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormItemKasir").html(data);
        $('select.select2').select2();
        $('#jenisOrder').focus();
      }
    });
}

function editBarang(id) {
    $.ajax({
      url:'dataformitemkasir.php',
      type:'post',
      data:{
        idPenjualanDetail : id,
        flagDetail              : 'update'
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormItemKasir").html(data);
        $('select.select2').select2();
        showJenisPenjualan();
      }
    });
}

function getNilai() {
  var hargaSatuan = $('#hargaSatuan').val();
  var qty = $('#qty').val();

  hargaSatuan = hargaSatuan || '0';
  hargaSatuan =  accounting.unformat(hargaSatuan, ",");
  hargaSatuan = parseInt(hargaSatuan);

  qty = qty || '0';
  qty =  accounting.unformat(qty, ",");
  qty = parseInt(qty);

  var nilai = (qty*hargaSatuan);
  nilai = accounting.formatNumber(nilai,{thousand : ".", decimal  : ","});
  $('#nilai').val(nilai);
} 


function prosesPenjualanDetail() {
  const dataForm = new FormData();
  dataForm.append('noNota',$('#noNota').val());
  dataForm.append('jenisOrder',$('#jenisOrder').val());
  dataForm.append('jenisPenjualan',$('#jenisPenjualan').val());
  dataForm.append('supplierSub',$('#supplierSub').val());
  dataForm.append('idCabangAdvertising',$('#idCabangAdvertising').val());
  dataForm.append('namaBahan',$('#namaBahan').val());
  dataForm.append('ukuran',$('#ukuran').val());
  dataForm.append('finishing',$('#finishing').val());
  dataForm.append('qty',$('#qty').val());
  dataForm.append('hargaSatuan',$('#hargaSatuan').val());
  dataForm.append('nilai',$('#nilai').val());
  dataForm.append('flagDetail',$('#flagDetail').val());
  dataForm.append('idPenjualanDetail',$('#idPenjualanDetail').val());

  const validasi = formValidation(dataForm,['idCabangAdvertising','supplierSub']);
    if ( validasi=== true){
      $.ajax({
      url:'prosespenjualandetail.php',
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
        console.log(dataJSON);
        notifikasi(dataJSON.notifikasi);
        if(dataJSON.notifikasi=='1'){
          getBarangTersimpan();
          formItemKasir();
       }
        
      }
    });
  }
}

function cancelBarang(idPenjualanDetail){
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
        url:'prosespenjualandetail.php',
        type:'post',
        data:{
          idPenjualanDetail : idPenjualanDetail,
          flagDetail : 'cancel'
        },
        success:function(data,status){
        //console.log(data);  
          let dataJSON = JSON.parse(data);
          notifikasi(dataJSON.notifikasi);
          getBarangTersimpan();
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

function getKembalian() {
  var grandTotal = $('#grandTotal').val();
  var pembayaran = $('#pembayaran').val();

  grandTotal = grandTotal || '0';
  pembayaran = pembayaran || '0';


  pembayaran = accounting.unformat(pembayaran, ",");
  pembayaran = parseInt(pembayaran);

  grandTotal = accounting.unformat(grandTotal, ",");
  grandTotal = parseInt(grandTotal);

  var kembalian = pembayaran-grandTotal;
  if(kembalian<0){
    kembalian=0;
  }
  
  kembalian = accounting.formatNumber(kembalian,{thousand : ".", decimal  : ","});
  $('#kembalian').val(kembalian);
}

function statusKembalian() {
  var grandTotal = $('#grandTotal').val();
  var pembayaran = $('#pembayaran').val();

  grandTotal = grandTotal || '0';
  pembayaran = pembayaran || '0';


  pembayaran = accounting.unformat(pembayaran, ",");
  pembayaran = parseInt(pembayaran);

  grandTotal = accounting.unformat(grandTotal, ",");
  grandTotal = parseInt(grandTotal);

  var kembalian = pembayaran-grandTotal;
  if(kembalian<0){
    statusPembayaran='Belum Lunas';
  }
  else{
    statusPembayaran='Lunas';
  }
 
  $('#statusPembayaran').val(statusPembayaran);
}

function getBarangTersimpan(){
  //console.log(noNota);
  $.ajax({
    url:'datadaftarbarangtersimpan.php',
    type:'post',
    data:{
      noNota : $('#noNota').val(),
      jenisPPN : $('#jenisPPN').val(),
      persenPPN : $('#persenPPN').val() || 11,
    },
    success:function(data,status){
      //console.log(data);
      $('#dataDaftarBarangTersimpan').empty().append(data);
      $('select.select2').select2();
      $('#jenisOrder').focus();
    }
  });
}

function prosesPenjualan() {
  var noNota = $('#noNota').val();
  let formKopKasir = document.getElementById('formKopKasir');
  let dataForm  = new FormData(formKopKasir);
  //dataForm.append('noNota',noNota);

  const validasi = formValidation(dataForm);
  if ( validasi=== true){
    $.ajax({
      url:'prosespenjualan.php',
      type:'post',
      data:{
        noNota : $('#noNota').val(),
        idPenjualan : $('#idPenjualan').val(),
        idCabang : $('#idCabang').val(),
        konsumen : $('#konsumen').val(),
        statusFakturPajak : $('#statusFakturPajak').val(),
        tipePenjualan : $('#tipePenjualan').val(),
        tanggalPenjualan : $('#tanggalPenjualan').val(),
        idCustomer : $('#idCustomer').val(),
        namaCustomer : $('#namaCustomer').val(),
        noTelpCustomer : $('#noTelpCustomer').val(),
        jenisPembayaran : $('#jenisPembayaran').val(),
        bankTujuanTransfer : $('#bankTujuanTransfer').val(),
        jenisPPN : $('#jenisPPN').val(),
        nilaiPPN : $('#nilaiPPN').val(),
        persenPPN : $('#persenPPN').val(),
        grandTotal : $('#grandTotal').val(),
        jumlahPembayaranAwal : $('#pembayaran').val(),
        lamaSelesai : $('#lamaSelesai').val(),
        statusPembayaran : $('#statusPembayaran').val(),
        statusInput : $('#statusInput').val(),
        idDesigner : $('#idDesigner').val(),
        flag : $('#flag').val()
      },
      success:function(data,status){ 
      //console.log(data);       
        let dataJSON = JSON.parse(data);
        notifikasi(dataJSON.notifikasi);
        formKasir(dataJSON.tipePenjualan,dataJSON.konsumen);
        dataDaftarPenjualan();
      }
    });
  }
  else {
     notifikasi(2, "Proses Gagal, Form Belum Terisi Dengan Lengkap");
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

function showJenisPenjualan(){ 
  var jenisPenjualan = $('#jenisPenjualan').val();
  var jenisOrder = $('#jenisOrder').val();

  if(jenisOrder=='Advertising'){
    $('#idCabangAdvertisingShow').show();
    $('#idCabangAdvertising').val($('#dataUpdateIdCabangAdvertising').val()).trigger('change');
  }
  else{
    $('#idCabangAdvertisingShow').hide();
    $('#idCabangAdvertising').val('0').trigger('change');
  }

  if(jenisPenjualan=='Sub'){
    $('#supplierSub').show();
    $('#supplierSub').val($('#dataUpdateSupplierSub').val());
  }
  else{
    $('#supplierSub').hide();
    $('#supplierSub').val('');
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


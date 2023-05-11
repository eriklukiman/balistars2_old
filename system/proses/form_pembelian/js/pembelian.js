$(function () {
  $(document).on('click', '.tombolPembelian', function (event) {
    $('.tombolPembelian.btn-success').removeClass('btn-success').addClass('btn-primary');
    $(this).removeClass('btn-primary').addClass('btn-success');
  })
})

function tambahPembelian(tipe,konsumen) {
    $.ajax({
      url:'dataformpembelian.php',
      type:'post',
      data:{
        tipe   : tipe,
        konsumen: konsumen,
        parameterOrder : $('#parameterOrder').val(),
        flag           : ''
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormPembelian").html(data);
        $('select.select2').select2();
        // $('.date').datepicker({
        //   startDate: 'd'
        // });
        formItemPembelian(konsumen,tipe);
        getPembelianTersimpan(konsumen,tipe);  
      }
    });
}

function formItemPembelian(konsumen,tipe) {
    $.ajax({
      url:'dataformitempembelian.php',
      type:'post',
      data:{
        konsumen : konsumen,
        tipe      : tipe
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormItemPembelian").html(data);
        $('select.select2').select2();
      }
    });
}

function getPembelianTersimpan(konsumen,tipe){
  $.ajax({
    url:'datadaftarpembeliantersimpan.php',
    type:'post',
    data:{
      noNota : $('#noNota').val(),
      jenisPPN : $('#jenisPPN').val(),
      persenPPN : $('#persenPPN').val(),
      konsumen : konsumen,
      tipe      : tipe
    },
    success:function(data,status){
      $('#dataDaftarPembelianTersimpan').empty().append(data);
      $('select.select2').select2();
    }
  });
}

function editBarang(idPembelianDetail,konsumen,tipe) {
    $.ajax({
      url:'dataformitempembelian.php',
      type:'post',
      data:{
        idPembelianDetail : idPembelianDetail,
        flagDetail        : 'update',
        konsumen          : konsumen,
        tipe              : tipe
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormItemPembelian").html(data);
        $('select.select2').select2();
        getPembelianTersimpan(konsumen,tipe);
      }
    });
}


function cancelBarang(idPembelianDetail,konsumen,tipe){
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
          getPembelianTersimpan(konsumen,tipe);
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

function prosesPembelianDetail(konsumen,tipe) {
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
      getPembelianTersimpan(konsumen,tipe);
      formItemPembelian(konsumen,tipe)
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

  const validasi = formValidation(dataForm);
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
        console.log(dataJSON.konsumen);
        console.log(dataJSON);
        notifikasi(dataJSON.notifikasi);
        tambahPembelian(dataJSON.tipePembelian,dataJSON.konsumen);
      }
    });
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

$('select.select2').select2();

$("#rentang").daterangepicker({
      locale: {
          format: 'DD-MM-YYYY'
      }
    });

function printLaporan(){ 
  let rentang = $('#rentang').val();
  let idCabang = $('#idCabangSearch').val();
  let tipe = $('#tipe').val();
  window.open('print_laporan/?rentang='+rentang+'&idCabang='+idCabang+'&tipe='+tipe,'_blank');
}

function exportExcel(){ 
  let rentang = $('#rentang').val();
  let idCabang = $('#idCabangSearch').val();
  let tipe = $('#tipe').val();
  window.open('excel/?rentang='+rentang+'&idCabang='+idCabang+'&tipe='+tipe,'_blank');
}

function dataDaftarBiaya(parameterOrder){ 
  $.ajax({
    url:'datadaftarbiaya.php',
    type:'post',
    data:{
      tipe : $('#tipe').val(),
      rentang : $('#rentang').val(),
      idCabang : $('#idCabangSearch').val(),
      parameterOrder : parameterOrder
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      //console.log(tipe);
      $('#dataDaftarBiaya').empty().append(data);
      $('.overlay').hide();
    }
  });
}

function editBiaya(noNota) {
  //console.log($('#tipe').val());
   $("#modalFormBiaya").modal('show');
    $.ajax({
      url:'dataformbiaya.php',
      type:'post',
      data:{
        noNota : noNota,
        parameterOrder : $('#parameterOrder').val(),
        flag           : 'update'
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormBiaya").html(data);
        $('select.select2').select2();
        formItemBiaya();
        getBiayaTersimpan();
      }
    });
}

function formItemBiaya() {
    $.ajax({
      url:'dataformitembiaya.php',
      type:'post',
      data:{

      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormItemBiaya").html(data);
        $('select.select2').select2();
      }
    });
}

function editBarang(id) {
    $.ajax({
      url:'dataformitembiaya.php',
      type:'post',
      data:{
        idBiayaDetail : id,
        flagDetail          : 'update'
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormItemBiaya").html(data);
        $('select.select2').select2();
        getBiayaTersimpan();
      }
    });
}


function cancelBarang(idBiayaDetail){
  console.log(idBiayaDetail);
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
        url:'prosesbiayadetail.php',
        type:'post',
        data:{
          idBiayaDetail : idBiayaDetail,
          flagDetail : 'cancel'
        },
        success:function(data,status){   
          let dataJSON = JSON.parse(data);
          console.log(dataJSON);
          notifikasi(dataJSON.notifikasi);
          getBiayaTersimpan();
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

function cancelBiaya(noNota){
   swal({
    title: "Apakah anda yakin ingin membatalkan biaya ini?",
    text: "Setelah dibatalkan, proses tidak dapat diulangi!",
    icon: "warning",
    buttons: true,
    dangerMode: true,
  })
  .then((willDelete) => {
    if(willDelete){
      $.ajax({
        url:'prosesbiaya.php',
        type:'post',
        data:{
          noNota : noNota,
          flag : 'cancel'
        },
        success:function(data,status){   
          let dataJSON = JSON.parse(data);
          console.log(dataJSON);
          notifikasi(dataJSON.notifikasi);
          var parameterOrder = $('#parameterOrder').val(); 
          dataDaftarBiaya(parameterOrder);
        }
      });
    } 
    else{
      swal({
        title: "Proses pembatalan biaya dibatalkan!",
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

  var nilai = (qty*hargaSatuan);
  nilai = accounting.formatNumber(nilai,{thousand : ".", decimal  : ","});
  $('#nilai').val(nilai);
} 


function prosesBiayaDetail() {
  const dataForm = new FormData();
  dataForm.append('noNota',$('#noNota').val());
  dataForm.append('keterangan',$('#keterangan').val());
  dataForm.append('hargaSatuan',$('#hargaSatuan').val());
  dataForm.append('qty',$('#qty').val());
  dataForm.append('nilai',$('#nilai').val());
  dataForm.append('flagDetail',$('#flagDetail').val());
  dataForm.append('idBiayaDetail',$('#idBiayaDetail').val());
  const validasi = formValidation(dataForm);
  if ( validasi=== true){
     $.ajax({
      url:'prosesbiayadetail.php',
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
        formItemBiaya();
        getBiayaTersimpan();
        dataDaftarBiaya();
      }
    });
  }
  else {
     notifikasi(2, "Proses Gagal, Form Belum Terisi Dengan Lengkap");
  }
}


function getBiayaTersimpan(){
  console.log($('#jenisPPN').val(),$('#persenPPN').val())
  $.ajax({
    url:'datadaftarbiayatersimpan.php',
    type:'post',
    data:{
      noNota : $('#noNota').val(),
      jenisPPN : $('#jenisPPN').val(),
      persenPPN : $('#persenPPN').val() || 11,
      tipe : $('#tipe').val()
    },
    success:function(data,status){
      $('#dataDaftarBiayaTersimpan').empty().append(data);
      $('select.select2').select2();
      $('#keterangan').focus();
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

function prosesBiaya() {
  let formBiaya = document.getElementById('formBiaya');
  let dataForm  = new FormData(formBiaya);

  const validasi = formValidation(dataForm);
  if ( validasi=== true){
    $.ajax({
      url:'prosesbiaya.php',
      type:'post',
      data:{
        noNota : $('#noNota').val(),
        idBiaya : $('#idBiaya').val(),
        idPegawai : $('#idPegawai').val(),
        flag    : $('#flag').val(),
        tipeBiaya    : $('#tipeBiaya').val(),
        tanggalBiaya : $('#tanggalBiaya').val(),
        noNotaBiaya  : $('#noNotaBiaya').val(),
        idCabang : $('#idCabang').val(),
        jenisPPN : $('#jenisPPN').val(),
        persenPPN : $('#persenPPN').val(),
        ppn : $('#ppn').val(),
        grandTotal : $('#grandTotal').val(),
        kodeAkunting : $('#kodeAkunting').val(),
      },
      success:function(data,status){    
        //console.log(data);    
        let dataJSON = JSON.parse(data);
        console.log(dataJSON);
        notifikasi(dataJSON.notifikasi);
        dataDaftarBiaya();
        // if(dataJSON.flag == 'update'){
        //   $("#modalFormBiaya").modal('hide');
        // }
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
    $('#keterangan').val('');
    $('#hargaSatuan').val('');
    $('#qty').val('');
    $('#nilai').val('');
  }
}

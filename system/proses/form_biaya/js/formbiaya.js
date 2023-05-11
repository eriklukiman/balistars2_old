$(function () {
  $(document).on('click', '.tombolBiaya', function (event) {
    $('.tombolBiaya.btn-success').removeClass('btn-success').addClass('btn-primary');
    $(this).removeClass('btn-primary').addClass('btn-success');
  })
})

$("#rentang").daterangepicker({
      locale: {
          format: 'DD-MM-YYYY'
      }
    });



function tambahBiaya(tipe,noNota) {
  console.log(tipe);
    $.ajax({
      url:'dataformbiaya.php',
      type:'post',
      data:{
        noNota : noNota,
        tipe : tipe,
        flag : ''
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormBiaya").html(data);
        $('select.select2').select2();
        formItemBiaya();
        getBiayaTersimpan(tipe);
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
        tipe          : $('#tipeBiaya').val(),
        flagDetail          : 'update'
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormItemBiaya").html(data);
        $('select.select2').select2();
        getBiayaTersimpan($('#tipeBiaya').val());
      }
    });
}


function cancelBarang(idBiayaDetail){
  const tipe = $('#tipeBiaya').val();
  //console.log(idBiayaDetail);
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
          getBiayaTersimpan(tipe);
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


  hargaSatuan = hargaSatuan || '0';
  hargaSatuan =  accounting.unformat(hargaSatuan, ",");
  hargaSatuan = parseInt(hargaSatuan);

  var nilai = (qty*hargaSatuan);
  nilai = accounting.formatNumber(nilai,{thousand : ".", decimal  : ","});
  $('#nilai').val(nilai);
} 


function prosesBiayaDetail() {
  const tipe = $('#tipeBiaya').val();
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
        notifikasi(dataJSON.notifikasi);
        formItemBiaya();
        getBiayaTersimpan(tipe);
      }
    });
  }
  else {
     notifikasi(2, "Proses Gagal, Form Belum Terisi Dengan Lengkap");
  }
}


function getBiayaTersimpan(tipe){
  //console.log($('#jenisPPN').val(),$('#persenPPN').val())
  $.ajax({
    url:'datadaftarbiayatersimpan.php',
    type:'post',
    data:{
      noNota : $('#noNota').val(),
      jenisPPN : $('#jenisPPN').val(),
      persenPPN : $('#persenPPN').val() || 11,
      tipe : tipe
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
        tambahBiaya(dataJSON.tipe);
        dataDaftarBiaya();
        if(dataJSON.flag == 'update'){
          $("#modalFormBiaya").modal('hide');
        }
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

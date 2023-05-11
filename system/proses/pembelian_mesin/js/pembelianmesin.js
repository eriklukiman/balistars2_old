$(function () {
  $(document).on('click', '.tombolPembelian', function (event) {
    $('.tombolPembelian.btn-success').removeClass('btn-success').addClass('btn-primary');
    $(this).removeClass('btn-primary').addClass('btn-success');
  })
})

function tambahPembelianMesin(tipePembelian) {
  console.log(tipePembelian);
   //$("#modalFormPembelianMesin").modal('show');
    $.ajax({
      url:'dataformpembelianmesin.php',
      type:'post',
      data:{
        tipePembelian : tipePembelian,
        flag           : ''
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormPembelianMesin").html(data);
        $('select.select2').select2();
        // $('.date').datepicker({
        //   startDate: 'd'
        // });
        formItemPembelianMesin();
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

function cancelBarang(idPembelianDetail){
  
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
        tambahPembelianMesin(dataJSON.tipePembelian);
        //dataDaftarPembelianMesin('noNota');
        // if(dataJSON.flag == 'update'){
        //   $("#modalFormPembelianMesin").modal('hide');
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
    $('#namaBarang').val('');
    $('#hargaSatuan').val('');
    $('#qty').val('');
    $('#nilai').val('');
  }
}

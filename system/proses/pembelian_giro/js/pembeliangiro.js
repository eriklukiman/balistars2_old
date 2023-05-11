function dataDaftarPembelianGiro(){
  //console.log($('#parameterOrder').val());
  $.ajax({
    url:'datadaftarpembeliangiro.php',
    type:'post',
    data:{
      bulan : $('#bulan').val(),
      tahun : $('#tahun').val(),
      tipe : $('#tipeSearch').val(),
      parameterOrder : $('#parameterOrder').val()
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarPembelianGiro').empty().append(data);
      //$('#parameterOrder').val(parameterOrder);
      $('.overlay').hide();
    }
  });
}

function tambahPembelianGiro(noNota,idSupplier,tipe,tanggalAwal,sisaPembelian,dataNoNota) {
  console.log(noNota);
  $("#modalFormPembelianGiro").modal('show');
  $(".modal-title").empty().text("Pembelian Giro");

  $.ajax({
    url:'dataformpembeliangiro.php',
    type:'post',
    data:{
      noNota      : noNota,
      idSupplier  : idSupplier,
      tipePembelian : tipe,
      tanggalAwal : tanggalAwal,
      sisaPembelian : sisaPembelian,
      dataNoNota  : dataNoNota
    },
    beforeSend:function(){
    },
    success:function(data,status){
      $("#dataFormPembelianGiro").empty().html(data);
      $('select.select2').select2();
       }
  });
}

function tambahDpGiro(id,nama,total,tipe,periode,disabled,idDpGiro='') {
  //console.log(noNota);

  $("#modalFormPembelianGiro").modal('show');
  $(".modal-title").empty().text("DP Pembelian Giro");

  $.ajax({
    url:'dataformdpgiro.php',
    type:'post',
    data:{
      idSupplier            : id,
      namaSupplier          : nama,
      total                 : total,
      tipePembelianDp       : tipe,
      periode               : periode,
      disabled              : disabled,
      idDpGiro              : idDpGiro
    },
    beforeSend:function(){
    },
    success:function(data,status){
      $("#dataFormPembelianGiro").empty().html(data);
      $('select.select2').select2();
       }
  });
}

function finalPembelianGiro(dataNoNota,idDpGiro) {
  $.ajax({
    url:'prosespembeliangiro.php',
    type:'post',
    data:{
      dataNoNota  : dataNoNota,
      flag            : 'finalisasi',
      idDpGiro    : idDpGiro,
    },
    beforeSend:function(){
    },
    success:function(data,status){
      let dataJSON = JSON.parse(data);
      dataDaftarPembelianGiro();
      notifikasi(dataJSON.status,dataJSON.notifikasi);
      $('.overlay').hide();
      }
  });
}

function prosesTambahDpGiro(){

  let formTambahDp = document.getElementById('formTambahDp');
  let dataForm           = new FormData(formTambahDp);
  const validasi = formValidation(dataForm);
  if (validasi === true) {
    $.ajax({
      url:'prosestambahdpgiro.php',
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
        dataDaftarPembelianGiro();
        tambahDpGiro(dataJSON.idSupplier,dataJSON.namaSupplier,dataJSON.total,dataJSON.tipePembelianDp,dataJSON.periode,dataJSON.disabled);
        notifikasi(dataJSON.status,dataJSON.notifikasi);
      }
    });
  }
}

function cancelDpGiro(idDpGiro,id,nama,total,tipe,periode,disabled){
    $.ajax({
      url:'prosestambahdpgiro.php',
      type:'post',
      data:{
        idDpGiro  : idDpGiro,
        idSupplier            : id,
        namaSupplier          : nama,
        total                 : total,
        tipePembelianDp       : tipe,
        periode               :periode,
        disabled              : disabled,
        flag           : 'cancel'
      },

      success:function(data,status){
        let dataJSON=JSON.parse(data);
        dataDaftarPembelianGiro();
        tambahDpGiro(dataJSON.idSupplier,dataJSON.namaSupplier,dataJSON.total,dataJSON.tipePembelianDp,dataJSON.periode,dataJSON.disabled);
      }
    });
  }

function prosesPembelianGiro(){

  let formPembelianGiro = document.getElementById('formPembelianGiro');
  let dataForm           = new FormData(formPembelianGiro);

  const validasi = formValidation(dataForm);
  if (validasi === true) {
    $.ajax({
      url:'prosespembeliangiro.php',
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
        dataDaftarPembelianGiro(dataJSON.parameterOrder);
        notifikasi(dataJSON.status,dataJSON.notifikasi);
        resetForm(dataJSON.status);
        if(dataJSON.status==true){
          $('#modalFormPembelianGiro').modal('hide');
        }
      }
    });
  }
  else {
     notifikasi(2, "Proses Gagal, Form Belum Terisi Dengan Lengkap");
  }
}

function cancelFinalisasi(noGiro){
  swal({
    title: "Apakah anda yakin ingin Membuka Pembelian ini?",
    text: "Setelah dibatalkan, proses tidak dapat diulangi!",
    icon: "warning",
    buttons: true,
    dangerMode: true,
  })
  .then((willDelete) => {
    if(willDelete){
      
      $.ajax({
        url:'prosespembeliangiro.php',
        type:'post',
        data:{
          noGiro  : noGiro,
          flag           : 'cancel'
        },

        success:function(data,status){
          let dataJSON=JSON.parse(data);
          dataDaftarPembelianGiro();
          notifikasi(dataJSON.status,dataJSON.notifikasi);
        }
      });
    } 
    else{
      swal({
        title: "Proses pembatalan Gedung dibatalkan!",
        icon: "warning"
      });
    }
  });
}

// function validasiDp(){
//   const dp = parseInt($('#dp').val());
//   const total = parseInt($('#total').val());

//   $('#dp').removeClass('is-invalid');
//   $('#notifValidasi').text('')
//   if(dp > total){
//     $('#dp').addClass('is-invalid');
//     $('#notifValidasi').text('DP Melebihi Total Pembelian');
//   } 
// }

function resetForm(sukses){
  if(sukses == 1){
    $('input[type=text]').val('');
    $('#flag').val('');
    $('#cabang').val(null).trigger('change');
    $('#jenis').val(null).trigger('change');
    //$('.select2').val(null).trigger('change');
  }
}

function notifikasi(sukses,pesan){
  if(sukses){
    toastr.success(pesan);
  }
  else{
    toastr.error(pesan);
  }
  
}

function dataDaftarPemutihanPiutang(parameterOrder){
  $.ajax({
    url:'datadaftarpemutihanpiutang.php',
    type:'post',
    data:{
      parameterOrder : parameterOrder,
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarPemutihanPiutang').empty().append(data);
      $('#parameterOrder').val(parameterOrder);
      $('.overlay').hide();
    }
  });
}

function tambahPemutihanPiutang() {
  $("#modalFormPemutihanPiutang").modal('show');

  $.ajax({
    url:'dataformpemutihanpiutang.php',
    type:'post',
    data:{
      parameterOrder  : $('#parameterOrder').val(),
      flag            : ''
    },
    beforeSend:function(){
    },
    success:function(data,status){
      $("#dataFormPemutihanPiutang").html(data);
      $('select.select2').select2();
    }
  });
}


function prosesPemutihanPiutang(){
  $.ajax({
    url:'prosespemutihanpiutang.php',
    type:'post',
    data:{
      noNota : $('#noNota').val(),
      tanggalPemutihan : $('#tanggalPemutihan').val(),
      namaCabang : $('#namaCabang').val(),
      namaCustomer : $('#namaCustomer').val(),
      total : $('#total').val(),
      sisaPiutang : $('#sisaPiutang').val(),
      flag : $('#flag').val(),
      idPemutihan : $('#idPemutihan').val()
    },

    beforeSend:function(){
    },

    success:function(data,status){
      //console.log(data);
      let dataJSON = JSON.parse(data);
      dataDaftarPemutihanPiutang(dataJSON.parameterOrder);
      notifikasi(dataJSON.notifikasi);
      resetForm(dataJSON.notifikasi);
      if(dataJSON.flag == 'update'){
        $("#modalFormPemutihanPiutang").modal('hide');
      }
    }
  });
}

function editPemutihanPiutang(id) {
  //console.log(id);
  $("#modalFormPemutihanPiutang").modal('show');

    $.ajax({
      url:'dataformpemutihanpiutang.php',
      type:'post',
      data:{
        parameterOrder  : $('#parameterOrder').val(),
        idPemutihan : id,
        flag            : 'update'
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormPemutihanPiutang").html(data);
        $('select.select2').select2();
        showDataNota();
      }
    });
}

function cancelPemutihanPiutang(id){
  swal({
    title: "Apakah anda yakin ingin membatalkan data PemutihanPiutang ini?",
    text: "Setelah dibatalkan, proses tidak dapat diulangi!",
    icon: "warning",
    buttons: true,
    dangerMode: true,
  })
  .then((willDelete) => {
    if(willDelete){
      
      $.ajax({
        url:'prosespemutihanpiutang.php',
        type:'post',
        data:{
          idPemutihan  : id,
          parameterOrder : $('#parameterOrder').val(),
          flag           : 'cancel'
        },

        success:function(data,status){
          let dataJSON=JSON.parse(data);
          dataDaftarPemutihanPiutang(dataJSON.parameterOrder);
          notifikasi(dataJSON.notifikasi);
        }
      });
    } 
    else{
      swal({
        title: "Proses pembatalan PemutihanPiutang dibatalkan!",
        icon: "warning"
      });
    }
  });
}

function showDataNota(){
  let noNota=$('#noNota').val();

  //console.log(noNota);
  $.ajax({
    url:'datanotatersimpan.php',
    type:'post',
    data:{
      noNota:noNota
    },

    success:function(data,status){
      $('#dataNotaTersimpan').html(data);
    }

  });
}

function resetForm(sukses){
  if(sukses == 1){
    $('input[type=text]').val('');
    $('#flag').val('');
    $('.select2').val(null).trigger('change');
    
  }
}

function notifikasi(sukses){
  if(sukses == 1){
    toastr.success('Proses Data Berhasil');
  }
  else if(sukses == 2){
    toastr.error('Proses Data Gagal');
  }
  else if(sukses == 3){
    toastr.error('Proses Gagal, Piutang Sudah Diputihkan');
  }
  else if(sukses == 4){
    toastr.error('Proses Gagal, tidak ada Piutang yang Diputihkan');
  }
}


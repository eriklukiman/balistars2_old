function dataDaftarSewaGedung(){
  $.ajax({
    url:'datadaftarsewagedung.php',
    type:'post',
    data:{
      rentang : $('#rentang').val()
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarSewaGedung').empty().append(data);
      $('#parameterOrder').val(parameterOrder);
      $('.overlay').hide();
    }
  });
}

function tambahSewaGedung() {
  $("#modalFormSewaGedung").modal('show');

  $.ajax({
    url:'dataformsewagedung.php',
    type:'post',
    data:{
      flag            : ''
    },
    beforeSend:function(){
    },
    success:function(data,status){
      //console.log(data);
      $("#dataFormSewaGedung").html(data);
      $('select.select2').select2();
    }
  });
}

function editSewaGedung(id,noNota) {
  $("#modalFormSewaGedung").modal('show');

    $.ajax({
      url:'dataformsewagedung.php',
      type:'post',
      data:{
        idHutangGedung  : id,
        noNota          : noNota,
        flag            : 'update'
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormSewaGedung").html(data);
        $('select.select2').select2();
      }
    });
}


function prosesSewaGedung(){

  let formSewaGedung = document.getElementById('formSewaGedung');
  let dataForm           = new FormData(formSewaGedung);

  const validasi = formValidation(dataForm);
  if (validasi === true) {
    $.ajax({
      url:'prosessewagedung.php',
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
        dataDaftarSewaGedung();
        notifikasi(dataJSON.notifikasi);
        tambahSewaGedung();
        //resetForm(dataJSON.notifikasi);
        if(dataJSON.flag == 'update'){
          $("#modalFormSewaGedung").modal('hide');
        }
      }
    });
  } 
  else {
     notifikasi(2, "Proses Gagal, Form Belum Terisi Dengan Lengkap");
  }
}

function cancelSewaGedung(id,noNota){
  swal({
    title: "Apakah anda yakin ingin membatalkan data SewaGedung ini?",
    text: "Setelah dibatalkan, proses tidak dapat diulangi!",
    icon: "warning",
    buttons: true,
    dangerMode: true,
  })
  .then((willDelete) => {
    if(willDelete){

      let parameterOrder = $('#parameterOrder').val();
      let flag           = 'cancel';
      
      $.ajax({
        url:'prosessewagedung.php',
        type:'post',
        data:{
          idHutangGedung  : id,
          noNota          : noNota,
          parameterOrder : parameterOrder,
          flag           : flag
        },

        success:function(data,status){
          //console.log(data);
          let dataJSON=JSON.parse(data);
          dataDaftarSewaGedung();
          notifikasi(dataJSON.notifikasi);
        }
      });
    } 
    else{
      swal({
        title: "Proses pembatalan Sewa Gedung dibatalkan!",
        icon: "warning"
      });
    }
  });
}

function resetForm(sukses){
  if(sukses == 1){
    $('input[type=text]').val('');
    $('#flag').val('');
    $('.select2').val(null).trigger('change');
    $('#keterangan').val('');
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
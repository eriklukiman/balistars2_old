$("#rentang").daterangepicker({
    locale: {
        format: 'DD-MM-YYYY'
    },
    startDate : moment().startOf('month'),
    endDate   : moment()
  });

function dataDaftarMesinLaser(parameterOrder){
  $.ajax({
    url:'datadaftarmesinlaser.php',
    type:'post',
    data:{
      parameterOrder : parameterOrder,
      rentang : $('#rentang').val()
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarMesinLaser').empty().append(data);
      $('#parameterOrder').val(parameterOrder);
      $('.overlay').hide();
    }
  });
}

function tambahMesinLaser() {
  $("#modalFormMesinLaser").modal('show');

  $.ajax({
    url:'dataformmesinlaser.php',
    type:'post',
    data:{
      parameterOrder  : $('#parameterOrder').val(),
      flag            : ''
    },
    beforeSend:function(){
    },
    success:function(data,status){
      $("#dataFormMesinLaser").html(data);
      $('select.select2').select2();
    }
  });
}

// function editMesinLaser(id) {
//   $("#modalFormMesinLaser").modal('show');

//     $.ajax({
//       url:'dataformMesinLaser.php',
//       type:'post',
//       data:{
//         parameterOrder  : $('#parameterOrder').val(),
//         idMesinLaser       : id,
//         flag            : 'update'
//       },
//       beforeSend:function(){
//       },
//       success:function(data,status){
//         $("#dataFormMesinLaser").html(data);
//         $('select.select2').select2();
//       }
//     });
// }


function prosesMesinLaser(){

  let formMesinLaser = document.getElementById('formMesinLaser');
  let dataForm           = new FormData(formMesinLaser);

  const validasi = formValidation(dataForm);
  if (validasi === true) {
    $.ajax({
      url:'prosesmesinlaser.php',
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
        dataDaftarMesinLaser(dataJSON.parameterOrder);
        notifikasi(dataJSON.notifikasi);
        tambahMesinLaser();
        if(dataJSON.flag == 'update'){
          $("#modalFormMesinLaser").modal('hide');
        }
      }
    });
  } 
  else {
     notifikasi(2, "Proses Gagal, Form Belum Terisi Dengan Lengkap");
  }
}

function stopMesinLaser(){
  swal({
    title: "Apakah anda yakin ingin STOP data Mesin Laser ini?",
    icon: "warning",
    buttons: true,
    dangerMode: true,
  })
  .then((willDelete) => {
    if(willDelete){

      let formMesinLaser = document.getElementById('formMesinLaser');
      let dataForm           = new FormData(formMesinLaser);
      dataForm.set('flag','stop');

      const validasi = formValidation(dataForm);
      if (validasi === true) {
        $.ajax({
          url:'prosesmesinlaser.php',
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
            dataDaftarMesinLaser(dataJSON.parameterOrder);
            notifikasi(dataJSON.notifikasi);
            tambahMesinLaser();
            if(dataJSON.flag == 'update'){
              $("#modalFormMesinLaser").modal('hide');
            }
          }
        });
      } 
      else {
         notifikasi(2, "Proses Gagal, Form Belum Terisi Dengan Lengkap");
      }
    } 
    else{
      swal({
        title: "Proses pembatalan MesinLaser dibatalkan!",
        icon: "warning"
      });
    }
  });
}

function cancelMesinLaser(id){
  swal({
    title: "Apakah anda yakin ingin membatalkan data MesinLaser ini?",
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
        url:'prosesMesinLaser.php',
        type:'post',
        data:{
          idPerformaLaser  : id,
          parameterOrder : parameterOrder,
          flag           : flag
        },

        success:function(data,status){
          console.log(id);
          let dataJSON=JSON.parse(data);
          dataDaftarMesinLaser(dataJSON.parameterOrder);
          notifikasi(dataJSON.notifikasi);
        }
      });
    } 
    else{
      swal({
        title: "Proses pembatalan MesinLaser dibatalkan!",
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
    toastr.success('Proses Data MesinLaser Berhasil');
  }
  else if(sukses == 2){
    toastr.error('Proses Data MesinLaser Gagal');
  }
}
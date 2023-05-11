$("#rentang").daterangepicker({
    locale: {
        format: 'DD-MM-YYYY'
    },
    startDate : moment().startOf('month'),
    endDate   : moment()
  });

function dataDaftarMesinBW(parameterOrder){
  $.ajax({
    url:'datadaftarmesinbw.php',
    type:'post',
    data:{
      parameterOrder : parameterOrder,
      rentang : $('#rentang').val()
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarMesinBW').empty().append(data);
      $('#parameterOrder').val(parameterOrder);
      $('.overlay').hide();
    }
  });
}

function tambahMesinBW() {
  $("#modalFormMesinBW").modal('show');

  $.ajax({
    url:'dataformmesinbw.php',
    type:'post',
    data:{
      parameterOrder  : $('#parameterOrder').val(),
      flag            : ''
    },
    beforeSend:function(){
    },
    success:function(data,status){
      $("#dataFormMesinBW").html(data);
      $('select.select2').select2();
    }
  });
}

// function editMesinBW(id) {
//   $("#modalFormMesinBW").modal('show');

//     $.ajax({
//       url:'dataformMesinBW.php',
//       type:'post',
//       data:{
//         parameterOrder  : $('#parameterOrder').val(),
//         idMesinBW       : id,
//         flag            : 'update'
//       },
//       beforeSend:function(){
//       },
//       success:function(data,status){
//         $("#dataFormMesinBW").html(data);
//         $('select.select2').select2();
//       }
//     });
// }


function prosesMesinBW(){

  let formMesinBW = document.getElementById('formMesinBW');
  let dataForm           = new FormData(formMesinBW);

  const validasi = formValidation(dataForm);
  if (validasi === true) {
    $.ajax({
      url:'prosesmesinbw.php',
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
        dataDaftarMesinBW(dataJSON.parameterOrder);
        notifikasi(dataJSON.notifikasi);
        tambahMesinBW();
        if(dataJSON.flag == 'update'){
          $("#modalFormMesinBW").modal('hide');
        }
      }
    });
  } 
  else {
     notifikasi(2, "Proses Gagal, Form Belum Terisi Dengan Lengkap");
  }
}

function stopMesinBW(){
  swal({
    title: "Apakah anda yakin ingin STOP data Mesin BW ini?",
    icon: "warning",
    buttons: true,
    dangerMode: true,
  })
  .then((willDelete) => {
    if(willDelete){

      let formMesinBW = document.getElementById('formMesinBW');
      let dataForm           = new FormData(formMesinBW);
      dataForm.set('flag','stop');

      const validasi = formValidation(dataForm);
      if (validasi === true) {
        $.ajax({
          url:'prosesmesinbw.php',
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
            dataDaftarMesinBW(dataJSON.parameterOrder);
            notifikasi(dataJSON.notifikasi);
            tambahMesinBW();
            if(dataJSON.flag == 'update'){
              $("#modalFormMesinBW").modal('hide');
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
        title: "Proses pembatalan MesinBW dibatalkan!",
        icon: "warning"
      });
    }
  });
}

function cancelMesinBW(id){
  swal({
    title: "Apakah anda yakin ingin membatalkan data MesinBW ini?",
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
        url:'prosesMesinbw.php',
        type:'post',
        data:{
          idPerformaBW  : id,
          parameterOrder : parameterOrder,
          flag           : flag
        },

        success:function(data,status){
          console.log(id);
          let dataJSON=JSON.parse(data);
          dataDaftarMesinBW(dataJSON.parameterOrder);
          notifikasi(dataJSON.notifikasi);
        }
      });
    } 
    else{
      swal({
        title: "Proses pembatalan MesinBW dibatalkan!",
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
    toastr.success('Proses Data MesinBW Berhasil');
  }
  else if(sukses == 2){
    toastr.error('Proses Data MesinBW Gagal');
  }
}
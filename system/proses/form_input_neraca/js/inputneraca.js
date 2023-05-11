$('select.select2').select2();


$("#rentang").daterangepicker({
      locale: {
          format: 'DD-MM-YYYY'
      }
    });

function dataDaftarInputNeraca(parameterOrder){
  //console.log($('#rentang').val());
  $.ajax({
    url:'datadaftarinputneraca.php',
    type:'post',
    data:{
      parameterOrder : parameterOrder,
      rentang : $('#rentang').val(),
      tipeBiaya : $('#tipeBiayaSearch').val()
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarInputNeraca').empty().append(data);
      $('#parameterOrder').val(parameterOrder);
      $('.overlay').hide();
    }
  });
}

function tambahInputNeraca() {
  $("#modalFormInputNeraca").modal('show');

  $.ajax({
    url:'dataforminputneraca.php',
    type:'post',
    data:{
      parameterOrder  : $('#parameterOrder').val(),
      flag            : ''
    },
    beforeSend:function(){
    },
    success:function(data,status){
      $("#dataFormInputNeraca").html(data);
      $('select.select2').select2();
    }
  });
}


function prosesInputNeraca(){

  let formInputNeraca = document.getElementById('formInputNeraca');
  let dataForm           = new FormData(formInputNeraca);

  const validasi = formValidation(dataForm,["keterangan"]);
  if (validasi === true) {
    $.ajax({
      url:'prosesinputneraca.php',
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
        dataDaftarInputNeraca(dataJSON.parameterOrder);
        notifikasi(dataJSON.notifikasi);
        if(dataJSON.flag == 'update'){
          $("#modalFormInputNeraca").modal('hide');
        }
      }
    });
   }
  else {
     notifikasi(2, "Proses Gagal, Form Belum Terisi Dengan Lengkap");
  }
}

function editInputNeraca(id) {
  //console.log(id);
  $("#modalFormInputNeraca").modal('show');

    $.ajax({
      url:'dataforminputneraca.php',
      type:'post',
      data:{
        parameterOrder  : $('#parameterOrder').val(),
        idInputNeraca : id,
        flag            : 'update'
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormInputNeraca").html(data);
        $('select.select2').select2();
      }
    });
}

function cancelInputNeraca(id){
  swal({
    title: "Apakah anda yakin ingin membatalkan data InputNeraca ini?",
    text: "Setelah dibatalkan, proses tidak dapat diulangi!",
    icon: "warning",
    buttons: true,
    dangerMode: true,
  })
  .then((willDelete) => {
    if(willDelete){
      
      $.ajax({
        url:'prosesinputneraca.php',
        type:'post',
        data:{
          idInputNeraca  : id,
          parameterOrder : $('#parameterOrder').val(),
          flag           : 'cancel'
        },

        success:function(data,status){
          let dataJSON=JSON.parse(data);
          dataDaftarInputNeraca(dataJSON.parameterOrder);
          notifikasi(dataJSON.notifikasi);
        }
      });
    } 
    else{
      swal({
        title: "Proses pembatalan InputNeraca dibatalkan!",
        icon: "warning"
      });
    }
  });
}

function notifikasi(sukses){
  if(sukses == 1){
    toastr.success('Proses Data InputNeraca Berhasil');
  }
  else if(sukses == 2){
    toastr.error('Proses Data InputNeraca Gagal');
  }
}


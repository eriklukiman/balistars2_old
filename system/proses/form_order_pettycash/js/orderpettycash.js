
function dataDaftarOrderPettyCash(parameterOrder){
  $.ajax({
    url:'datadaftarorderpettycash.php',
    type:'post',
    data:{
      parameterOrder : parameterOrder,
      rentang : $('#rentang').val()
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarOrderPettyCash').empty().append(data);
      $('#parameterOrder').val(parameterOrder);
      $('.overlay').hide();
    }
  });
}

function tambahOrderPettyCash() {
  $("#modalFormOrderPettyCash").modal('show');

  $.ajax({
    url:'dataformorderpettycash.php',
    type:'post',
    data:{
      parameterOrder  : $('#parameterOrder').val(),
      flag            : ''
    },
    beforeSend:function(){
    },
    success:function(data,status){
      $("#dataFormOrderPettyCash").html(data);
      $('select.select2').select2();
      CKEDITOR.replace('keterangan');
    }
  });
}


function prosesOrderPettyCash(){

  let formOrderPettyCash = document.getElementById('formOrderPettyCash');
  let dataForm           = new FormData(formOrderPettyCash);
  // let keterangan         = CKEDITOR.instances['keterangan'].getData();
  // dataForm.set('keterangan',keterangan);

  const validasi = formValidation(dataForm,["keterangan"]);
  if (validasi === true) {
    $.ajax({
      url:'prosesorderpettycash.php',
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
        dataDaftarOrderPettyCash(dataJSON.parameterOrder);
        notifikasi(dataJSON.notifikasi);
        resetForm(dataJSON.notifikasi);
        if(dataJSON.flag == 'update'){
          $("#modalFormOrderPettyCash").modal('hide');
        }
      }
    });
   }
  else {
     notifikasi(2, "Proses Gagal, Form Belum Terisi Dengan Lengkap");
  }
}

function editOrderPettyCash(id) {
  //console.log(id);
  $("#modalFormOrderPettyCash").modal('show');

    $.ajax({
      url:'dataformorderpettycash.php',
      type:'post',
      data:{
        parameterOrder  : $('#parameterOrder').val(),
        idOrderKasKecil : id,
        flag            : 'update'
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormOrderPettyCash").html(data);
        $('select.select2').select2();
        CKEDITOR.replace('keterangan');
      }
    });
}

function cancelOrderPettyCash(id){
  swal({
    title: "Apakah anda yakin ingin membatalkan data OrderPettyCash ini?",
    text: "Setelah dibatalkan, proses tidak dapat diulangi!",
    icon: "warning",
    buttons: true,
    dangerMode: true,
  })
  .then((willDelete) => {
    if(willDelete){
      
      $.ajax({
        url:'prosesorderpettycash.php',
        type:'post',
        data:{
          idOrderKasKecil  : id,
          parameterOrder : $('#parameterOrder').val(),
          flag           : 'cancel'
        },

        success:function(data,status){
          let dataJSON=JSON.parse(data);
          dataDaftarOrderPettyCash(dataJSON.parameterOrder);
          notifikasi(dataJSON.notifikasi);
        }
      });
    } 
    else{
      swal({
        title: "Proses pembatalan Order Petty Cash dibatalkan!",
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
    
  }
}

function notifikasi(sukses){
  if(sukses == 1){
    toastr.success('Proses Data OrderPettyCash Berhasil');
  }
  else if(sukses == 2){
    toastr.error('Proses Data OrderPettyCash Gagal');
  }
}


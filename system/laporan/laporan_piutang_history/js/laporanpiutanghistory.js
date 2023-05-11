$('select.select2').select2();

$('#rentang').daterangepicker({
  locale : {
    format : 'DD-MM-YYYY'
  }
})


function dataDaftarPiutangHistory(parameterOrder){ 
  $.ajax({
    url:'datadaftarpiutanghistory.php',
    type:'post',
    data:{
      tipe : $('#tipeSearch').val(),
      rentang : $('#rentang').val(),
      idCabang : $('#idCabangSearch').val(),
      parameterOrder : parameterOrder
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      //console.log(tipe);
      $('#dataDaftarPiutangHistory').empty().append(data);
      $('.overlay').hide();
    }
  });
}


function editPiutangHistory(id) {
   $("#modalFormPiutangHistory").modal('show');
    $.ajax({
      url:'dataformpiutanghistory.php',
      type:'post',
      data:{
        idPiutang : id
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormPiutangHistory").html(data);
        $('select.select2').select2();
      }
    });
}


function prosesPiutangHistory(){

  let formPiutangHistory = document.getElementById('formPiutangHistory');
  let dataForm           = new FormData(formPiutangHistory);

  const validasi = formValidation(dataForm);
  if (validasi === true) {
    $.ajax({
      url:'prosespiutanghistory.php',
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
        editPiutangHistory(dataJSON.idPiutang);
        notifikasi(dataJSON.notifikasi);
      }
    });
  }
  else {
     notifikasi(2, "Proses Gagal, Form Belum Terisi Dengan Lengkap");
  }
}

function showJenisPembayaran() {
    $.ajax({
        url: "dataformjenispembayaran.php",
        type: "post",
        data: {
            jenisPembayaran: $("#jenisPembayaranSearch").val(),
        },
        beforeSend: function () {},
        success: function (data, status) {
            $("#boxJenisPembayaran").html(data);
            $("select.select2").select2();

        },
    });
}

function notifikasi(sukses){
  if(sukses == 1){
    toastr.success('Proses Data Berhasil');
  }
  else if(sukses == 2){
    toastr.error('Proses Data Gagal');
  }
}


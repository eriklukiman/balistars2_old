
$('select.select2').select2();

function dataDaftarPenyusutanAktiva(parameterOrder){ 
  $.ajax({
    url:'datadaftarpenyusutanaktiva.php',
    type:'post',
    data:{
      bulan : $('#bulan1').val(),
      tahun : $('#tahun1').val(),
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarPenyusutanAktiva').empty().append(data);
      $('.overlay').hide();
    }
  });
}

function jualAktiva(idPembelianDetail) {
  //console.log($('#tipe').val());
   $("#modalFormJualAktiva").modal('show');
    $.ajax({
      url:'dataformjualaktiva.php',
      type:'post',
      data:{
        idPembelianDetail : idPembelianDetail,
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormJualAktiva").html(data);
        $('select.select2').select2();
      }
    });
}

function prosesJualAktiva() {
  let formJualAktiva = document.getElementById('formJualAktiva');
  let dataForm        = new FormData(formJualAktiva);

  const validasi = formValidation(dataForm);
  if ( validasi=== true){
     $.ajax({
      url:'prosesjualaktiva.php',
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
        console.log(dataJSON);
        notifikasi(dataJSON.notifikasi);
        $("#modalFormJualAktiva").modal('hide');
        dataDaftarPenyusutanAktiva();

      }
    });
  }
  else {
     notifikasi(2, "Proses Gagal, Form Belum Terisi Dengan Lengkap");
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

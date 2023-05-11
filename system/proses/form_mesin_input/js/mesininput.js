$('select.select2').select2();

function dataDaftarMesinInput(parameterOrder){
  $.ajax({
    url:'datadaftarmesininput.php',
    type:'post',
    data:{
      parameterOrder : parameterOrder,
      rentang : $('#rentang').val(),
      jenisOrder : $('#jenisOrderSearch').val()
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarMesinInput').empty().append(data);
      $('#parameterOrder').val(parameterOrder);
      $('.overlay').hide();
    }
  });
}

function tambahMesinInput() {
  $("#modalFormMesinInput").modal('show');

  $.ajax({
    url:'dataformmesininput.php',
    type:'post',
    data:{
      parameterOrder  : $('#parameterOrder').val(),
      flag            : ''
    },
    beforeSend:function(){
    },
    success:function(data,status){
      $("#dataFormMesinInput").html(data);
      $('select.select2').select2();
    }
  });
}


function prosesMesinInput(){

  let formMesinInput = document.getElementById('formMesinInput');
  let dataForm           = new FormData(formMesinInput);

  const validasi = formValidation(dataForm,['noNota']);
  if (validasi === true) {
    $.ajax({
      url:'prosesmesininput.php',
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
        dataDaftarMesinInput(dataJSON.parameterOrder);
        notifikasi(dataJSON.notifikasi);
        if(dataJSON.flag == 'update'){
          $("#modalFormMesinInput").modal('hide');
        }
      }
    });
   }
  else {
     notifikasi(2, "Proses Gagal, Form Belum Terisi Dengan Lengkap");
  }
}

function editMesinInput(id) {
  $("#modalFormMesinInput").modal('show');

    $.ajax({
      url:'dataformmesininput.php',
      type:'post',
      data:{
        parameterOrder  : $('#parameterOrder').val(),
        idPerforma       : id,
        flag            : 'update'
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormMesinInput").html(data);
        $('select.select2').select2();
      }
    });
}

function cancelMesinInput(id){
  swal({
    title: "Apakah anda yakin ingin membatalkan data ini?",
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
        url:'prosesmesininput.php',
        type:'post',
        data:{
          idPerforma  : id,
          parameterOrder : parameterOrder,
          flag           : flag
        },

        success:function(data,status){
          let dataJSON=JSON.parse(data);
          dataDaftarMesinInput(dataJSON.parameterOrder);
          notifikasi(dataJSON.notifikasi);
        }
      });
    } 
    else{
      swal({
        title: "Proses pembatalan dibatalkan!",
        icon: "warning"
      });
    }
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

function showLuas() {

  var qty = $('#qty').val();
  var ukuran = $('#ukuran').val();
  var sisi = ukuran.split('x');
  var panjang = parseInt(sisi[0]);
  var lebar = parseInt(sisi[1]);

  qty = qty || '0';
  qty =  accounting.unformat(qty, ",");
  qty = parseInt(qty);

  var luas = parseFloat((qty*panjang*lebar/10000).toFixed(2));
  //luas = accounting.formatNumber(luas,{thousand : ".", decimal  : ","});
  $('#luas').val(luas);
} 
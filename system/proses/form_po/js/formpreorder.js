$(function () {
  $(document).on('click', '.tombolPO', function (event) {
    $('.tombolPO.btn-success').removeClass('btn-success').addClass('btn-primary');
    $(this).removeClass('btn-primary').addClass('btn-success');
  })
})

function formPreorder(konsumen,flag,noPo,rentang) {
  console.log(noPo);
    $.ajax({
      url:'dataformpreorder.php',
      type:'post',
      data:{
        konsumen : konsumen,
        noPo     : noPo,
        rentang  : rentang,
        flag     : flag
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormPreorder").html(data);
        $('select.select2').select2();
        formItemPreorder();
        getBarangTersimpan();
      }
    });
}

function formItemPreorder() {
    $.ajax({
      url:'dataformitempreorder.php',
      type:'post',
      data:{
        noPo : $('#noPo').val(),
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormItemPreorder").html(data);
        $('select.select2').select2();
      }
    });
}

function getNilai() {
  var hargaSatuan = $('#hargaSatuan').val();
  var qty = $('#qty').val();

  hargaSatuan = hargaSatuan || '0';
  hargaSatuan =  accounting.unformat(hargaSatuan, ",");
  hargaSatuan = parseInt(hargaSatuan);

  qty = qty || '0';
  qty =  accounting.unformat(qty, ",");
  qty = parseInt(qty);

  var nilai = (qty*hargaSatuan);
  nilai = accounting.formatNumber(nilai,{thousand : ".", decimal  : ","});
  $('#nilai').val(nilai);
} 


function prosesPreorderDetail() {
  const dataForm = new FormData();
  dataForm.append('noPo',$('#noPo').val());
  dataForm.append('namaBahan',$('#namaBahan').val());
  dataForm.append('ukuran',$('#ukuran').val());
  dataForm.append('finishing',$('#finishing').val());
  dataForm.append('qty',$('#qty').val());
  dataForm.append('hargaSatuan',$('#hargaSatuan').val());
  dataForm.append('nilai',$('#nilai').val());
  dataForm.append('flagDetail',$('#flagDetail').val());
  dataForm.append('idPoDetail',$('#idPoDetail').val());

  const validasi = formValidation(dataForm);
  if ( validasi=== true){
    $.ajax({
      url:'prosespreorderdetail.php',
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
        notifikasi(dataJSON.status,dataJSON.notifikasi);
        if(dataJSON.status==true){
          getBarangTersimpan();
          formItemPreorder();
       }
        
      }
    });
  }
}

function editBarang(id) {
    $.ajax({
      url:'dataformitempreorder.php',
      type:'post',
      data:{
        idPoDetail  : id,
        flagDetail  : 'update'
      },
      beforeSend:function(){
      },
      success:function(data,status){
        $("#dataFormItemPreorder").html(data);
        $('select.select2').select2();
      }
    });
}

function cancelBarang(idPoDetail){
   swal({
    title: "Apakah anda yakin ingin membatalkan item ini?",
    text: "Setelah dibatalkan, proses tidak dapat diulangi!",
    icon: "warning",
    buttons: true,
    dangerMode: true,
  })
  .then((willDelete) => {
    if(willDelete){
      $.ajax({
        url:'prosespreorderdetail.php',
        type:'post',
        data:{
          idPoDetail : idPoDetail,
          flagDetail : 'cancel'
        },
        success:function(data,status){
        //console.log(data);  
          let dataJSON = JSON.parse(data);
          notifikasi(dataJSON.status,dataJSON.notifikasi);
          getBarangTersimpan();
        }
      });
    } 
    else{
      swal({
        title: "Proses pembatalan item dibatalkan!",
        icon: "warning"
      });
    }
  });
}


function getBarangTersimpan(){
  //console.log(noPo);
  $.ajax({
    url:'datadaftarbarangtersimpan.php',
    type:'post',
    data:{
      noPo : $('#noPo').val(),
      rentang : $('#rentang').val(),
      flag : $('#flag').val()
    },
    success:function(data,status){
      //console.log(data);
      $('#dataDaftarBarangTersimpan').empty().append(data);
      $('select.select2').select2();
    }
  });
}

function prosesPreorder() {
  var noPo = $('#noPo').val();

  let formPo = document.getElementById('formPo');
  let dataForm  = new FormData(formPo);

  const validasi = formValidation(dataForm);
  if ( validasi=== true){
    $.ajax({
      url:'prosespreorder.php',
      type:'post',
      data:{
        noPo : noPo,
        idPo : $('#idPo').val(),
        idCabang : $('#idCabang').val(),
        tanggalPo : $('#tanggalPo').val(),
        idCabangAdvertising : $('#idCabangAdvertising').val(),
        keterangan : $('#keterangan').val(),
        customer : $('#customer').val(),
        idCustomer : $('#idCustomer').val(),
        namaCustomer : $('#namaCustomer').val(),
        noTelpCustomer : $('#noTelpCustomer').val(),
        tanggalSelesai : $('#tanggalSelesai').val(),
        konsumen : $('#konsumen').val(),
        flag : $('#flag').val()
      },
      success:function(data,status){ 
      //console.log(data);       
        let dataJSON = JSON.parse(data);
        notifikasi(dataJSON.status,dataJSON.notifikasi);
         formPreorder(dataJSON.konsumen,dataJSON.flag);  
      }
    });
  }
  else {
     notifikasi(2, "Proses Gagal, Form Belum Terisi Dengan Lengkap");
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


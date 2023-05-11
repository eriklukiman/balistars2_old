function dataDaftarPo(parameterOrder){
  $.ajax({
    url:'datadaftarpo.php',
    type:'post',
    data:{
      parameterOrder : parameterOrder,
      rentang : $('#rentang').val()
    },
    beforeSend:function(){
      $('.overlay').show();
    },
    success:function(data,status){
      $('#dataDaftarPo').empty().append(data);
      $('#parameterOrder').val(parameterOrder);
      $('.overlay').hide();
    }
  });
}

function tambahNoNota(noPo) {
  $("#modalFormTambahNoNota").modal('show');

  $.ajax({
    url:'dataformtambahnonota.php',
    type:'post',
    data:{
      noPo  : noPo,
    },
    beforeSend:function(){
    },
    success:function(data,status){
      $("#dataFormTambahNoNota").html(data);
      $("#modalFormTambahNoNota").modal('hide');
    }
  });
}

function editPo(noPo) {
    $.ajax({
      url:'form_po_umum/formpoumum.php',
      type:'post',
      data:{
        noPo       : noPo,
        flag       : 'update'
      },
      beforeSend:function(){
      },
      success:function(data,status){
        //let dataJSON = JSON.parse(data);
      dataDaftarPo();
     // notifikasi(dataJSON.notifikasi);
      }
    });
}


function prosesTambahNoNota(){

  $.ajax({
    url:'prosestambahnonota.php',
    type:'post',
    data:{
      noPo   : $('#noPo').val(),
      noNota : $('#noNota').val()
    },

    beforeSend:function(){
    },

    success:function(data,status){
       //console.log(data);
      let dataJSON = JSON.parse(data);
      dataDaftarPo();
      notifikasi(dataJSON.notifikasi);
      $("#modalFormTambahNoNota").modal('hide');
      
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
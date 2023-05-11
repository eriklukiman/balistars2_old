function validasiAngka(id) {
  let value    = $(id).val();
  //Regular Expression di bawah berarti : setiap inputan harus dimulai dengan angka
  let rgx      = /^[0-9]*$/;
  let newValue = value.match(rgx);

  $(id).val(newValue);
}
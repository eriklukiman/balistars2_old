<?php  
#tools
include_once('fungsineracalajurrincian/fungsikasbesarrincian.php');
include_once('fungsineracalajurrincian/fungsikaskecilrincian.php');
include_once('fungsineracalajurrincian/fungsikasbankrincian.php');
include_once('fungsineracalajurrincian/fungsikasadvertisingrincian.php');
include_once('fungsineracalajurrincian/fungsikaspersglobalrincian.php');
include_once('fungsineracalajurrincian/fungsikashutanglancarrincian.php');
include_once('fungsineracalajurrincian/fungsikasinputrincian.php');
include_once('fungsineracalajurrincian/fungsikasmodalawalrincian.php');
include_once('fungsineracalajurrincian/fungsikaspriverincian.php');
include_once('fungsineracalajurrincian/fungsikaspenjualanpjrincian.php');
include_once('fungsineracalajurrincian/fungsikaspenjualanjlrincian.php');
include_once('fungsineracalajurrincian/fungsikashpprincian.php');
include_once('fungsineracalajurrincian/fungsikaspphrincian.php');
include_once('fungsineracalajurrincian/fungsikasppnrincian.php');
include_once('fungsineracalajurrincian/fungsikaspendapatanlainrincian.php');
include_once('fungsineracalajurrincian/fungsikaspiutangA1rincian.php');
include_once('fungsineracalajurrincian/fungsikaspiutangA2rincian.php');
include_once('fungsineracalajurrincian/fungsikaspemutihanpiutangrincian.php');
include_once('fungsineracalajurrincian/fungsikashutangA1rincian.php');
include_once('fungsineracalajurrincian/fungsikashutangA2rincian.php');
include_once('fungsineracalajurrincian/fungsikasbiayarincian.php');
include_once('fungsineracalajurrincian/fungsikasbiayaadminrincian.php');
include_once('fungsineracalajurrincian/fungsikasbiayasewarincian.php');
include_once('fungsineracalajurrincian/fungsikaspendapatanrincian.php');
include_once('fungsineracalajurrincian/fungsikassewabayardimukarincian.php');
include_once('fungsineracalajurrincian/fungsikasmesinrincian.php');
include_once('fungsineracalajurrincian/fungsikaskendaraaninventarisrincian.php');
include_once('fungsineracalajurrincian/fungsikaspenyusutanmesinkendaraanrincian.php');
include_once('fungsineracalajurrincian/fungsikasbiayapenyusutanmesinkendaraanrincian.php');


function selectRincian ()
{
  ?>
  <div class="form-group">
      <label>Kode ACC </label>
      <select name="kodeACC" id="kodeACCSearch" class="form-control select2" onchange="dataDaftarNeracaLajurRincian();">
        <option value="0">Pilih Kode ACC</option>
        <?php
         $kodeACC     = array(
                            'KB-1',//Kas Besar
                            'KB-2',
                            'KB-3',
                            'KB-4',
                            'KB-5',
                            'KB-6',
                            'KB-7',
                            'KB-8',
                            'KB-10',
                            'KB-11',
                            'KB-12',
                            'KB-13',
                          
                            'KK-1',//Kas Kecil
                            'KK-2',
                            'KK-3',
                            'KK-4',
                            'KK-5',
                            'KK-6',
                            'KK-7',
                            'KK-8',
                            'KK-9',
                            'KK-10',
                            'KK-11',
                            'KK-12',
                            'KK-13',

                            'B-31',//Bank
                            'B-29',
                            'B-28',
                            'B-27',
                            'B-15',
                            'B-19',
                            'B-8',
                            'B-9',
                            'B-10',
                            'B-11',
                            'B-12',
                            'B-13',
                            'B-20',
                            'B-21',
                            'B-26',
                            'B-14',
                            'B-32',
                            'B-38',
                            'B-39',
                            'B-41',

                            '1119,2',

                            'G-1',//Pers Global
                            'G-2',
                            'G-3',
                            'G-4',
                            'G-5',
                            'G-6',
                            'G-7',
                            'G-8',
                            'G-10',
                            'G-11',
                            'G-12',
                            'G-13',
                            '1161',

                            '1131',
                            '1132',
                            '1131.1-A1',
                            '1132.1-A2',
                            '2111',
                            '2112',
                            '2135',
                            '2142',
                            '2145',

                            '1313',
                            '1314',
                            '1316',

                            'PY-1313',
                            'PY-1314',
                            'PY-1316',

                            'Cadangan Pajak',
                            'Modal Awal',
                            '3140',

                            'PJ-1',//Penjualan A1
                            'PJ-2',
                            'PJ-3',
                            'PJ-4',
                            'PJ-5',
                            'PJ-6',
                            'PJ-7',
                            'PJ-8',
                            'PJ-10',
                            'PJ-11',
                            'PJ-12',
                            'PJ-13',

                            'JL-1',//Penjualan A2
                            'JL-2',
                            'JL-3',
                            'JL-4',
                            'JL-5',
                            'JL-6',
                            'JL-7',
                            'JL-8',
                            'JL-10',
                            'JL-11',
                            'JL-12',
                            'JL-13',

                            'HPP-1',//HPP
                            'HPP-2',
                            'HPP-3',
                            'HPP-4',
                            'HPP-5',
                            'HPP-6',
                            'HPP-7',
                            'HPP-8',
                            'HPP-10',
                            'HPP-11',
                            'HPP-12',
                            'HPP-13',

                            '2112',//Biaya
                            '6101',
                            '6110',
                            '6130',
                            '6140',
                            '6141',
                            '6142',
                            '6143',
                            '6150',
                            '6151',
                            '6170',
                            '6180',
                            '6181',
                            '6211',
                            '6212',
                            '6215',
                            '6216',
                            '6262',
                            '6263',
                            '6264',
                            '6270',
                            '6280',
                            '6290',
                            '6310',
                            '6324',
                            '6326',
                            '6340',
                            '6345',
                            '6346',
                            '6350',
                            '6351',
                            '6355',
                            '6360',
                            '6370',
                            '6390',
                            '6391',
                            '6395',
                            '6396',
                            '6397',
                            'BY-1313',
                            'BY-1314',
                            'BY-1316',
                            'PL-2',
                            'PL-4',
                            '7120');
        $keterangan = array(
                            'Kas Besar Batubulan (1111,1)',
                            'Kas Besar Gatsu (1112,1)',
                            'Kas Besar Gunung Agung (1113,1)',
                            'Kas Besar Tabanan (1114,1)',
                            'Kas Besar Gianyar (1115,1)',
                            'Kas Besar Nusa Dua (1116,1)',
                            'Kas Besar Ubud (1117,1)',
                            'Kas Besar Sesetan (1118,1)',
                            'Kas Besar Singaraja (11110,1)',
                            'Kas Besar Imam Bonjol (11111,1)',
                            'Kas Besar Mataram 1 (11112,1)',
                            'Kas Besar Mataram 2 (11113,1)',

                            'Kas Kecil Batubulan (1111,2)',
                            'Kas Kecil Gatsu (1112,2)',
                            'Kas Kecil Gunung Agung (1113,2)',
                            'Kas Kecil Tabanan (1114,2)',
                            'Kas Kecil Gianyar (1115,2)',
                            'Kas Kecil Nusa Dua (1116,2)',
                            'Kas Kecil Ubud (1117,2)',
                            'Kas Kecil Sesetan (1118,2)',
                            'Kas Kecil Head Office (1119,2)',
                            'Kas Kecil Singaraja (11110,2)',
                            'Kas Kecil Imam Bonjol (11111,2)',
                            'Kas Kecil Mataram 1 (11112,2)',
                            'Kas Kecil Mataram 2 (11113,2)',

                            'Kas Pusat (11231)',
                            'BCA Giro (11229)',
                            'BCA Induk (11228)',
                            'Kas Pak Sui (11227)',
                            'BCA CV Balistars (11215)',
                            'Mandiri CV Balistars (11219)',
                            'BCA(Petty Cash Pusat) (1128)',
                            'BCA(Petty Cash Advertising) (1129)',
                            'BCA(Petty Cash Kas Bon) (11210)',
                            'BCA(Induk 1) (11211)',
                            'BCA(Induk 2) (11212)',
                            'BCA(Giro) (11213)',
                            'Mandiri(Plafon+Save P Tjandra) (11220)',
                            'HSBC (11221)',
                            'BCA Saving PJK (11226)',
                            'BCA(Plafon/Pinjaman Bank (11214)',
                            'BCA (Induk BSG dan ADV) (11232)',
                            'Bank Induk 3 (11238)',
                            'Bank Petty Cash Pusat Mataram (11239)',
                            'Titipan PPH PS 23 Supplier (11241)',

                            'Kas Advertising (1119,2)',

                            'Pers Global Batubulan (1149,1)',
                            'Pers Global Gatsu (1149,2)',
                            'Pers Global Gunung Agung (1149,3)',
                            'Pers Global Tabanan (1149,4)',
                            'Pers Global Gianyar (1149,5)',
                            'Pers Global Nusa Dua (1149,6)',
                            'Pers Global Ubud (1149,7)',
                            'Pers Global Sesetan (1149,8)',
                            'Pers Global Singaraja (1149,10)',
                            'Pers Global Imam Bonjol (1149,11)',
                            'Pers Global Mataram 1 (1149,12)',
                            'Pers Global Mataram 2 (1149,13)',
                            'Sewa Dibayar Muka (1161)',

                            'Piutang A1 (1131)',
                            'Piutang A2 (1132)',
                            'Pemutihan Piutang A1 (1131.1)',
                            'Pemutihan Piutang A2 (1132.1)',
                            'Hutang A1 (2111)',
                            'Hutang A2 (2112)',
                            'Hutang Lancar Lain-lain (2135)',
                            'PPH (2142)',
                            'PPN (2145)',

                            'Mesin dan Perlengkapan (1313)', 
                            'Kendaraan (1314)',
                            'Inventaris dan Perlengkapan (1316)',

                            'Ak. Pen. Mesin dan Perlengkapan (1313)', 
                            'Ak. Pen. Kendaraan (1314)',
                            'Ak. Pen. Inventaris dan Perlengkapan (1316)',

                            'Cadangan Pajak (3115)',
                            'Modal Awal (3110)',
                            'Prive (3140)',

                            'Penjualan Batubulan A1 (4210)',
                            'Penjualan Gatsu A1 (4220)',
                            'Penjualan Gn Agung A1 (4230)',
                            'Penjualan Tabanan A1 (4240)',
                            'Penjualan Gianyar A1 (4250)',
                            'Penjualan Nusa Dua A1 (4260)',
                            'Penjualan Ubud A1 (4270)',
                            'Penjualan Sesetan A1 (4280)', 
                            'Penjualan Singaraja A1 (42100)',
                            'Penjualan Imambonjol A1 (42110)',
                            'Penjualan Mataram 1 A1 (42120)',
                            'Penjualan mataram 2 A1 (42130)',

                            'Penjualan Batubulan A2 (4210)',
                            'Penjualan Gatsu A2 (4220)',
                            'Penjualan Gn Agung A2 (4230)',
                            'Penjualan Tabanan A2 (4240)',
                            'Penjualan Gianyar A2 (4250)',
                            'Penjualan Nusa Dua A2 (4260)',
                            'Penjualan Ubud A2 (4270)',
                            'Penjualan Sesetan A2 (4280)', 
                            'Penjualan Singaraja A2 (42100)',
                            'Penjualan Imambonjol A2 (42110)',
                            'Penjualan Mataram 1 A2 (42120)',
                            'Penjualan mataram 2 A2 (42130)',

                            'HPP Batubulan (5111)',
                            'HPP Gatsu (5112)',
                            'HPP Gunung Agung (5113)',
                            'HPP Tabanan (5114)',
                            'HPP Gianyar (5115)',
                            'HPP Nusa Dua (5116)',
                            'HPP Ubud (5117)',
                            'HPP Sesetan (5118)',
                            'HPP Singaraja (51110)',
                            'HPP Imam Bonjol (51111)',
                            'HPP Mataram 1 (51112)',
                            'HPP Mataram 2 (51113)',

                            'Pembelian Bahan (2112)',
                            'Biaya Parkir (6101)',
                            'Biaya Sampel/Promosi (6110)',
                            'Biaya Fee dan Komisi (6130)',
                            'Biaya Perjalanan Dinas (6140)',
                            'Biaya Advertising (6141)',
                            'Biaya BS Grafika (6142)',
                            'Biaya DTF (6143)',
                            'Biaya transport (6150)',
                            'Biaya Pengiriman (6151)',
                            'Biaya Perbaikan Bangunan Kantor (6170)',
                            'Biaya Pemeliharaan Inventaris Kantor (6180)',
                            'Biaya Pemeliharaan Mesin dan Perlengkapan (6181)',
                            'Biaya Gaji (6211)',
                            'Biaya Lembur (6212)',
                            'Biaya THR (6215)',
                            'Biaya BPJS Ketenagakerjaan (6216)',
                            'Biaya Fotocopy (6262)',
                            'Biaya Materai  (6263)',
                            'Biaya Suplies Kantor (6264)',
                            'Biaya Telpon, Indihome dan Pulsa (6270)',
                            'Biaya Listrik (6280)',
                            'Biaya Air PDAM (6290)',
                            'Biaya Iuran Keamanan DLL (6310)',
                            'Biaya Pajak PPH 25/28 (6324)',
                            'Biaya Pajak PPH PS4  (6326)',
                            'Biaya Sewa (6340)',
                            'Biaya Program Komputer(6345)',
                            'Biaya Maintenance Fee(6346)',
                            'Biaya Asuransi (6350)',
                            'Biaya Pulsa XL (6351)',
                            'Biaya Pengurusan Izin dan Surat (6355)',
                            'Biaya ADM Bank  (6360)',
                            'Biaya Pajak Bunga Bank (6370)',
                            'Biaya Bunga Pinjaman Bank (6390)',
                            'Biaya Penghapusan Piutang (6391)',
                            'Biaya Konsumsi (6395)',
                            'Biaya Banten (6396)',
                            'Biaya Lain-lain (6397)',
                            'Biaya Pen. Mesin dan Perlengkapan (5556)', 
                            'Biaya Pen. Kendaraan (6160)',
                            'Biaya Pen. Inventaris dan Perlengkapan (6335)',
                            'Pendapatan Bunga Bank (7110)',
                            'Pendapatan Umum (7115)',
                            'Pendapatan Lainnya (7120)');
        for ($i=0; $i < count($kodeACC) ; $i++) { 
          ?>
          <option value="<?=$kodeACC[$i]?>"> <?=$keterangan[$i]?> </option>
          <?php
        }
        ?>
      </select>
    </div>
  <?php
}

#Important!
function mapping($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total)
{
  switch ($kodeACC) {
    case "0":
      break;
    case "KB-1":
      return kasBesar($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "KB-2":
      return kasBesar($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "KB-3":
      return kasBesar($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "KB-4":
      return kasBesar($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "KB-5":
      return kasBesar($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "KB-6":
      return kasBesar($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "KB-7":
      return kasBesar($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "KB-8":
      return kasBesar($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "KB-10":
      return kasBesar($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "KB-11":
      return kasBesar($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "KB-12":
      return kasBesar($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "KB-13":
      return kasBesar($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;

    case "KK-1":
      return kasKecil($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "KK-2":
      return kasKecil($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "KK-3":
      return kasKecil($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "KK-4":
      return kasKecil($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "KK-5":
      return kasKecil($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "KK-6":
      return kasKecil($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "KK-7":
      return kasKecil($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "KK-8":
      return kasKecil($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "KK-9":
      return kasKecil($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "KK-10":
      return kasKecil($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "KK-11":
      return kasKecil($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "KK-12":
      return kasKecil($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "KK-13":
      return kasKecil($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;

    case "B-31":
      return kasBank($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "B-29":
      return kasBank($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
     case "B-28":
      return kasBank($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
     case "B-27":
      return kasBank($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "B-15":
      return kasBank($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
     case "B-19":
      return kasBank($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
     case "B-8":
      return kasBank($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
     case "B-9":
      return kasBank($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
     case "B-10":
      return kasBank($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
     case "B-11":
      return kasBank($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
     case "B-12":
      return kasBank($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
     case "B-13":
      return kasBank($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
     case "B-20":
      return kasBank($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
     case "B-21":
      return kasBank($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
     case "B-26":
      return kasBank($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
     case "B-14":
      return kasBank($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
     case "B-32":
      return kasBank($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
     case "B-38":
      return kasBank($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
     case "B-39":
      return kasBank($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
     case "B-41":
      return kasBank($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;

    case "1119,2":
      return kasAdvertising($db,$tanggalAwal,$tanggalAkhir,$total);
      break;

     case "G-1":
      return persGlobal($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "G-2":
      return persGlobal($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "G-3":
      return persGlobal($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "G-4":
      return persGlobal($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "G-5":
      return persGlobal($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "G-6":
      return persGlobal($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "G-7":
      return persGlobal($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "G-8":
      return persGlobal($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "G-10":
      return persGlobal($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "G-11":
      return persGlobal($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "G-12":
      return persGlobal($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "G-13":
      return persGlobal($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;

     case "1161":
      return kasBayarDiMuka($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;

    case "1131":
      return kasPiutangA1($db,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "1132":
      return kasPiutangA2($db,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "1131.1-A1":
      return kasPemutihanPiutang($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "1132.1-A2":
      return kasPemutihanPiutang($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break; 
    case "2111":
      return kasHutangA1($db,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "2112":
      return kasHutangA2($db,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "2135":
      return hutangLancar($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "2142":
      return kasPPH($db,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "2145":
      return kasPPN($db,$tanggalAwal,$tanggalAkhir,$total);
      break;


    case "1313":
      return kasMesin($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "1314":
      return kasKendaraan($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "1316":
      return kasKendaraan($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;

     case "PY-1313":
      return kasPenyMesin($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "PY-1314":
      return kasPenyMesin($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "PY-1316":
      return kasPenyMesin($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;

    case "Cadangan Pajak":
      return kasInput($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "Modal Awal":
      return kasModalAwal($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "3140":
      return kasPrive($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;

    case "PJ-1":
      return kasPenjualanPJ($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "PJ-2":
      return kasPenjualanPJ($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "PJ-3":
      return kasPenjualanPJ($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "PJ-4":
      return kasPenjualanPJ($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "PJ-5":
      return kasPenjualanPJ($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "PJ-6":
      return kasPenjualanPJ($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "PJ-7":
      return kasPenjualanPJ($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "PJ-8":
      return kasPenjualanPJ($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "PJ-10":
      return kasPenjualanPJ($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "PJ-11":
      return kasPenjualanPJ($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "PJ-12":
      return kasPenjualanPJ($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "PJ-13":
      return kasPenjualanPJ($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;

  case "JL-1":
      return kasPenjualanJL($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "JL-2":
      return kasPenjualanJL($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "JL-3":
      return kasPenjualanJL($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "JL-4":
      return kasPenjualanJL($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "JL-5":
      return kasPenjualanJL($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "JL-6":
      return kasPenjualanJL($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "JL-7":
      return kasPenjualanJL($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "JL-8":
      return kasPenjualanJL($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "JL-10":
      return kasPenjualanJL($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "JL-11":
      return kasPenjualanJL($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "JL-12":
      return kasPenjualanJL($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "JL-13":
      return kasPenjualanJL($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;

    case "HPP-1":
      return kasHPP($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "HPP-2":
      return kasHPP($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "HPP-3":
      return kasHPP($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "HPP-4":
      return kasHPP($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "HPP-5":
      return kasHPP($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "HPP-6":
      return kasHPP($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "HPP-7":
      return kasHPP($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "HPP-8":
      return kasHPP($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "HPP-10":
      return kasHPP($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "HPP-11":
      return kasHPP($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "HPP-12":
      return kasHPP($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "HPP-13":
      return kasHPP($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;

    case "2112":
      return kasBiaya($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "6101":
      return kasBiaya($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "6110":
      return kasBiaya($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "6130":
      return kasBiaya($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "6140":
      return kasBiaya($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "6141":
      return kasBiaya($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "6142":
      return kasBiaya($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "6143":
      return kasBiaya($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "6150":
      return kasBiaya($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "6151":
      return kasBiaya($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "6170":
      return kasBiaya($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "6180":
      return kasBiaya($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "6181":
      return kasBiaya($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "6211":
      return kasBiaya($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "6212":
      return kasBiaya($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "6215":
      return kasBiaya($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "6216":
      return kasBiaya($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "6262":
      return kasBiaya($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "6263":
      return kasBiaya($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "6264":
      return kasBiaya($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "6270":
      return kasBiaya($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "6280":
      return kasBiaya($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "6290":
      return kasBiaya($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "6310":
      return kasBiaya($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "6324":
      return kasBiaya($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "6326":
      return kasBiaya($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "6340":
      return kasBiayaSewa($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "6345":
      return kasBiaya($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "6346":
      return kasBiaya($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "6350":
      return kasBiaya($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "6351":
      return kasBiaya($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "6355":
      return kasBiaya($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "6360":
      return kasBiayaAdmin($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "6370":
      return kasBiaya($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "6390":
      return kasBiaya($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "6391":
      return kasBiaya($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "6395":
      return kasBiaya($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "6396":
      return kasBiaya($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "6397":
      return kasBiaya($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "BY-1313":
      return kasBiayaMesin($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "BY-1314":
      return kasBiayaMesin($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "BY-1316":
      return kasBiayaMesin($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "PL-2":
      return kasPendapatan($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
    case "PL-4":
      return kasPendapatan($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total);
      break;
     case "7120":
      return kasPendapatanLain($db,$tanggalAwal,$tanggalAkhir,$total);
      break;
    default:
      echo "Please Check Kode ACC!";
  }
}

function saldoAwal($dataAwal,$total)
{

  $total['saldo'] += $dataAwal['saldo'];
  ?>
  <tr>
    <td colspan="4"><b> Saldo Awal </b></td>
    <td style="text-align: right;"><b><?=ubahToRp($dataAwal['saldo'])?></b></td>
  </tr>
  <?php
  return $total;
}

function tampilTable($keterangan,$tanggal,$debet,$kredit,$saldo,$total)
{
  //if($saldo!=0){return saldoAwal($saldo,$total);}
  $keterangan      = strtoupper($keterangan);
  $saldo         = $debet-$kredit;
  $total['debet']     += $debet;
  $total['kredit']    += $kredit;
  $total['saldo']   += $saldo;
  ?>
  <tr>
    <td><?=ubahTanggalIndo($tanggal)?></td>
    <td><?=$keterangan?></td>
    <td style="text-align: right;"><?=ubahToRp($debet)?></td>
    <td style="text-align: right;"><?=ubahToRp($kredit)?></td>
    <td style="text-align: right;"><?=ubahToRp($total['saldo'])?></td>
  </tr>
  <?php
  return $total;
}

function sqlSwitch($sqlProperty,$parameter)
{
  $sqlSum      = $sqlProperty; 
  $sqlProperty = str_replace($parameter,'0 as saldo',$sqlProperty);
  $sqlList     = $sqlProperty; 
  if($sqlSum==$sqlList){return $sqlSum;}
  return "(".$sqlSum.") UNION ALL (".$sqlList.")";
}

#debuger

function console_log( $data ){
  echo '<script>';
  echo 'console.log('. json_encode( $data ) .')';
  echo '</script>';
}

function sqlDebug($sqlProperty)
{
  #default
  $date = "'2020-01-01' and '2020-12-31'";
  $sqlProperty = str_replace('(','<br>(',$sqlProperty);
  $sqlProperty = str_replace(')',')<br>',$sqlProperty);
  $sqlProperty = str_replace('M<br>(','M(',$sqlProperty);
  $sqlProperty = str_replace(')<br> as',') as',$sqlProperty);
  $sqlProperty = str_replace(')<br>)','))',$sqlProperty);
  $sqlProperty = str_replace('? and ?',$date,$sqlProperty);
  echo $sqlProperty;
  console_log($sqlProperty);
}



?>
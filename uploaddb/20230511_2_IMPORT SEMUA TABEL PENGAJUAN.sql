-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 11, 2023 at 07:52 AM
-- Server version: 10.4.22-MariaDB
-- PHP Version: 7.3.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `balistarsupload`
--

-- --------------------------------------------------------

--
-- Table structure for table `balistars_data_surat_pengajuan`
--

CREATE TABLE `balistars_data_surat_pengajuan` (
  `idDataSuratPengajuan` bigint(20) UNSIGNED NOT NULL,
  `idPengajuan` bigint(20) NOT NULL,
  `data` varchar(255) NOT NULL,
  `kolom` varchar(50) NOT NULL,
  `row` int(11) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT '''Aktif''',
  `tahapan` varchar(50) NOT NULL,
  `idUser` bigint(20) UNSIGNED NOT NULL,
  `timeStamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `idUserEdit` bigint(20) DEFAULT NULL,
  `timeStampEdit` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `balistars_payment`
--

CREATE TABLE `balistars_payment` (
  `idPayment` bigint(20) UNSIGNED NOT NULL,
  `idPengajuan` bigint(20) UNSIGNED NOT NULL,
  `tanggal` date NOT NULL,
  `keterangan` text NOT NULL,
  `lamaWaktu` time NOT NULL,
  `menit` int(11) NOT NULL,
  `jenisPengajuan` varchar(50) NOT NULL,
  `statusPayment` varchar(20) NOT NULL DEFAULT 'Aktif',
  `idUser` bigint(20) UNSIGNED NOT NULL,
  `timeStamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `idUserEdit` bigint(20) UNSIGNED DEFAULT NULL,
  `timeStampEdit` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `balistars_pengajuan_additional`
--

CREATE TABLE `balistars_pengajuan_additional` (
  `idAdditional` bigint(20) UNSIGNED NOT NULL,
  `namaCustomer` varchar(255) NOT NULL,
  `tglPengajuan` date NOT NULL,
  `biaya` decimal(15,0) NOT NULL,
  `omset` decimal(15,0) NOT NULL,
  `profit` decimal(15,0) NOT NULL,
  `ratio` decimal(15,2) NOT NULL,
  `linkPO` text NOT NULL,
  `linkSuratPenjamin` text NOT NULL,
  `linkDP` text NOT NULL,
  `linkBuktiOrder` text NOT NULL,
  `linkDesainCetakan` text NOT NULL,
  `linkNotaSupplier` text NOT NULL,
  `linkFoto` text NOT NULL,
  `linkAbsensi` text NOT NULL,
  `linkBuktiTransfer` text NOT NULL,
  `linkLainnya` text NOT NULL,
  `attempt` int(2) NOT NULL DEFAULT 1,
  `tahapan` varchar(50) NOT NULL,
  `idCabang` bigint(20) UNSIGNED NOT NULL,
  `statusAdditional` varchar(20) NOT NULL DEFAULT 'Aktif',
  `idUser` bigint(20) UNSIGNED NOT NULL,
  `timeStamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `idUserEdit` bigint(20) UNSIGNED DEFAULT NULL,
  `timeStampEdit` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `balistars_pengajuan_partisi`
--

CREATE TABLE `balistars_pengajuan_partisi` (
  `idPartisi` bigint(20) UNSIGNED NOT NULL,
  `namaCustomer` varchar(255) NOT NULL,
  `tglPengajuan` date NOT NULL,
  `biaya` decimal(15,0) NOT NULL,
  `lamaPartisi` varchar(255) NOT NULL,
  `keteranganPembelian` text NOT NULL,
  `linkSuratPartisi` text NOT NULL,
  `linkBuktiOrder` text NOT NULL,
  `linkPenawaran` text NOT NULL,
  `linkFoto` text NOT NULL,
  `linkPerbandingan` text NOT NULL,
  `linkLainnya` text NOT NULL,
  `attempt` int(2) NOT NULL DEFAULT 1,
  `tahapan` varchar(50) NOT NULL,
  `idCabang` bigint(20) UNSIGNED NOT NULL,
  `statusPartisi` varchar(20) NOT NULL DEFAULT 'Aktif',
  `idUser` bigint(20) UNSIGNED NOT NULL,
  `timeStamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `idUserEdit` bigint(20) UNSIGNED DEFAULT NULL,
  `timeStampEdit` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `balistars_pengajuan_pengembalian`
--

CREATE TABLE `balistars_pengajuan_pengembalian` (
  `idPengembalian` bigint(20) UNSIGNED NOT NULL,
  `namaCustomer` varchar(255) NOT NULL,
  `tglPengajuan` date NOT NULL,
  `jumlahTransaksi` decimal(15,0) NOT NULL,
  `totalPengembalian` decimal(15,0) NOT NULL,
  `linkSuratPengajuan` text NOT NULL,
  `linkSuratPernyataanCustomer` text NOT NULL,
  `linkBuktiTransfer` text NOT NULL,
  `linkBuktiPotongPPH` text NOT NULL,
  `linkBuktiPotongPPN` text NOT NULL,
  `linkRincianPenjualanExcel` text NOT NULL,
  `linkBuktiChatCustomer` text NOT NULL,
  `linkNotaPenjualan` text DEFAULT NULL,
  `attempt` int(2) NOT NULL DEFAULT 1,
  `tahapan` varchar(50) NOT NULL,
  `idCabang` bigint(20) UNSIGNED NOT NULL,
  `statusPengembalian` varchar(20) NOT NULL DEFAULT 'Aktif',
  `idUser` bigint(20) UNSIGNED NOT NULL,
  `timeStamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `idUserEdit` bigint(20) UNSIGNED DEFAULT NULL,
  `timeStampEdit` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `balistars_pengajuan_petty_cash`
--

CREATE TABLE `balistars_pengajuan_petty_cash` (
  `idPettyCash` bigint(20) UNSIGNED NOT NULL,
  `namaProyek` varchar(255) NOT NULL,
  `namaPerusahaan` varchar(255) NOT NULL,
  `tglPengajuan` date NOT NULL,
  `estimasiOmset` decimal(15,0) NOT NULL,
  `estimasiBiayaPengeluaran` varchar(255) NOT NULL,
  `nominal` decimal(15,0) NOT NULL,
  `biayaEksternal` decimal(15,0) NOT NULL,
  `noPO` varchar(255) NOT NULL,
  `keterangan` text NOT NULL,
  `attempt` int(2) NOT NULL DEFAULT 1,
  `tahapan` varchar(50) NOT NULL,
  `idCabang` bigint(20) UNSIGNED NOT NULL,
  `statusPettyCash` varchar(20) NOT NULL DEFAULT 'Aktif',
  `idUser` bigint(20) UNSIGNED NOT NULL,
  `timeStamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `idUserEdit` bigint(20) UNSIGNED DEFAULT NULL,
  `timeStampEdit` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `balistars_penyetujuan`
--

CREATE TABLE `balistars_penyetujuan` (
  `idPenyetujuan` bigint(20) UNSIGNED NOT NULL,
  `idPengajuan` bigint(20) UNSIGNED NOT NULL,
  `tahapan` varchar(50) NOT NULL,
  `jenisPengajuan` varchar(50) NOT NULL,
  `hasil` varchar(50) NOT NULL,
  `lamaWaktu` time NOT NULL,
  `menit` int(11) NOT NULL,
  `attempt` int(3) NOT NULL,
  `keterangan` text NOT NULL,
  `idUserPenyetuju` bigint(20) NOT NULL,
  `statusPenyetujuan` varchar(20) NOT NULL DEFAULT 'Aktif',
  `idUser` bigint(20) UNSIGNED NOT NULL,
  `timeStamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `idUserEdit` bigint(20) UNSIGNED DEFAULT NULL,
  `timeStampEdit` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `balistars_data_surat_pengajuan`
--
ALTER TABLE `balistars_data_surat_pengajuan`
  ADD PRIMARY KEY (`idDataSuratPengajuan`),
  ADD UNIQUE KEY `idPengajuan` (`idPengajuan`,`kolom`,`row`,`tahapan`);

--
-- Indexes for table `balistars_payment`
--
ALTER TABLE `balistars_payment`
  ADD PRIMARY KEY (`idPayment`);

--
-- Indexes for table `balistars_pengajuan_additional`
--
ALTER TABLE `balistars_pengajuan_additional`
  ADD PRIMARY KEY (`idAdditional`);

--
-- Indexes for table `balistars_pengajuan_partisi`
--
ALTER TABLE `balistars_pengajuan_partisi`
  ADD PRIMARY KEY (`idPartisi`);

--
-- Indexes for table `balistars_pengajuan_pengembalian`
--
ALTER TABLE `balistars_pengajuan_pengembalian`
  ADD PRIMARY KEY (`idPengembalian`);

--
-- Indexes for table `balistars_pengajuan_petty_cash`
--
ALTER TABLE `balistars_pengajuan_petty_cash`
  ADD PRIMARY KEY (`idPettyCash`);

--
-- Indexes for table `balistars_penyetujuan`
--
ALTER TABLE `balistars_penyetujuan`
  ADD PRIMARY KEY (`idPenyetujuan`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `balistars_data_surat_pengajuan`
--
ALTER TABLE `balistars_data_surat_pengajuan`
  MODIFY `idDataSuratPengajuan` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `balistars_payment`
--
ALTER TABLE `balistars_payment`
  MODIFY `idPayment` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `balistars_pengajuan_additional`
--
ALTER TABLE `balistars_pengajuan_additional`
  MODIFY `idAdditional` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `balistars_pengajuan_partisi`
--
ALTER TABLE `balistars_pengajuan_partisi`
  MODIFY `idPartisi` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `balistars_pengajuan_pengembalian`
--
ALTER TABLE `balistars_pengajuan_pengembalian`
  MODIFY `idPengembalian` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `balistars_pengajuan_petty_cash`
--
ALTER TABLE `balistars_pengajuan_petty_cash`
  MODIFY `idPettyCash` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `balistars_penyetujuan`
--
ALTER TABLE `balistars_penyetujuan`
  MODIFY `idPenyetujuan` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

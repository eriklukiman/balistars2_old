UPDATE balistars_penyesuaian SET includeLabaRugiKotor = 'Ya' WHERE jenisPenyesuaian IN ('Pembelian', 'Penjualan');
UPDATE balistars_penyesuaian SET includeLabaRugiKotor = 'Tidak' WHERE jenisPenyesuaian IN ('Biaya', 'Uang Masuk');
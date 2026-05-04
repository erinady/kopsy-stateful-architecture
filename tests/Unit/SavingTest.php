<?php

describe('Transaksi simpanan mendapatkan poin berdasarkan saldo kumulatif', function () {

    it('menghitung poin dari setoran', function () {
        $saldoSimpanan = 100000;
        $setorSimpanan = 100000;
        $totalSaldo = $saldoSimpanan + $setorSimpanan;

        $nominalPoin = 100000; // Misalnya, setiap 100.000 mendapatkan 1 poin
        $pointDiperoleh = $totalSaldo/$nominalPoin;
        
        expect($pointDiperoleh)->toBe(2);
    });

    it('menghitung poin dari penarikan dengan saldo kurang dari 100.000', function () {
        $saldoSimpanan = 200000;
        $tarikSimpanan = 50000;
        $totalSaldo = $saldoSimpanan - $tarikSimpanan;

        $nominalPoin = 100000;
        // gabisa dapat 0,5, 150k tetep 1 poin aja
        $pointDiperoleh = floor($totalSaldo/$nominalPoin);

        $pointDiperoleh = (int) $pointDiperoleh;
        expect($pointDiperoleh)->toBe(1);
    });

    it('menghitung poin dari penarikan dengan nominal sama dengan saldo', function () {
        $saldoSimpanan = 150000;
        $tarikSimpanan = 150000;
        $totalSaldo = $saldoSimpanan - $tarikSimpanan;

        $nominalPoin = 100000;
        $pointDiperoleh = floor($totalSaldo/$nominalPoin);

        $pointDiperoleh = (int) $pointDiperoleh;
        expect($pointDiperoleh)->toBe(0);
    });
});

test('penarikan tidak boleh lebih dari saldo', function () {
    $saldo = 100000;
    $penarikan = 150000;
    
    $valid = $penarikan <= $saldo;
    
    expect($valid)->toBeFalse();
});
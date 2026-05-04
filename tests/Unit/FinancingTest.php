<?php

test('Menghitung simulasi estimasi cicilan per bulan', function () {
    $costPrice = 1000000;
    $marginAmount = $costPrice * 0.08;
    $downPayment = 200000;

    $totalPrice = $costPrice + $marginAmount - $downPayment;
    $tenor = 10;
    $installmentPerMonth = $totalPrice / $tenor;

    // to int
    $installmentPerMonth = (int) $installmentPerMonth;
    expect($installmentPerMonth)->toBe(88000);
});

test('Menghitung Qimah Haliyyah', function () {
    $costPrice = 1000000;
    $marginAmount = $costPrice * 0.08;
    $tenor = 10;    

    $costPricePaid = ($costPrice / $tenor) * 5; // sudah bayar 5 bulan
    $remainingCostPrice = $costPrice - $costPricePaid;
    $marginDiffPerMonth = $marginAmount / $tenor; // margin per bulan
    
    $qimahHaliyyah = $remainingCostPrice + $marginDiffPerMonth;

    $qimahHaliyyah = (int) $qimahHaliyyah;
    expect($qimahHaliyyah)->toBe(508000);
});

test('Menghitung Qimah Ismiyyahh', function () {
    $costPrice = 1000000;
    $marginAmount = $costPrice * 0.08;

    $qimahIsmiyyah = $costPrice + $marginAmount;

    $qimahIsmiyyah = (int) $qimahIsmiyyah;
    expect($qimahIsmiyyah)->toBe(1080000);
});

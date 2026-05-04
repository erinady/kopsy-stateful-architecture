<?php

// FR 1, 2, 6
test('only penanggung jawab anggota can manage all simpanan data and record transactions')->todo();
test('members cannot modify their own or other members simpanan data')->todo();

// FR 3
test('only active registered members can have simpanan products')->todo();

// FR 4, 5, 8
test('members can view their simpanan balance, akad information, and personal ledger')->todo();

// FR 7
test('system generates valid transaction proof for deposits and withdrawals')->todo();

// FR 9, 10, 11
test('simpanan pokok can only be deposited once during new member registration')->todo();
test('simpanan pokok withdrawal is disabled while membership is active')->todo();
test('simpanan pokok is returned only after resignation is approved by ketua koperasi')->todo();

// FR 12, 13
test('system supports routine simpanan wajib deposits based on cooperative periods')->todo();
test('simpanan wajib withdrawal is disabled while membership is active')->todo();

// FR 14
test('simpanan withdrawal cannot exceed available balance')->todo();

// FR 15, 16
test('members must include purpose and select tenor when opening tabungan berjangka')->todo();
test('tabungan berjangka withdrawal is disabled before maturity date')->todo();

// FR 17, 18, 19, 20, 21
test('members must set purpose and target nominal when opening tabungan ibadah')->todo();
test('tabungan ibadah withdrawal is disabled before target nominal is reached')->todo();
test('tabungan ibadah can be withdrawn after target nominal is reached')->todo();
test('system stops accepting deposits for tabungan ibadah that reached the target')->todo();
test('members must withdraw completed tabungan ibadah before opening a new one')->todo();

// FR 22, 23, 24, 25 (Bisa dipindah ke tests/Unit/SimpananPointTest.php nantinya)
test('system calculates simpanan points based on current month total balance')->todo();
test('system applies floor rounding to simpanan points calculation')->todo();
test('system sets zero points if member has no balance or deposits in the current month')->todo();
test('system calculates and displays total accumulated points for member')->todo();

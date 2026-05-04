<?php

// FR 26, 27, 28, 29, 30
test('staf murabahah can record new murabahah application')->todo();
test('staf murabahah can complete applicant identity and heir data')->todo();
test('staf murabahah can complete applicant occupational and financial data')->todo();
test('staf murabahah can complete rahn (collateral) data for application')->todo();
test('staf murabahah can save murabahah application form as draft')->todo();

// FR 31
test('ketua murabahah or ketua koperasi can approve or reject application with reasons')->todo();

// FR 32, 33, 34
test('system handles wakalah akad application by member as muwakkil')->todo();
test('system provides wakalah akad document template download')->todo();
test('staf murabahah can upload signed wakalah akad document')->todo();

// FR 35, 38
test('staf murabahah can add supplier info and procurement proof after approval')->todo();
test('staf murabahah can add murabahah details including tenor and signed document for finalization')->todo();

// FR 36 (Bisa dipindah ke tests/Unit/MurabahahSimulationTest.php)
test('system displays monthly installment simulation for staf murabahah before finalization')->todo();

// FR 37
test('system provides murabahah akad document template download before finalization')->todo();

// FR 39, 40, 41
test('staf murabahah can process early payoff application')->todo();
test('system calculates tsaman naqdy, qimah ismiyyah, and qimah haliyyah for early payoff')->todo();
test('system generates early payoff proof receipt for member and staff')->todo();

// FR 42, 43, 44, 45
test('staf murabahah can record murabahah installment payments')->todo();
test('system calculates remaining balance and installment amount based on tsaman al-murabahah divided by tenor')->todo();
test('system generates installment payment proof receipt')->todo();
test('system calculates member points based on payment punctuality and nominal')->todo();

// FR 46
test('system sends email notification H-1 and on installment due date')->todo();

// FR 47, 48, 49, 50
test('ketua and staf murabahah can view active murabahah list')->todo();
test('ketua and staf murabahah can view murabahah history of all members')->todo();
test('members can view their own ongoing murabahah financings')->todo();
test('members can view their own murabahah financing history')->todo();

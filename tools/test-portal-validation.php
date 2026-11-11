<?php
require dirname(__DIR__) . '/api/portal-validation.php';
function check(bool $condition, string $message): void { if (!$condition) { throw new RuntimeException($message); } }
$valid = ['students'=>[['id'=>'s1']], 'tasks'=>[], 'items'=>[['id'=>'i1','qty'=>5,'min'=>2]], 'moves'=>[['type'=>'in','qty'=>5]], 'attendance'=>['2026-11-10|A'=>['marks'=>['s1'=>'P']]]];
check(portal_validate_records($valid) === null, 'Valid records rejected');
$bad=$valid; $bad['items'][0]['qty']=-1; check(portal_validate_records($bad)!==null, 'Negative stock accepted');
$bad=$valid; $bad['items'][0]['qty']=1.5; check(portal_validate_records($bad)!==null, 'Fractional stock accepted');
$bad=$valid; $bad['students'][]=['id'=>'s1']; check(portal_validate_records($bad)!==null, 'Duplicate IDs accepted');
$bad=$valid; $bad['moves'][0]['qty']=0; check(portal_validate_records($bad)!==null, 'Empty movement accepted');
$bad=$valid; $bad['attendance']['2026-11-10|A']['marks']['s1']='X'; check(portal_validate_records($bad)!==null, 'Invalid mark accepted');
$bad=$valid; $bad['attendance']['2026-11-10|A']['marks']['s1']='A'; check(portal_validate_records($bad)===null, 'Absent mark rejected');
echo "Portal record validation: 7 checks passed.\n";

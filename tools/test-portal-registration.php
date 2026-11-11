<?php
require dirname(__DIR__) . '/api/portal-registration.php';
function verify(bool $ok, string $message): void { if (!$ok) throw new RuntimeException($message); }
$new = portal_registration_student([], '2026/001', 'New Student', 'Year 1', '0788123456');
verify($new['name'] === 'New Student' && $new['cls'] === 'Year 1' && $new['phone'] === '0788123456', 'New student fields not retained');
verify(strlen($new['id']) <= 48 && $new['id'] !== '', 'New student ID invalid');
$other = portal_registration_student([], '2026/002', 'Other Student', 'Year 1', '0788123457');
verify($other['id'] !== $new['id'], 'Generated IDs collided');
$existing = ['id'=>'s1','reg'=>'ABC/001','name'=>'Existing Student','cls'=>'Year 2','phone'=>'0788123458','extra'=>'preserve'];
$linked = portal_registration_student([$existing], 'abc/001', 'Submitted Name', 'Year 1', '0788123459');
verify($linked === $existing, 'Existing student identity or metadata changed');
$rejected=false;
try { portal_registration_student([['reg'=>'bad']], 'BAD', 'Student', 'Class', '0788123456'); } catch (DomainException $error) { $rejected=true; }
verify($rejected, 'Malformed existing record accepted');
echo "Student self-registration: 5 checks passed.\n";

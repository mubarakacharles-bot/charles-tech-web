<?php
declare(strict_types=1);

/** Reuse an existing registration number, or create a new student record. */
function portal_registration_student(array $students, string $registration, string $name, string $class, string $phone): array
{
    foreach ($students as $candidate) {
        if (is_array($candidate) && strcasecmp((string) ($candidate['reg'] ?? ''), $registration) === 0) {
            if (empty($candidate['id']) || strlen((string) $candidate['id']) > 48) {
                throw new DomainException('The existing student record cannot be linked. Ask the administrator for help.');
            }
            return $candidate;
        }
    }
    return ['id' => 'student-' . bin2hex(random_bytes(16)), 'name' => $name,
        'cls' => $class, 'reg' => $registration, 'phone' => $phone];
}

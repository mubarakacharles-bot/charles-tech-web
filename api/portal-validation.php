<?php
declare(strict_types=1);

/** Validate core records without changing attendance scoring or portal roles. */
function portal_validate_records(array $state): ?string
{
    foreach (['students', 'items', 'tasks'] as $collection) {
        $ids = [];
        foreach ($state[$collection] as $record) {
            if (!is_array($record) || !is_string($record['id'] ?? null) || $record['id'] === '' || strlen($record['id']) > 48) {
                return 'Each ' . $collection . ' record must have a valid ID.';
            }
            if (isset($ids[$record['id']])) {
                return 'Duplicate record IDs were found in ' . $collection . '.';
            }
            $ids[$record['id']] = true;
        }
    }
    foreach ($state['items'] as $item) {
        foreach (['qty', 'min'] as $field) {
            if (!is_int($item[$field] ?? null) || $item[$field] < 0 || $item[$field] > 2147483647) {
                return 'Stock quantities and reorder levels must be non-negative whole numbers.';
            }
        }
    }
    foreach ($state['moves'] as $move) {
        if (!is_array($move) || !in_array($move['type'] ?? null, ['in', 'out'], true)
            || !is_int($move['qty'] ?? null) || $move['qty'] < 1 || $move['qty'] > 2147483647) {
            return 'Stock movements must have a valid direction and a positive whole-number quantity.';
        }
    }
    foreach ($state['attendance'] as $record) {
        if (!is_array($record) || !is_array($record['marks'] ?? null)) {
            return 'Attendance records must contain student marks.';
        }
        foreach ($record['marks'] as $mark) {
            if (!in_array($mark, ['P', 'A'], true)) {
                return 'Attendance marks must be Present or Absent.';
            }
        }
    }
    return null;
}

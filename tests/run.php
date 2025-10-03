<?php
require __DIR__ . '/../app/Core/bootstrap.php';

use App\Support\AdvancedMatching;
use App\Services\ConsentService;
use App\Services\IngestService;

function check(bool $condition, string $message): void {
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function testAdvancedMatchingEmail(): void {
    $result = AdvancedMatching::normalize(['email' => ' USER@Example.com ']);
    $expected = hash('sha256', 'user@example.com');
    check(isset($result['em']), 'Expected em hash');
    check($result['em'] === $expected, 'Email hash mismatch');
}

function testAdvancedMatchingPhone(): void {
    $result = AdvancedMatching::normalize(['phone' => '+55 (11) 91234-5678']);
    $expected = hash('sha256', '5511912345678');
    check($result['ph'] === $expected, 'Phone hash mismatch');
}

function invokePrivate(object $instance, string $method, array $args = []) {
    $ref = new ReflectionClass($instance);
    $m = $ref->getMethod($method);
    $m->setAccessible(true);
    return $m->invokeArgs($instance, $args);
}

function testConsentServiceNormalizePurposes(): void {
    $service = new ConsentService();
    $result = invokePrivate($service, 'normalizePurposes', [['ads' => 'true', 'analytics' => false, 'other']]);
    check($result['ads'] === true, 'ads should be true');
    check($result['analytics'] === false, 'analytics should be false');
    check($result['other'] === true, 'other should be true');
}

function testIngestServiceStringValue(): void {
    $service = new IngestService();
    $value = invokePrivate($service, 'stringValue', [' evt-123 ']);
    check($value === 'evt-123', 'stringValue should trim');
    $numeric = invokePrivate($service, 'stringValue', [42]);
    check($numeric === '42', 'stringValue should cast numbers');
    $nullValue = invokePrivate($service, 'stringValue', ['   ']);
    check($nullValue === null, 'stringValue should return null for empty');
}

$tests = [
    'testAdvancedMatchingEmail',
    'testAdvancedMatchingPhone',
    'testConsentServiceNormalizePurposes',
    'testIngestServiceStringValue',
];

$passed = 0;
foreach ($tests as $test) {
    try {
        $test();
        $passed++;
        echo "✔ {$test}\n";
    } catch (Throwable $e) {
        echo "✘ {$test}: " . $e->getMessage() . "\n";
        exit(1);
    }
}

echo "\n{$passed}/" . count($tests) . " tests passed.\n";

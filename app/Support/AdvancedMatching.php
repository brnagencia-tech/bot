<?php
namespace App\Support;

class AdvancedMatching
{
    /**
     * Normaliza e hasheia identificadores seguindo regras simples.
     *
     * @param array<string,mixed> $input
     * @return array<string,string>
     */
    public static function normalize(array $input): array
    {
        $normalized = [];
        foreach ($input as $key => $value) {
            if (!is_string($value)) {
                continue;
            }
            $value = trim($value);
            if ($value === '') {
                continue;
            }
            switch ($key) {
                case 'email':
                    $normalized['em'] = self::hash(strtolower($value));
                    break;
                case 'phone':
                    $digits = preg_replace('/[^0-9]/', '', $value) ?? '';
                    if ($digits !== '') {
                        $normalized['ph'] = self::hash($digits);
                    }
                    break;
                case 'first_name':
                case 'last_name':
                case 'city':
                case 'state':
                case 'country':
                case 'gender':
                    $normalized[self::mapKey($key)] = self::hash(strtolower($value));
                    break;
                case 'zip':
                    $normalized['zp'] = self::hash(strtolower(str_replace([' ', '-'], '', $value)));
                    break;
                case 'dob':
                    $normalized['db'] = self::hash(str_replace(['-', '/'], '', $value));
                    break;
                case 'external_id':
                    $normalized['external_id'] = self::hash($value);
                    break;
            }
        }
        return $normalized;
    }

    private static function hash(string $value): string
    {
        return hash('sha256', $value);
    }

    private static function mapKey(string $key): string
    {
        return match ($key) {
            'first_name' => 'fn',
            'last_name' => 'ln',
            'city' => 'ct',
            'state' => 'st',
            'country' => 'country',
            'gender' => 'ge',
            default => $key,
        };
    }
}

<?php

declare(strict_types=1);

namespace App\Hashing;

use Illuminate\Contracts\Hashing\Hasher;
use RuntimeException;

/**
 * Runtime-adaptive hasher for hosts where the compiled PHP cannot create
 * certain password hashes (observed on the Vercel PHP runtime: bcrypt
 * creation returns false while verification still works).
 *
 * At construction the hasher probes each candidate algorithm and keeps the
 * first one whose hash AND verify round-trip succeeds, so any hash it
 * creates is guaranteed verifiable by the same runtime. Verification of
 * existing hashes is format-agnostic (password_verify detects $2y$,
 * $argon2id$, ... from the stored string), so historical bcrypt accounts
 * keep logging in regardless of which algorithm is selected.
 */
class FallbackHasher implements Hasher
{
    /** @return array<int, string> */
    private function candidates(): array
    {
        $candidates = [PASSWORD_BCRYPT];

        // Constants are conditional because argon2 is an optional compile
        // flag; referencing an undefined constant is fatal in PHP 8.
        if (defined('PASSWORD_ARGON2ID')) {
            $candidates[] = PASSWORD_ARGON2ID;
        }

        if (defined('PASSWORD_ARGON2I')) {
            $candidates[] = PASSWORD_ARGON2I;
        }

        return $candidates;
    }

    private ?string $algorithm;

    private array $options;

    public function __construct(array $options = [])
    {
        $this->options = $options;
        $this->algorithm = $this->probeAlgorithm();
    }

    public function make($value, array $options = []): string
    {
        if ($this->algorithm === null) {
            throw new RuntimeException(
                'No password hashing algorithm on this runtime can create and verify hashes.'
            );
        }

        try {
            $hash = password_hash(
                (string) $value,
                $this->algorithm,
                $this->optionsFor($this->algorithm, $options)
            );
        } catch (\Throwable $e) {
            throw new RuntimeException('Password hashing failed: '.$e->getMessage(), 0, $e);
        }

        if ($hash === false || ! $this->check($value, $hash)) {
            throw new RuntimeException('Password hashing failed on this runtime.');
        }

        return $hash;
    }

    public function info($hashedValue): array
    {
        return is_string($hashedValue) ? password_get_info($hashedValue) : [];
    }

    public function check($value, $hashedValue, array $options = []): bool
    {
        if (! is_string($hashedValue) || $hashedValue === '') {
            return false;
        }

        return password_verify((string) $value, $hashedValue);
    }

    public function needsRehash($hashedValue, array $options = []): bool
    {
        if ($this->algorithm === null || ! is_string($hashedValue)) {
            return false;
        }

        return password_needs_rehash(
            $hashedValue,
            $this->algorithm,
            $this->optionsFor($this->algorithm, $options)
        );
    }

    private function probeAlgorithm(): ?string
    {
        foreach ($this->candidates() as $algorithm) {
            try {
                $hash = @password_hash('fallback-hasher-probe', $algorithm, $this->optionsFor($algorithm, []));
            } catch (\Throwable) {
                continue;
            }

            if ($hash !== false && password_verify('fallback-hasher-probe', $hash)) {
                return $algorithm;
            }
        }

        return null;
    }

    private function optionsFor(?string $algorithm, array $options): array
    {
        if ($algorithm === null || str_starts_with((string) $algorithm, 'argon')) {
            return $options;
        }

        // Clamp defensively: an invalid BCRYPT_ROUNDS in the hosting env
        // (empty or 0) makes password_hash fail with a fatal cost error -
        // this was the production breakage behind "Bcrypt hashing not
        // supported".
        $cost = (int) ($options['cost'] ?? $this->options['cost'] ?? 12);
        $cost = ($cost >= 4 && $cost <= 31) ? $cost : 12;

        return array_merge(['cost' => $cost], $options);
    }
}

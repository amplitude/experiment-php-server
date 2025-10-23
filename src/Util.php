<?php

namespace AmplitudeExperiment;

function hashCode(string $s): int
{
    $hash = 0;
    if (strlen($s) === 0) {
        return $hash;
    }
    for ($i = 0; $i < strlen($s); $i++) {
        $chr = ord($s[$i]);
        // Use bitwise operations to ensure we stay within 32-bit signed integer range
        // This prevents float conversion and maintains compatibility with PHP 8.5+
        $hash = ((($hash << 5) - $hash) + $chr) & 0xFFFFFFFF;
        // Convert from unsigned 32-bit to signed 32-bit
        if ($hash > 0x7FFFFFFF) {
            $hash = $hash - 0x100000000;
        }
    }
    return $hash;
}

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
        $hash = (int) (($hash << 5) - $hash + $chr);
    }
    return $hash;
}

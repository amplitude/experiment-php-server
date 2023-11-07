<?php

namespace AmplitudeExperiment\EvaluationCore;

use Exception;

function select($selectable, $selector)
{
    if (!$selector || count($selector) === 0) {
        return null;
    }

    foreach ($selector as $selectorElement) {
        if (!$selectorElement || !$selectable || !is_array($selectable)) {
            return null;
        }

        if (array_key_exists($selectorElement, $selectable)) {
            $selectable = $selectable[$selectorElement];
        } else {
            return null;
        }
    }

    // "0" is falsy in PHP, so we need to check for it explicitly
    if (!$selectable && $selectable !== '0') {
        return null;
    } else {
        return $selectable;
    }
}

/**
 * @throws Exception
 */
function topologicalSort($flags, $flagKeys = null): array
{
    $available = $flags;
    $result = [];
    $isNullOrEmpty = !$flagKeys || count($flagKeys) === 0;
    $startingKeys = $isNullOrEmpty ? array_keys($available) : $flagKeys;

    foreach ($startingKeys as $flagKey) {
        $traversal = parentTraversal($flagKey, $available);
        if ($traversal) {
            $result = array_merge($result, $traversal);
        }
    }

    return $result;
}

/**
 * @throws Exception
 */
function parentTraversal($flagKey, &$available, $path = []): ?array
{
    $flag = $available[$flagKey] ?? null;

    if (!$flag) {
        return null;
    } elseif (empty($flag["dependencies"])) {
        unset($available[$flag["key"]]);
        return [$flag];
    }

    $path[] = $flag["key"];
    $result = [];

    foreach ($flag["dependencies"] as $parentKey) {
        if (in_array($parentKey, $path)) {
            throw new Exception("Detected a cycle between flags " . implode(',', $path));
        }

        $traversal = parentTraversal($parentKey, $available, $path);
        if ($traversal) {
            $result = array_merge($result, $traversal);
        }
    }
    $result[] = $flag;
    array_pop($path);
    unset($available[$flag["key"]]);
    return $result;
}


<?php

namespace AmplitudeExperiment\EvaluationCore;

use AmplitudeExperiment\EvaluationCore\Types\EvaluationFlag;
use Exception;

function select($selectable, $selector)
{
    if (!$selector || count($selector) === 0) {
        return null;
    }

    foreach ($selector as $selectorElement) {
        if ($selectable instanceof Types\EvaluationVariant) {
            $selectable = [
                'key' => $selectable->key,
                'value' => $selectable->value,
                'payload' => $selectable->payload,
                'metadata' => $selectable->metadata
            ];
        }

        if (!is_bool($selectable) && (!$selectable || !is_array($selectable))) {
            return null;
        }

        if (array_key_exists($selectorElement, $selectable)) {
            $selectable = $selectable[$selectorElement];
        } else {
            return null;
        }
    }

    // "0" is falsy in PHP, so we need to check for it explicitly
    // Also handle boolean values explicitly
    if ((!$selectable && $selectable !== '0' && $selectable !== 0 && $selectable !== false) || $selectable === null) {
        return null;
    } else {
        return $selectable;
    }
}

/**
 * @param array<string, EvaluationFlag> $flags
 * @param string[]|null $flagKeys
 * @return EvaluationFlag[]
 * @throws Exception
 */
function topologicalSort(array $flags, ?array $flagKeys = null): array
{
    $available = [];
    // Index flags by key for lookup
    foreach ($flags as $flag) {
        $available[$flag->key] = $flag;
    }

    $result = [];
    $startingKeys = $flagKeys ?? array_keys($available);

    if (empty($startingKeys)) {
        return array_values($flags);
    }

    foreach ($startingKeys as $flagKey) {
        if (!array_key_exists($flagKey, $available)) {
            continue;
        }
        $traversal = parentTraversal($flagKey, $available);
        if ($traversal !== null) {
            array_push($result, ...$traversal);
        }
    }

    return array_values(array_unique($result, SORT_REGULAR));
}

/**
 * @param string $flagKey
 * @param array<string, EvaluationFlag> $available
 * @param string[] $path
 * @return EvaluationFlag[]|null
 * @throws Exception
 */
function parentTraversal(string $flagKey, array &$available, array $path = []): ?array
{
    $flag = $available[$flagKey] ?? null;
    if (!$flag) {
        return null;
    }

    if (!$flag->dependencies || empty($flag->dependencies)) {
        unset($available[$flag->key]);
        return [$flag];
    }

    $path[] = $flag->key;
    $result = [];

    foreach ($flag->dependencies as $parentKey) {
        if (in_array($parentKey, $path)) {
            throw new Exception("Detected a cycle between flags " . implode(',', $path));
        }
        if (array_key_exists($parentKey, $available)) {
            $traversal = parentTraversal($parentKey, $available, $path);
            if ($traversal !== null) {
                array_push($result, ...$traversal);
            }
        }
    }

    $result[] = $flag;
    array_pop($path);
    unset($available[$flag->key]);
    return $result;
}

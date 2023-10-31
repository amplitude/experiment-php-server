<?php

namespace AmplitudeExperiment\EvaluationCore;

use Exception;

class Util
{
    public static function select($selectable, $selector)
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

        if (!$selectable) {
            return null;
        } else {
            return $selectable;
        }
    }

    /**
     * @throws Exception
     */
    public static function topologicalSort($flags, $flagKeys = null): array
    {
        $available = $flags;
        $result = [];
        $startingKeys = $flagKeys ?? array_keys($available);

        foreach ($startingKeys as $flagKey) {
            $traversal = Util::parentTraversal($flagKey, $available);
            if ($traversal) {
                $result = array_merge($result, $traversal);
            }
        }

        return $result;
    }

    /**
     * @throws Exception
     */
    private static function parentTraversal($flagKey, &$available, $path = []): ?array
    {
        $flag = $available[$flagKey] ?? null;

        if (!$flag) {
            return null;
        } elseif (empty($flag->dependencies)) {
            unset($available[$flag->key]);
            return [$flag];
        }

        $path[] = $flag->key;
        $result = [];

        foreach ($flag->dependencies as $parentKey) {
            if (in_array($parentKey, $path)) {
                throw new Exception("Detected a cycle between flags " . implode(',', $path));
            }

            $traversal = Util::parentTraversal($parentKey, $available, $path);
            if ($traversal) {
                $result = array_merge($result, $traversal);
            }
        }
        $result[] = $flag;
        array_pop($path);
        unset($available[$flag->key]);
        return $result;
    }
}

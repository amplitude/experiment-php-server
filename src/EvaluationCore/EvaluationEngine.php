<?php

namespace AmplitudeExperiment\EvaluationCore;

require_once __DIR__ . '/Util.php';

use AmplitudeExperiment\EvaluationCore\Types\EvaluationFlag;
use AmplitudeExperiment\EvaluationCore\Types\EvaluationVariant;
use AmplitudeExperiment\EvaluationCore\Types\EvaluationSegment;
use AmplitudeExperiment\EvaluationCore\Types\EvaluationCondition;
use Exception;

class EvaluationEngine
{
    /**
     * @param array<string, mixed> $context
     * @param EvaluationFlag[] $flags
     * @return array<string, EvaluationVariant>
     */
    public function evaluate(array $context, array $flags): array
    {
        $results = [];
        $target = [
            'context' => $context,
            'result' => &$results,
        ];

        foreach ($flags as $flag) {
            $variant = $this->evaluateFlag($target, $flag);
            if ($variant !== null) {
                $results[$flag->key] = $variant;
            }
        }

        return $results;
    }

    /**
     * @param array<string, mixed> $target
     * @param EvaluationFlag $flag
     * @return EvaluationVariant|null
     */
    private function evaluateFlag(array $target, EvaluationFlag $flag): ?EvaluationVariant
    {
        $result = null;

        foreach ($flag->segments as $segment) {
            $result = $this->evaluateSegment($target, $flag, $segment);
            if ($result !== null) {
                $metadata = array_merge(
                    $flag->metadata ?? [],
                    $segment->metadata ?? [],
                    $result->metadata ?? []
                );

                return new EvaluationVariant(
                    $result->key,
                    $result->value,
                    $result->payload,
                    $metadata
                );
            }
        }
        return $result;
    }


    /**
     * @param array<string, mixed> $target
     * @param EvaluationFlag $flag
     * @param EvaluationSegment $segment
     * @return EvaluationVariant|null
     */
    private function evaluateSegment(array $target, EvaluationFlag $flag, EvaluationSegment $segment): ?EvaluationVariant
    {
        if ($segment->conditions === null) {
            $variantKey = $this->bucket($target, $segment);

            if ($variantKey !== null && isset($flag->variants[$variantKey])) {
                return $flag->variants[$variantKey];
            } else {
                return null;
            }
        }

        foreach ($segment->conditions as $conditions) {
            $match = true;

            foreach ($conditions as $condition) {
                $match = $this->matchCondition($target, $condition);

                if (!$match) {
                    break;
                }
            }

            if ($match) {
                $variantKey = $this->bucket($target, $segment);

                if ($variantKey !== null && isset($flag->variants[$variantKey])) {
                    return $flag->variants[$variantKey];
                } else {
                    return null;
                }
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $target
     * @param EvaluationCondition $condition
     * @return bool
     */
    private function matchCondition(array $target, EvaluationCondition $condition): bool
    {
        $propValue = select($target, $condition->selector);

        if ($propValue === null) {
            return $this->matchNull($condition->op, $condition->values);
        } elseif (is_bool($propValue)) {
            return $this->matchBoolean($propValue, $condition->op, $condition->values);
        } elseif ($this->isSetOperator($condition->op)) {
            $propValueStringList = $this->coerceStringArray($propValue);

            if ($propValueStringList === null) {
                return false;
            }

            return $this->matchSet($propValueStringList, $condition->op, $condition->values);
        } else {
            $propValueString = $this->coerceString($propValue);

            if ($propValueString !== null) {
                return $this->matchString(
                    $propValueString,
                    $condition->op,
                    $condition->values
                );
            } else {
                return false;
            }
        }
    }

    /**
     * @param string $key
     * @return int
     */
    private function getHash(string $key): int
    {
        return Murmur3::hash3_int($key);
    }

    /**
     * @param array<string, mixed> $target
     * @param EvaluationSegment $segment
     * @return string|null
     */
    private function bucket(array $target, EvaluationSegment $segment): ?string
    {
        if ($segment->bucket === null) {
            return $segment->variant ?? null;
        }

        $bucketingValue = $this->coerceString(select($target, $segment->bucket->selector));

        if ($bucketingValue === null || strlen($bucketingValue) === 0) {
            return $segment->variant ?? null;
        }

        $keyToHash = "{$segment->bucket->salt}/$bucketingValue";
        $hash = $this->getHash($keyToHash);
        $allocationValue = $hash % 100;
        $distributionValue = floor($hash / 100);

        foreach ($segment->bucket->allocations as $allocation) {
            $allocationStart = $allocation->range[0];
            $allocationEnd = $allocation->range[1];

            if ($allocationValue >= $allocationStart && $allocationValue < $allocationEnd) {
                foreach ($allocation->distributions as $distribution) {
                    $distributionStart = $distribution->range[0];
                    $distributionEnd = $distribution->range[1];

                    if ($distributionValue >= $distributionStart && $distributionValue < $distributionEnd) {
                        return $distribution->variant;
                    }
                }
            }
        }

        return $segment->variant ?? null;
    }

    /**
     * @param string $op
     * @param array<string> $filterValues
     * @return bool
     */
    private function matchNull(string $op, array $filterValues): bool
    {
        $containsNone = $this->containsNone($filterValues);

        switch ($op) {
            case EvaluationOperator::IS:
            case EvaluationOperator::CONTAINS:
            case EvaluationOperator::LESS_THAN:
            case EvaluationOperator::LESS_THAN_EQUALS:
            case EvaluationOperator::GREATER_THAN:
            case EvaluationOperator::GREATER_THAN_EQUALS:
            case EvaluationOperator::VERSION_LESS_THAN:
            case EvaluationOperator::VERSION_LESS_THAN_EQUALS:
            case EvaluationOperator::VERSION_GREATER_THAN:
            case EvaluationOperator::VERSION_GREATER_THAN_EQUALS:
            case EvaluationOperator::SET_IS:
            case EvaluationOperator::SET_CONTAINS:
            case EvaluationOperator::SET_CONTAINS_ANY:
                return $containsNone;
            case EvaluationOperator::IS_NOT:
            case EvaluationOperator::DOES_NOT_CONTAIN:
            case EvaluationOperator::SET_DOES_NOT_CONTAIN:
            case EvaluationOperator::SET_DOES_NOT_CONTAIN_ANY:
                return !$containsNone;
            default:
                return false;
        }
    }

    /**
     * @param array<string> $propValues
     * @param string $op
     * @param array<string> $filterValues
     * @return bool
     */
    private function matchSet(array $propValues, string $op, array $filterValues): bool
    {
        switch ($op) {
            case EvaluationOperator::SET_IS:
                return $this->setEquals($propValues, $filterValues);
            case EvaluationOperator::SET_IS_NOT:
                return !$this->setEquals($propValues, $filterValues);
            case EvaluationOperator::SET_CONTAINS:
                return $this->matchesSetContainsAll($propValues, $filterValues);
            case EvaluationOperator::SET_DOES_NOT_CONTAIN:
                return !$this->matchesSetContainsAll($propValues, $filterValues);
            case EvaluationOperator::SET_CONTAINS_ANY:
                return $this->matchesSetContainsAny($propValues, $filterValues);
            case EvaluationOperator::SET_DOES_NOT_CONTAIN_ANY:
                return !$this->matchesSetContainsAny($propValues, $filterValues);
            default:
                return false;
        }
    }

    /**
     * @param string $propValue
     * @param string $op
     * @param array<string> $filterValues
     * @return bool
     */
    private function matchString(string $propValue, string $op, array $filterValues): bool
    {
        switch ($op) {
            case EvaluationOperator::IS:
                return $this->matchesIs($propValue, $filterValues);
            case EvaluationOperator::IS_NOT:
                return !$this->matchesIs($propValue, $filterValues);
            case EvaluationOperator::CONTAINS:
                return $this->matchesContains($propValue, $filterValues);
            case EvaluationOperator::DOES_NOT_CONTAIN:
                return !$this->matchesContains($propValue, $filterValues);
            case EvaluationOperator::LESS_THAN:
            case EvaluationOperator::LESS_THAN_EQUALS:
            case EvaluationOperator::GREATER_THAN:
            case EvaluationOperator::GREATER_THAN_EQUALS:
                return $this->matchesComparable(
                    $propValue,
                    $op,
                    $filterValues,
                    fn(string $value): ?int => $this->parseNumber($value),
                    fn($a, string $op, $b): bool => $this->comparator($a, $op, $b)
                );
            case EvaluationOperator::VERSION_LESS_THAN:
            case EvaluationOperator::VERSION_LESS_THAN_EQUALS:
            case EvaluationOperator::VERSION_GREATER_THAN:
            case EvaluationOperator::VERSION_GREATER_THAN_EQUALS:
                return $this->matchesComparable(
                    $propValue,
                    $op,
                    $filterValues,
                    fn(string $value): ?SemanticVersion => SemanticVersion::parse($value),
                    fn(SemanticVersion $a, string $op, SemanticVersion $b): bool => $this->versionComparator($a, $op, $b)
                );
            case EvaluationOperator::REGEX_MATCH:
                return $this->matchesRegex($propValue, $filterValues);
            case EvaluationOperator::REGEX_DOES_NOT_MATCH:
                return !$this->matchesRegex($propValue, $filterValues);
            default:
                return false;
        }
    }

    /**
     * @param string $propValue
     * @param array<string> $filterValues
     * @return bool
     */
    private function matchesIs(string $propValue, array $filterValues): bool
    {
        // Case-sensitive exact match
        if (in_array($propValue, $filterValues)) {
            return true;
        }
        $lowerPropValue = strtolower($propValue);
        if ($lowerPropValue === 'true' && in_array('true', array_map('strtolower', $filterValues))) {
            return true;
        }
        if ($lowerPropValue === 'false' && in_array('false', array_map('strtolower', $filterValues))) {
            return true;
        }
        return false;
    }

    /**
     * @param string $propValue
     * @param array<string> $filterValues
     * @return bool
     */
    private function matchesContains(string $propValue, array $filterValues): bool
    {
        foreach ($filterValues as $filterValue) {
            if (stripos($propValue, $filterValue) !== false) {
                return true;
            }
        }
        return false;
    }

    private function matchesComparable(string $propValue, string $op, array $filterValues, callable $typeTransformer, callable $typeComparator): bool
    {
        $propValueTransformed = $typeTransformer($propValue);
        $filterValuesTransformed = array_filter(array_map($typeTransformer, $filterValues), function ($filterValue) {
            return $filterValue !== null;
        });

        if ($propValueTransformed === null || empty($filterValuesTransformed)) {
            foreach ($filterValues as $filterValue) {
                if ($this->comparator($propValue, $op, $filterValue)) {
                    return true;
                }
            }
        } else {
            foreach ($filterValuesTransformed as $filterValueTransformed) {
                if ($typeComparator($propValueTransformed, $op, $filterValueTransformed)) {
                    return true;
                }
            }
        }
        return false;
    }

    private function comparator($propValue, $op, $filterValue): bool
    {
        switch ($op) {
            case EvaluationOperator::LESS_THAN:
            case EvaluationOperator::VERSION_LESS_THAN:
                return $propValue < $filterValue;
            case EvaluationOperator::LESS_THAN_EQUALS:
            case EvaluationOperator::VERSION_LESS_THAN_EQUALS:
                return $propValue <= $filterValue;
            case EvaluationOperator::GREATER_THAN:
            case EvaluationOperator::VERSION_GREATER_THAN:
                return $propValue > $filterValue;
            case EvaluationOperator::GREATER_THAN_EQUALS:
            case EvaluationOperator::VERSION_GREATER_THAN_EQUALS:
                return $propValue >= $filterValue;
            default:
                return false;
        }
    }

    private function versionComparator(SemanticVersion $propValue, $op, SemanticVersion $filterValue): bool
    {
        $compareTo = $propValue->compareTo($filterValue);
        switch ($op) {
            case EvaluationOperator::LESS_THAN:
            case EvaluationOperator::VERSION_LESS_THAN:
                return $compareTo < 0;
            case EvaluationOperator::LESS_THAN_EQUALS:
            case EvaluationOperator::VERSION_LESS_THAN_EQUALS:
                return $compareTo <= 0;
            case EvaluationOperator::GREATER_THAN:
            case EvaluationOperator::VERSION_GREATER_THAN:
                return $compareTo > 0;
            case EvaluationOperator::GREATER_THAN_EQUALS:
            case EvaluationOperator::VERSION_GREATER_THAN_EQUALS:
                return $compareTo >= 0;
            default:
                return false;
        }
    }

    private function matchesRegex(string $propValue, array $filterValues): bool
    {
        foreach ($filterValues as $filterValue) {
            if (preg_match('#' . $filterValue . '#', $propValue)) {
                return true;
            }
        }
        return false;
    }

    private function containsNone(array $filterValues): bool
    {
        return in_array('(none)', $filterValues);
    }

    private function parseNumber(string $value): ?int
    {
        $parsedValue = filter_var($value, FILTER_VALIDATE_INT);
        return $parsedValue === false ? null : $parsedValue;
    }

    private function coerceString($value): ?string
    {
        if ($value === null) {
            return null;
        }
        if (is_object($value) || is_array($value)) {
            return json_encode($value);
        }
        return strval($value);
    }

    private function coerceStringArray($value): ?array
    {
        if (is_array($value)) {
            return array_filter(array_map([$this, 'coerceString'], $value));
        }
        $stringValue = strval($value);
        try {
            $parsedValue = json_decode($stringValue, true);
            if (is_array($parsedValue)) {
                return array_filter(array_map([$this, 'coerceString'], $parsedValue));
            } else {
                return null;
            }
        } catch (Exception $e) {
            return null;
        }
    }

    private function isSetOperator(string $op): bool
    {
        $validOperators = [
            EvaluationOperator::SET_IS,
            EvaluationOperator::SET_IS_NOT,
            EvaluationOperator::SET_CONTAINS,
            EvaluationOperator::SET_DOES_NOT_CONTAIN,
            EvaluationOperator::SET_CONTAINS_ANY,
            EvaluationOperator:: SET_DOES_NOT_CONTAIN_ANY
        ];
        return in_array($op, $validOperators);
    }

    private function setEquals(array $xa, array $ya): bool
    {
        $uniqueXa = array_unique($xa);
        $uniqueYa = array_unique($ya);

        sort($uniqueXa);
        sort($uniqueYa);

        return $uniqueXa === $uniqueYa;
    }

    private function matchesSetContainsAll(array $propValues, array $filterValues): bool
    {
        if (count($propValues) < count($filterValues)) {
            return false;
        }
        foreach ($filterValues as $filterValue) {
            if (!$this->matchesIs($filterValue, $propValues)) {
                return false;
            }
        }
        return true;
    }

    private function matchesSetContainsAny(array $propValues, array $filterValues): bool
    {
        foreach ($filterValues as $filterValue) {
            if ($this->matchesIs($filterValue, $propValues)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param bool $propValue
     * @param string $op
     * @param array<string> $filterValues
     * @return bool
     */
    private function matchBoolean(bool $propValue, string $op, array $filterValues): bool
    {
        $propValueString = $propValue ? 'true' : 'false';

        switch ($op) {
            case EvaluationOperator::IS:
                foreach ($filterValues as $value) {
                    $lowercaseValue = strtolower($value);
                    if (($propValue && $lowercaseValue === 'true') ||
                        (!$propValue && $lowercaseValue === 'false')) {
                        return true;
                    }
                }
                return false;
            case EvaluationOperator::IS_NOT:
                foreach ($filterValues as $value) {
                    $lowercaseValue = strtolower($value);
                    if (($propValue && $lowercaseValue === 'true') ||
                        (!$propValue && $lowercaseValue === 'false')) {
                        return false;
                    }
                }
                return true;
            default:
                return $this->matchString($propValueString, $op, $filterValues);
        }
    }
}

<?php

namespace AmplitudeExperiment\Flag;

use AmplitudeExperiment\EvaluationCore\Types\EvaluationFlag;
use AmplitudeExperiment\EvaluationCore\Types\EvaluationVariant;
use AmplitudeExperiment\EvaluationCore\Types\EvaluationSegment;
use AmplitudeExperiment\EvaluationCore\Types\EvaluationBucket;
use AmplitudeExperiment\EvaluationCore\Types\EvaluationCondition;
use AmplitudeExperiment\EvaluationCore\Types\EvaluationDistribution;
use AmplitudeExperiment\EvaluationCore\Types\EvaluationAllocation;

/**
 * Creates an array of EvaluationFlag objects from raw flag data
 *
 * @param array<string, mixed> $rawFlags
 * @return EvaluationFlag[]
 */
function createFlagsFromArray(array $rawFlags): array
{
    $flags = [];

    foreach ($rawFlags as $flagData) {
        if (!isset($flagData['key'])) {
            continue;
        }

        // Process variants
        $variants = [];
        if (isset($flagData['variants']) && is_array($flagData['variants'])) {
            foreach ($flagData['variants'] as $variantKey => $variantData) {
                if (!isset($variantData['key'])) {
                    continue;
                }
                $variants[$variantKey] = new EvaluationVariant(
                    $variantData['key'],
                    $variantData['value'] ?? null,
                    $variantData['payload'] ?? null,
                    $variantData['metadata'] ?? null
                );
            }
        }

        // Process segments
        $segments = [];
        if (isset($flagData['segments']) && is_array($flagData['segments'])) {
            foreach ($flagData['segments'] as $segmentData) {
                // Process bucket if exists
                $bucket = null;
                if (isset($segmentData['bucket']) && is_array($segmentData['bucket'])) {
                    $allocations = [];
                    if (isset($segmentData['bucket']['allocations']) && is_array($segmentData['bucket']['allocations'])) {
                        foreach ($segmentData['bucket']['allocations'] as $allocationData) {
                            if (!isset($allocationData['distributions'], $allocationData['range'])) {
                                continue;
                            }

                            $distributions = [];
                            foreach ($allocationData['distributions'] as $distributionData) {
                                if (!isset($distributionData['variant'], $distributionData['range'])) {
                                    continue;
                                }
                                $distributions[] = new EvaluationDistribution(
                                    $distributionData['variant'],
                                    $distributionData['range']
                                );
                            }

                            $allocations[] = new EvaluationAllocation(
                                $allocationData['range'],
                                $distributions
                            );
                        }
                    }

                    $bucket = new EvaluationBucket(
                        $segmentData['bucket']['selector'] ?? [],
                        $segmentData['bucket']['salt'] ?? '',
                        $allocations
                    );
                }

                // Process conditions if exists
                $conditions = null;
                if (isset($segmentData['conditions']) && is_array($segmentData['conditions'])) {
                    $conditions = array_map(function ($conditionSet) {
                        return array_map(function ($condition) {
                            if (!isset($condition['op'], $condition['selector'], $condition['values'])) {
                                return null;
                            }
                            return new EvaluationCondition(
                                $condition['selector'],
                                $condition['op'],
                                $condition['values']
                            );
                        }, $conditionSet);
                    }, $segmentData['conditions']);

                    // Remove null values from conditions
                    $conditions = array_map(function($conditionSet) {
                        return array_filter($conditionSet);
                    }, array_filter($conditions));
                }

                $segments[] = new EvaluationSegment(
                    $bucket,
                    $conditions,
                    $segmentData['variant'] ?? null,
                    $segmentData['metadata'] ?? null
                );
            }
        }

        $flags[] = new EvaluationFlag(
            $flagData['key'],
            $variants,
            $segments,
            $flagData['dependencies'] ?? null,
            $flagData['metadata'] ?? null
        );
    }

    return $flags;
}

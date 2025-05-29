<?php
declare(strict_types=1);

namespace AmplitudeExperiment\EvaluationCore;

class EvaluationOperator
{
    public const IS = 'is';
    public const IS_NOT = 'is not';
    public const CONTAINS = 'contains';
    public const DOES_NOT_CONTAIN = 'does not contain';
    public const LESS_THAN = 'less';
    public const LESS_THAN_EQUALS = 'less or equal';
    public const GREATER_THAN = 'greater';
    public const GREATER_THAN_EQUALS = 'greater or equal';
    public const VERSION_LESS_THAN = 'version less';
    public const VERSION_LESS_THAN_EQUALS = 'version less or equal';
    public const VERSION_GREATER_THAN = 'version greater';
    public const VERSION_GREATER_THAN_EQUALS = 'version greater or equal';
    public const SET_IS = 'set is';
    public const SET_IS_NOT = 'set is not';
    public const SET_CONTAINS = 'set contains';
    public const SET_DOES_NOT_CONTAIN = 'set does not contain';
    public const SET_CONTAINS_ANY = 'set contains any';
    public const SET_DOES_NOT_CONTAIN_ANY = 'set does not contain any';
    public const REGEX_MATCH = 'regex match';
    public const REGEX_DOES_NOT_MATCH = 'regex does not match';
}

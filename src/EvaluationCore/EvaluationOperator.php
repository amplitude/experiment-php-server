<?php

namespace AmplitudeExperiment\EvaluationCore;

class EvaluationOperator
{
    const IS = 'is';
    const IS_NOT = 'is not';
    const CONTAINS = 'contains';
    const DOES_NOT_CONTAIN = 'does not contain';
    const LESS_THAN = 'less';
    const LESS_THAN_EQUALS = 'less or equal';
    const GREATER_THAN = 'greater';
    const GREATER_THAN_EQUALS = 'greater or equal';
    const VERSION_LESS_THAN = 'version less';
    const VERSION_LESS_THAN_EQUALS = 'version less or equal';
    const VERSION_GREATER_THAN = 'version greater';
    const VERSION_GREATER_THAN_EQUALS = 'version greater or equal';
    const SET_IS = 'set is';
    const SET_IS_NOT = 'set is not';
    const SET_CONTAINS = 'set contains';
    const SET_DOES_NOT_CONTAIN = 'set does not contain';
    const SET_CONTAINS_ANY = 'set contains any';
    const SET_DOES_NOT_CONTAIN_ANY = 'set does not contain any';
    const REGEX_MATCH = 'regex match';
    const REGEX_DOES_NOT_MATCH = 'regex does not match';
}

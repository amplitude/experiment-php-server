<?php

namespace AmplitudeExperiment\Exception;

use LogicException;

/**
 * Raised when the SDK cannot discover a PSR-18 client or PSR-17 factory and
 * the consumer did not supply one via the config builder. Always indicates
 * a configuration / installation gap, not a transient failure — extends
 * {@link LogicException} so consumers can handle it distinctly from
 * runtime transport faults.
 */
class MissingHttpImplementationException extends LogicException
{
}

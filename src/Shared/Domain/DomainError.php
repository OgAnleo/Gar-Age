<?php

namespace GarAge\Shared\Domain;

use DomainException;

/**
 * Base class for every error raised by the domain.
 */
abstract class DomainError extends DomainException {}

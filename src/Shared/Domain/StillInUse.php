<?php

namespace GarAge\Shared\Domain;

/**
 * An aggregate cannot be removed because other aggregates still refer to it.
 */
abstract class StillInUse extends DomainError {}

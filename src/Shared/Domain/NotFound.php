<?php

namespace GarAge\Shared\Domain;

/**
 * A requested aggregate does not exist (or is not visible to the tenant).
 */
abstract class NotFound extends DomainError {}

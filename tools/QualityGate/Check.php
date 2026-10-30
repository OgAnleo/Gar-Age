<?php

namespace Tools\QualityGate;

interface Check
{
    public function run(): CheckResult;
}

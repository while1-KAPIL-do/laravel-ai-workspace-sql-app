<?php

namespace App\Services\Token;

class PHPEstimatorService
{
    public function estimate(string $text): int
    {
        return (int) ceil(strlen($text) / 4);
    }
}
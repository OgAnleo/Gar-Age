<?php

namespace App\Http\Controllers;

use GarAge\Billing\Application\GenerateDueFees;
use Illuminate\Http\JsonResponse;

/**
 * HTTP adapter for hosts without a cron daemon (Render's free plan): an
 * external scheduler calls it to do what `fees:generate` does every night.
 * Fee generation is idempotent, so calling it more than once is harmless.
 */
class CronController extends Controller
{
    public function fees(GenerateDueFees $generateDueFees): JsonResponse
    {
        $today = now()->toDateTimeImmutable();

        return response()->json([
            'date' => $today->format('Y-m-d'),
            'issued' => $generateDueFees->handle($today),
        ]);
    }
}

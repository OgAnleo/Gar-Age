<?php

namespace App\Http\Controllers;

use Database\Seeders\DemoSeeder;
use GarAge\Billing\Application\GenerateDueFees;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;

/**
 * HTTP adapter for hosts without a cron daemon (Render's free plan): an
 * external scheduler calls it to do the nightly jobs.
 */
class CronController extends Controller
{
    /**
     * What `fees:generate` does. Fee generation is idempotent, so calling it
     * more than once is harmless.
     */
    public function fees(GenerateDueFees $generateDueFees): JsonResponse
    {
        $today = now()->toDateTimeImmutable();

        return response()->json([
            'date' => $today->format('Y-m-d'),
            'issued' => $generateDueFees->handle($today),
        ]);
    }

    /**
     * Rebuild the public demo (its business and its account), whatever
     * visitors did to it. Only on the demo.
     */
    public function demo(): JsonResponse
    {
        abort_unless(config('demo.enabled') === true, 404);

        Artisan::call('db:seed', ['--class' => DemoSeeder::class, '--force' => true]);

        return response()->json(['reset' => true]);
    }
}

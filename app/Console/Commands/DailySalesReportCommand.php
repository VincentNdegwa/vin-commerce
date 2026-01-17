<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Mail\DailySalesReportMail;
use App\Services\SalesReportService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class DailySalesReportCommand extends Command
{
    protected $signature = 'report:daily-sales';

    protected $description = 'Send the daily sales report email';

    public function handle(SalesReportService $service): int
    {
        $toDay = CarbonImmutable::now();
        $from = $toDay->copy()->startOfDay();
        $toTime = $toDay->copy()->endOfDay();

        $dataByCreator = $service->aggregateForPeriodByCreator($from, $toTime);

        if ($dataByCreator === []) {
            return self::SUCCESS;
        }

        $reportName = 'Daily_Sales_Report_'.$toDay->format('Y-m-d');

        foreach ($dataByCreator as $entry) {
            $user = $entry['user'];

            if ($user->email === null) {
                continue;
            }

            $payload = [
                'total_sales' => $entry['total_sales'],
                'total_orders' => $entry['total_orders'],
                'products' => $entry['products'],
            ];

            Mail::to($user->email)->send(new DailySalesReportMail($reportName, $payload));
        }

        return self::SUCCESS;
    }
}

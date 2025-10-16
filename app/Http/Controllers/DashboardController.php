<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * @return \Inertia\Response
     */
    public function __invoke(): Response
    {
        // Get sales data for the last 30 days
        $salesData = $this->getSalesDataForLast30Days();

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'users' => User::count(),
                'clients' => Client::count(),
                'products' => Product::count(),
                'orders' => Order::count(),
                'leads' => Lead::count(),
                'opportunities' => Opportunity::count(),
            ],
            'salesChart' => $salesData,
            'funnelData' => $this->getFunnelData(),
        ]);
    }

    private function getSalesDataForLast30Days(): array
    {
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subDays(29); // 30 days including today

        $sales = Order::select(
            DB::raw('DATE(ordered_at) as date'),
            DB::raw('SUM(total) as total_sales')
        )
            ->whereBetween('ordered_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $categories = [];
        $data = [];

        // Generate all dates for the last 30 days
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $formattedDate = Carbon::now()->subDays($i)->format('d/m');

            $categories[] = $formattedDate;
            $data[] = (float) ($sales[$date]->total_sales ?? 0);
        }

        return [
            'categories' => $categories,
            'data' => $data,
        ];
    }

    private function getFunnelData(): array
    {
        // Leads (total)
        $leadsTotal = Lead::count();

        // Leads Qualificados
        $leadsQualified = Lead::where('status', 'qualified')->count();

        // Oportunidades (abertas - ainda no pipeline)
        $opportunitiesOpen = Opportunity::whereNotIn('stage', ['won', 'lost'])->count();

        // Oportunidades Vencidas (ganhas)
        $opportunitiesWon = Opportunity::where('stage', 'won')->count();

        return [
            'labels' => ['Leads', 'Leads Qualificados', 'Oportunidades', 'Oportunidades Vencidas'],
            'data' => [$leadsTotal, $leadsQualified, $opportunitiesOpen, $opportunitiesWon],
        ];
    }
}

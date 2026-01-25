<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Drug;
use App\Models\Visit;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $orgId = Filament::getTenant()?->id;

        return [
            Stat::make('User Accounts', User::where('organization_id', $orgId)->count())
                ->icon('heroicon-o-users'),

            Stat::make('All Patients', Patient::where('organization_id', $orgId)->count())
                ->icon('heroicon-o-user-group'),

            Stat::make('Visits Today', Visit::where('organization_id', $orgId)
                ->whereDate('created_at', now())
                ->count())
                ->icon('heroicon-o-clipboard-document-check'),


            Stat::make('Pending Appointments', Appointment::where('organization_id', $orgId)
                ->where('status', 'pending')
                ->count())
                ->icon('heroicon-o-calendar-days'),

            Stat::make('Draft Invoices', Invoice::where('organization_id', $orgId)
                ->where('status', 'draft')
                ->count())
                ->icon('heroicon-o-document-text'),

            // 🔹 Drug-related stats
            Stat::make('Active Drugs', Drug::where('organization_id', $orgId)
                ->active()
                ->count())
                ->icon('heroicon-o-beaker'),

            Stat::make('Low Stock Drugs', Drug::where('organization_id', $orgId)
                ->lowStock()
                ->count())
                ->icon('heroicon-o-exclamation-triangle'),

            Stat::make('Expiring Soon (30 days)', Drug::where('organization_id', $orgId)
                ->expiringSoon(30)
                ->count())
                ->icon('heroicon-o-clock'),

            Stat::make('Controlled Substances', Drug::where('organization_id', $orgId)
                ->controlled()
                ->count())
                ->icon('heroicon-o-lock-closed'),
        ];
    }
}

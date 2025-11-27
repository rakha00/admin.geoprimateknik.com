<?php

namespace App\Filament\Resources\FinanceReportResource\Pages;

use App\Filament\Resources\FinanceReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageFinanceReports extends ManageRecords
{
    protected static string $resource = FinanceReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

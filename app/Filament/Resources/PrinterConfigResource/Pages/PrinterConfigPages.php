<?php

namespace App\Filament\Resources\PrinterConfigResource\Pages;

use App\Filament\Resources\PrinterConfigResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;

class ListPrinterConfigs extends ListRecords
{
    protected static string $resource = PrinterConfigResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}

class CreatePrinterConfig extends CreateRecord
{
    protected static string $resource = PrinterConfigResource::class;
}

class EditPrinterConfig extends EditRecord
{
    protected static string $resource = PrinterConfigResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
}

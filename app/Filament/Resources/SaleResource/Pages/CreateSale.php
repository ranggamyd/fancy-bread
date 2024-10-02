<?php

namespace App\Filament\Resources\SaleResource\Pages;

// use App\Models\DriverSchedule;
use Illuminate\Support\Facades\Auth;
// use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\SaleResource;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateSale extends CreateRecord
{
    protected static string $resource = SaleResource::class;

    // protected function handleRecordCreation(array $data): Model
    // {
    //     $driver_id = $data['driver_id'];

    //     unset($data['driver_id']);
    //     $sale = static::getModel()::create($data);

    //     $schedule = new DriverSchedule();
    //     $schedule->sale_id = $sale->id;
    //     $schedule->driver_id = $driver_id;
    //     $schedule->date = $sale->date;

    //     $schedule->save();

    //     return $sale;
    // }

    protected function afterCreate(): void
    {
        $sale = $this->record;

        Notification::make()
            ->icon('heroicon-o-shopping-cart')
            ->title("#{$sale->invoice}")
            ->body("New sale from : {$sale->customer->name} - {$sale->customer->short_address}.")
            ->actions([Action::make('Detail')->url(SaleResource::getUrl('view', ['record' => $sale]))])
            ->sendToDatabase(Auth::user());
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

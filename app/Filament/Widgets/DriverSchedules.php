<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use App\Filament\Resources\SaleResource;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class DriverSchedules extends FullCalendarWidget
{
    protected static ?int $sort = 2;

    public function config(): array
    {
        return [
            // 'initialView' => 'listWeek',
            'displayEventTime' => false,
            'eventDisplay' => 'block',
            'headerToolbar' => [
                'left' => 'dayGridMonth,listWeek',
                'center' => 'title',
                'right' => 'prev,next today',
            ],
        ];
    }

    public function fetchEvents(array $fetchInfo): array
    {
        return Sale::query()
            ->where('date', '>=', $fetchInfo['start'])
            ->where('date', '<=', $fetchInfo['end'])
            ->get()
            ->map(
                fn(Sale $sale) => [
                    'title' => $sale->driver->name . ': ' . $sale->customer->name . ' - ' . $sale->customer->short_address,
                    'start' => $sale->date,
                    'end' => $sale->date,
                    'url' => SaleResource::getUrl(name: 'view', parameters: ['record' => $sale]),
                    'shouldOpenUrlInNewTab' => true
                ]
            )
            ->all();
    }

    public function eventDidMount(): string
    {
        return <<<JS
        function({ event, timeText, isStart, isEnd, isMirror, isPast, isFuture, isToday, el, view }){
            el.setAttribute("x-tooltip", "tooltip");
            el.setAttribute("x-data", "{ tooltip: '"+event.title+"' }");
        }
    JS;
    }
}

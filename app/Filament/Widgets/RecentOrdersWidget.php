<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentOrdersWidget extends BaseWidget
{
    protected static ?string $heading = 'Pesanan Terbaru';

    protected static ?int $sort = 2;

    protected static bool $isLazy = true;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(Order::query()->with(['user', 'payment'])->latest()->limit(5))
            ->columns([
                TextColumn::make('id')
                    ->label('ID Pesanan')
                    ->prefix('#')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Pelanggan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('total_price')
                    ->label('Total')
                    ->money('idr')
                    ->sortable(),

                TextColumn::make('order_status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Order::getStatusLabels()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        Order::STATUS_PENDING => 'warning',
                        Order::STATUS_PAID => 'info',
                        Order::STATUS_PROCESSING => 'primary',
                        Order::STATUS_SHIPPED => 'info',
                        Order::STATUS_COMPLETED => 'success',
                        Order::STATUS_CANCELLED => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated(false)
            ->poll('30s')
            ->emptyStateHeading('Belum ada pesanan')
            ->emptyStateDescription('Pesanan dari pelanggan akan muncul di sini.')
            ->emptyStateIcon('heroicon-o-shopping-bag');
    }
}

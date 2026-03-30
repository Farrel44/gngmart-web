<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables\Columns\BadgeColumn;
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

                BadgeColumn::make('order_status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'info' => fn ($state) => in_array($state, ['paid', 'processing']),
                        'success' => 'completed',
                        'danger' => 'cancelled',
                        'primary' => 'shipped',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Tertunda',
                        'paid' => 'Dibayar',
                        'processing' => 'Diproses',
                        'shipped' => 'Dikirim',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                        default => $state,
                    }),

                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated(false)
            ->emptyStateHeading('Belum ada pesanan')
            ->emptyStateDescription('Pesanan dari pelanggan akan muncul di sini.')
            ->emptyStateIcon('heroicon-o-shopping-bag');
    }
}

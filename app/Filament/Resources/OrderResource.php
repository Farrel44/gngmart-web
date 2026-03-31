<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

/**
 * Resource Filament untuk mengelola pesanan dari sisi Admin.
 *
 * Fitur:
 * - Melihat daftar semua pesanan
 * - Verifikasi pembayaran (update payment_status)
 * - Update status pesanan (dengan state machine)
 * - Lihat detail pesanan dan bukti bayar
 */
class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Penjualan';

    protected static ?string $navigationLabel = 'Pesanan';

    protected static ?int $navigationSort = 1;

    /**
     * Badge di navigasi menampilkan jumlah order pending untuk alert cepat.
     */
    public static function getNavigationBadge(): ?string
    {
        $pendingCount = Cache::remember('pending_orders_count', 60, fn () => static::getModel()::where('order_status', Order::STATUS_PENDING)->count()
        );

        return $pendingCount > 0 ? (string) $pendingCount : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    /**
     * Form untuk edit order.
     * Admin hanya bisa mengubah status, tidak bisa edit data lainnya.
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pesanan')
                    ->schema([
                        Forms\Components\TextInput::make('id')
                            ->label('No. Pesanan')
                            ->disabled(),

                        Forms\Components\TextInput::make('user.name')
                            ->label('Pembeli')
                            ->disabled(),

                        Forms\Components\TextInput::make('total_price')
                            ->label('Total')
                            ->prefix('Rp')
                            ->disabled(),

                        Forms\Components\DateTimePicker::make('order_date')
                            ->label('Tanggal Order')
                            ->disabled(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Status Pesanan')
                    ->schema([
                        Forms\Components\Select::make('order_status')
                            ->label('Status Pesanan')
                            ->options(function ($record) {
                                if (! $record) {
                                    return Order::getStatusLabels();
                                }

                                // Hanya tampilkan transisi yang valid berdasarkan state machine
                                $allowedTransitions = $record->getAvailableTransitions();
                                $labels = [];

                                // Selalu sertakan status saat ini
                                $labels[$record->order_status] = Order::getStatusLabels()[$record->order_status];

                                // Tambahkan transisi yang diizinkan
                                foreach ($allowedTransitions as $status) {
                                    $labels[$status] = Order::getStatusLabels()[$status];
                                }

                                return $labels;
                            })
                            ->required()
                            ->native(false),
                    ]),

                Forms\Components\Section::make('Alamat Pengiriman')
                    ->schema([
                        Forms\Components\Textarea::make('address_shipment')
                            ->label('Alamat')
                            ->disabled()
                            ->rows(3),
                    ]),
            ]);
    }

    /**
     * Tabel daftar pesanan dengan filter dan aksi.
     */
    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['user', 'payment']))
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('No. Pesanan')
                    ->searchable()
                    ->sortable()
                    ->prefix('#'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pembeli')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_price')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('order_status')
                    ->label('Status Pesanan')
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

                Tables\Columns\TextColumn::make('payment.payment_status')
                    ->label('Status Bayar')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => $state
                        ? (Payment::getStatusLabels()[$state] ?? $state)
                        : 'Belum Bayar')
                    ->color(fn (?string $state): string => match ($state) {
                        Payment::STATUS_PENDING => 'warning',
                        Payment::STATUS_SUCCESS => 'success',
                        Payment::STATUS_FAILED => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('payment.payment_method')
                    ->label('Metode')
                    ->formatStateUsing(fn (?string $state): string => $state
                        ? (Payment::getMethodLabels()[$state] ?? $state)
                        : '-'),

                Tables\Columns\TextColumn::make('order_date')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('order_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('order_status')
                    ->label('Status Pesanan')
                    ->options(Order::getStatusLabels()),

                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Status Bayar')
                    ->options(array_merge(
                        ['' => 'Belum Bayar'],
                        Payment::getStatusLabels()
                    ))
                    ->query(function (Builder $query, array $data) {
                        if ($data['value'] === '') {
                            return $query->whereDoesntHave('payment');
                        }

                        return $query->whereHas('payment', function ($q) use ($data) {
                            $q->where('payment_status', $data['value']);
                        });
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                // Action khusus: Verifikasi Pembayaran
                Tables\Actions\Action::make('verify_payment')
                    ->label('Verifikasi Bayar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Verifikasi Pembayaran')
                    ->modalDescription('Yakin ingin memverifikasi pembayaran ini? Status order akan otomatis berubah menjadi "Sudah Dibayar".')
                    ->modalSubmitActionLabel('Ya, Verifikasi')
                    // Tampilkan jika ada payment pending (non-midtrans, karena midtrans auto-verify via callback)
                    ->visible(fn (Order $record): bool => $record->payment?->payment_status === Payment::STATUS_PENDING
                        && $record->payment?->payment_method !== Payment::METHOD_MIDTRANS)
                    ->action(function (Order $record): void {
                        // Update payment status ke success
                        $record->payment->update([
                            'payment_status' => Payment::STATUS_SUCCESS,
                        ]);

                        // Transisi order status ke paid
                        $record->transitionTo(Order::STATUS_PAID);
                    }),

                // Action khusus: Proses Pesanan
                Tables\Actions\Action::make('process_order')
                    ->label('Proses')
                    ->icon('heroicon-o-cog')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->modalHeading('Proses Pesanan')
                    ->modalDescription('Yakin ingin memproses pesanan ini? Pastikan pembayaran sudah diverifikasi.')
                    ->visible(fn (Order $record): bool => $record->canTransitionTo(Order::STATUS_PROCESSING))
                    ->action(fn (Order $record) => $record->transitionTo(Order::STATUS_PROCESSING)),

                // Action khusus: Kirim Pesanan
                Tables\Actions\Action::make('ship_order')
                    ->label('Kirim')
                    ->icon('heroicon-o-truck')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Kirim Pesanan')
                    ->modalDescription('Yakin pesanan sudah dikirim?')
                    ->visible(fn (Order $record): bool => $record->canTransitionTo(Order::STATUS_SHIPPED))
                    ->action(fn (Order $record) => $record->transitionTo(Order::STATUS_SHIPPED)),

                // Action khusus: Selesaikan Pesanan
                Tables\Actions\Action::make('complete_order')
                    ->label('Selesai')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Selesaikan Pesanan')
                    ->modalDescription('Yakin pesanan sudah diterima oleh pembeli?')
                    ->visible(fn (Order $record): bool => $record->canTransitionTo(Order::STATUS_COMPLETED))
                    ->action(fn (Order $record) => $record->transitionTo(Order::STATUS_COMPLETED)),
            ])
            ->bulkActions([
                // Tidak ada bulk action untuk order demi keamanan
            ]);
    }

    /**
     * Infolist untuk view detail pesanan.
     */
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informasi Pesanan')
                    ->schema([
                        Infolists\Components\TextEntry::make('id')
                            ->label('No. Pesanan')
                            ->prefix('#'),

                        Infolists\Components\TextEntry::make('user.name')
                            ->label('Pembeli'),

                        Infolists\Components\TextEntry::make('user.email')
                            ->label('Email'),

                        Infolists\Components\TextEntry::make('order_date')
                            ->label('Tanggal Order')
                            ->dateTime('d M Y, H:i'),

                        Infolists\Components\TextEntry::make('order_status')
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

                        Infolists\Components\TextEntry::make('total_price')
                            ->label('Total')
                            ->money('IDR'),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Alamat Pengiriman')
                    ->schema([
                        Infolists\Components\TextEntry::make('address_shipment')
                            ->label('')
                            ->columnSpanFull(),
                    ]),

                Infolists\Components\Section::make('Pembayaran')
                    ->schema([
                        Infolists\Components\TextEntry::make('payment.payment_method')
                            ->label('Metode')
                            ->formatStateUsing(fn (?string $state): string => $state
                                ? (Payment::getMethodLabels()[$state] ?? $state)
                                : 'Belum ada'),

                        Infolists\Components\TextEntry::make('payment.payment_status')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(fn (?string $state): string => $state
                                ? (Payment::getStatusLabels()[$state] ?? $state)
                                : 'Belum Bayar')
                            ->color(fn (?string $state): string => match ($state) {
                                Payment::STATUS_PENDING => 'warning',
                                Payment::STATUS_SUCCESS => 'success',
                                Payment::STATUS_FAILED => 'danger',
                                default => 'gray',
                            }),

                        Infolists\Components\TextEntry::make('payment.payment_date')
                            ->label('Tanggal Bayar')
                            ->dateTime('d M Y, H:i'),

                        Infolists\Components\TextEntry::make('payment.midtrans_transaction_id')
                            ->label('ID Transaksi Midtrans')
                            ->visible(fn ($record) => $record->payment?->midtrans_transaction_id !== null),

                        Infolists\Components\ImageEntry::make('payment.payment_proof')
                            ->label('Bukti Pembayaran')
                            ->disk('public')
                            ->visibility('public')
                            ->columnSpanFull()
                            ->visible(fn ($record) => $record->payment?->payment_proof !== null),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Daftar Barang')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('items')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('product.name')
                                    ->label('Produk'),

                                Infolists\Components\TextEntry::make('quantity')
                                    ->label('Qty'),

                                Infolists\Components\TextEntry::make('price')
                                    ->label('Harga')
                                    ->money('IDR'),

                                Infolists\Components\TextEntry::make('subtotal')
                                    ->label('Subtotal')
                                    ->state(fn ($record) => $record->getSubtotal())
                                    ->money('IDR'),
                            ])
                            ->columns(4),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}

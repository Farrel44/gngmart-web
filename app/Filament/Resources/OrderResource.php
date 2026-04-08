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

    protected static ?string $modelLabel = 'Pesanan';

    protected static ?string $pluralModelLabel = 'Pesanan';

    protected static ?string $slug = 'orders';

    protected static ?int $navigationSort = 1;

    /**
     * Badge di navigasi — realtime count tanpa cache agar selalu akurat.
     */
    public static function getNavigationBadge(): ?string
    {
        $pendingCount = static::getModel()::where('order_status', Order::STATUS_PENDING)->count();

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
            ->modifyQueryUsing(fn ($query) => $query->with(['user', 'payment', 'items']))
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
                    ->options([
                        'no_payment' => 'Belum Bayar',
                        ...Payment::getStatusLabels(),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (blank($data['value'])) {
                            return $query;
                        }

                        if ($data['value'] === 'no_payment') {
                            return $query->whereDoesntHave('payment');
                        }

                        return $query->whereHas('payment', function ($q) use ($data) {
                            $q->where('payment_status', $data['value']);
                        });
                    }),
            ])
            ->poll('15s')
            ->emptyStateHeading('Belum ada pesanan')
            ->emptyStateDescription('Pesanan baru dari pelanggan akan muncul di sini secara otomatis.')
            ->emptyStateIcon('heroicon-o-shopping-bag')
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lihat'),
                Tables\Actions\EditAction::make()
                    ->label('Ubah'),

                // Action Group: Workflow actions
                Tables\Actions\ActionGroup::make([
                    // Action 1: Verifikasi Pembayaran (PENDING → PROCESSING)
                    Tables\Actions\Action::make('verify_payment_pending')
                        ->label('Verifikasi Bayar')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Verifikasi Pembayaran')
                        ->modalDescription('Verifikasi pembayaran pembeli? Status order akan berubah menjadi "Diproses".')
                        ->modalSubmitActionLabel('Ya, Verifikasi')
                        ->visible(fn (Order $record): bool => 
                            $record->order_status === Order::STATUS_PENDING
                            && $record->payment?->payment_status === Payment::STATUS_PENDING
                            && $record->payment?->payment_method !== Payment::METHOD_MIDTRANS)
                        ->action(function (Order $record): void {
                            $record->transitionTo(Order::STATUS_PROCESSING);
                        })
                        ->after(function () {
                            \Filament\Notifications\Notification::make()
                                ->title('Pembayaran Terverifikasi')
                                ->body('Status pesanan berubah menjadi "Diproses".')
                                ->success()
                                ->send();
                        }),

                    // Action 2: Kirim Pesanan (PROCESSING → SHIPPED)
                    Tables\Actions\Action::make('ship_order')
                        ->label('Kirim')
                        ->icon('heroicon-o-truck')
                        ->color('info')
                        ->requiresConfirmation()
                        ->modalHeading('Kirim Pesanan')
                        ->modalDescription('Yakin pesanan sudah dikirim?')
                        ->visible(fn (Order $record): bool => $record->order_status === Order::STATUS_PROCESSING)
                        ->action(fn (Order $record) => $record->transitionTo(Order::STATUS_SHIPPED))
                        ->after(function () {
                            \Filament\Notifications\Notification::make()
                                ->title('Pesanan Dikirim')
                                ->body('Status pesanan berubah menjadi "Dikirim".')
                                ->success()
                                ->send();
                        }),

                    // Action 3: Verifikasi Pembayaran Tunai (SHIPPED → PAID)
                    Tables\Actions\Action::make('confirm_cod_payment')
                        ->label('Verifikasi Bayar')
                        ->icon('heroicon-o-banknotes')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Verifikasi Pembayaran Tunai')
                        ->modalDescription('Konfirmasi pembayaran tunai sudah diterima dari pembeli?')
                        ->modalSubmitActionLabel('Ya, Sudah Diterima')
                        ->visible(fn (Order $record): bool => 
                            $record->order_status === Order::STATUS_SHIPPED
                            && $record->payment?->payment_status === Payment::STATUS_PENDING
                            && $record->payment?->payment_method !== Payment::METHOD_MIDTRANS)
                        ->action(function (Order $record): void {
                            $record->payment->update(['payment_status' => Payment::STATUS_SUCCESS]);
                            $record->transitionTo(Order::STATUS_PAID);
                        })
                        ->after(function () {
                            \Filament\Notifications\Notification::make()
                                ->title('Pembayaran Dikonfirmasi')
                                ->body('Status pesanan berubah menjadi "Sudah Dibayar".')
                                ->success()
                                ->send();
                        }),

                    // Action 4: Pesanan Selesai (PAID → COMPLETED)
                    Tables\Actions\Action::make('complete_order')
                        ->label('Selesai')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Selesaikan Pesanan')
                        ->modalDescription('Yakin pesanan sudah diterima oleh pembeli?')
                        ->visible(fn (Order $record): bool => $record->order_status === Order::STATUS_PAID)
                        ->action(fn (Order $record) => $record->transitionTo(Order::STATUS_COMPLETED))
                        ->after(function () {
                            \Filament\Notifications\Notification::make()
                                ->title('Pesanan Selesai')
                                ->body('Status pesanan berubah menjadi "Selesai".')
                                ->success()
                                ->send();
                        }),

                    // Action: Hapus Pesanan (hanya untuk COMPLETED atau CANCELLED)
                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->visible(fn (Order $record): bool => 
                            in_array($record->order_status, [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED]))
                        ->modalHeading('Hapus Pesanan')
                        ->modalDescription('Pesanan akan dihapus secara permanen. Tindakan ini tidak dapat dibatalkan.')
                        ->modalSubmitActionLabel('Ya, Hapus'),
                ])
                    ->label('Aksi')
                    ->icon('heroicon-o-ellipsis-horizontal'),
            ])
            ->bulkActions([
                // Bulk Delete: Untuk menghapus multiple pesanan COMPLETED atau CANCELLED
                Tables\Actions\BulkAction::make('bulk_delete')
                    ->label('Hapus Pesanan')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Pesanan Terpilih')
                    ->modalDescription('Semua pesanan terpilih akan dihapus secara permanen. Tindakan ini tidak dapat dibatalkan.')
                    ->modalSubmitActionLabel('Ya, Hapus Semua')
                    ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                        // Hapus hanya pesanan dengan status COMPLETED atau CANCELLED
                        $deletable = $records->filter(fn (Order $order) => 
                            in_array($order->order_status, [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED]));
                        
                        $deletable->each->delete();
                        
                        $deletedCount = $deletable->count();
                        $skippedCount = $records->count() - $deletedCount;
                        
                        if ($deletedCount > 0) {
                            \Filament\Notifications\Notification::make()
                                ->title('Pesanan Dihapus')
                                ->body($deletedCount . ' pesanan berhasil dihapus.' . ($skippedCount > 0 ? " $skippedCount pesanan tidak bisa dihapus karena masih aktif." : ''))
                                ->success()
                                ->send();
                        } elseif ($skippedCount > 0) {
                            \Filament\Notifications\Notification::make()
                                ->title('Tidak Ada Pesanan yang Dihapus')
                                ->body('Hanya pesanan dengan status "Selesai" atau "Dibatalkan" yang bisa dihapus.')
                                ->warning()
                                ->send();
                        }
                    }),
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
                                    ->label('Jumlah'),

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

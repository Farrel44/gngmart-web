<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PromotionResource\Pages;
use App\Models\Promotion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Filament Resource: Promotions
 *
 * Mengelola promosi/diskon yang bisa diterapkan ke produk atau kategori.
 * Admin bisa mengatur persentase diskon, periode aktif,
 * dan memilih produk/kategori yang tercakup.
 */
class PromotionResource extends Resource
{
    protected static ?string $model = Promotion::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Promo';

    protected static ?string $navigationLabel = 'Promosi';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Promosi')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Promo')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ramadhan Sale 2026'),

                        Forms\Components\TextInput::make('discount_percentage')
                            ->label('Diskon (%)')
                            ->required()
                            ->numeric()
                            ->suffix('%')
                            ->minValue(1)
                            ->maxValue(100)
                            ->helperText('Persentase diskon dari harga asli (1-100)'),

                        Forms\Components\DatePicker::make('start_date')
                            ->label('Mulai')
                            ->required()
                            ->native(false),

                        Forms\Components\DatePicker::make('end_date')
                            ->label('Berakhir')
                            ->required()
                            ->native(false)
                            ->afterOrEqual('start_date'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->helperText('Nonaktifkan untuk menjeda promosi tanpa menghapus'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Target Promosi')
                    ->description('Pilih produk dan/atau kategori yang tercakup dalam promosi ini')
                    ->schema([
                        Forms\Components\Select::make('products')
                            ->label('Produk')
                            ->relationship('products', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->helperText('Kosongkan jika promo hanya berlaku untuk kategori'),

                        Forms\Components\Select::make('categories')
                            ->label('Kategori')
                            ->relationship('categories', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->helperText('Semua produk dalam kategori terpilih mendapat diskon'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Promo')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('discount_percentage')
                    ->label('Diskon')
                    ->suffix('%')
                    ->sortable()
                    ->color('success'),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('Mulai')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->label('Berakhir')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),

                // Jumlah produk yang terkena promo langsung
                Tables\Columns\TextColumn::make('products_count')
                    ->label('Produk')
                    ->counts('products')
                    ->sortable(),

                // Jumlah kategori yang terkena promo
                Tables\Columns\TextColumn::make('categories_count')
                    ->label('Kategori')
                    ->counts('categories')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPromotions::route('/'),
            'create' => Pages\CreatePromotion::route('/create'),
            'edit' => Pages\EditPromotion::route('/{record}/edit'),
        ];
    }
}

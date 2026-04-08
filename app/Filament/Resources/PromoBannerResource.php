<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PromoBannerResource\Pages;
use App\Models\PromoBanner;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Filament Resource: Promo Banners
 *
 * Mengelola promo banner section (bagian hijau bawah di homepage).
 * Terpisah dari carousel, admin bisa membuat/edit banner promo spesifik.
 */
class PromoBannerResource extends Resource
{
    protected static ?string $model = PromoBanner::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Konten';

    protected static ?string $navigationLabel = 'Promo Banner';

    protected static ?string $modelLabel = 'Promo Banner';

    protected static ?string $pluralModelLabel = 'Promo Banner';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Konten Promo')
                    ->schema([
                        Forms\Components\TextInput::make('subtitle')
                            ->label('Label Promo')
                            ->maxLength(100)
                            ->helperText('Contoh: PROMO MINGGU, PENAWARAN SPESIAL')
                            ->required(),

                        Forms\Components\TextInput::make('title')
                            ->label('Judul Promo')
                            ->maxLength(255)
                            ->helperText('Judul utama promo')
                            ->required(),

                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->helperText('Deskripsi singkat promo'),

                        Forms\Components\FileUpload::make('image_path')
                            ->label('Gambar Promo')
                            ->image()
                            ->directory('promo-banners')
                            ->disk('public')
                            ->imageResizeMode('cover')
                            ->helperText('Gambar akan ditampilkan di sebelah kanan'),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Tombol & Warna')
                    ->schema([
                        Forms\Components\TextInput::make('button_text')
                            ->label('Teks Tombol')
                            ->maxLength(100)
                            ->placeholder('Lihat Promo'),

                        Forms\Components\TextInput::make('button_url')
                            ->label('URL Tombol')
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://...'),

                        Forms\Components\Select::make('background_color')
                            ->label('Warna Background')
                            ->options([
                                'from-green-300 via-green-400 to-green-500' => 'Hijau',
                                'from-blue-300 via-blue-400 to-blue-500' => 'Biru',
                                'from-red-300 via-red-400 to-red-500' => 'Merah',
                                'from-yellow-300 via-yellow-400 to-yellow-500' => 'Kuning',
                                'from-purple-300 via-purple-400 to-purple-500' => 'Ungu',
                                'from-pink-300 via-pink-400 to-pink-500' => 'Pink',
                            ])
                            ->default('from-green-300 via-green-400 to-green-500'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Status')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->helperText('Banner tidak aktif tidak akan tampil di homepage'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('image_path')
                    ->label('Gambar')
                    ->formatStateUsing(fn ($state) => $state ? '<img src="' . asset('storage/' . $state) . '" class="h-16 w-auto rounded object-cover" />' : 'No image')
                    ->html(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('button_text')
                    ->label('Tombol')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('background_color')
                    ->label('Warna')
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'from-green-300 via-green-400 to-green-500' => 'Hijau',
                        'from-blue-300 via-blue-400 to-blue-500' => 'Biru',
                        'from-red-300 via-red-400 to-red-500' => 'Merah',
                        'from-yellow-300 via-yellow-400 to-yellow-500' => 'Kuning',
                        'from-purple-300 via-purple-400 to-purple-500' => 'Ungu',
                        'from-pink-300 via-pink-400 to-pink-500' => 'Pink',
                        default => $state,
                    }),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
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
            'index' => Pages\ListPromoBanners::route('/'),
            'create' => Pages\CreatePromoBanner::route('/create'),
            'edit' => Pages\EditPromoBanner::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources\DiscountCodes;

use App\Enums\DiscountType;
use App\Filament\Resources\DiscountCodes\Pages\ListDiscountCodes;
use App\Filament\Resources\DiscountCodes\Pages\CreateDiscountCode;
use App\Filament\Resources\DiscountCodes\Pages\EditDiscountCode;
use App\Models\DiscountCode;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Table;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use BackedEnum;
use Filament\Schemas\Components\Utilities\Get;

class DiscountCodeResource extends Resource
{
    protected static ?string $model           = DiscountCode::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-tag';
    // protected static ?string $navigationGroup = 'Shop';
    public static function getNavigationGroup(): ?string
    {
        return 'Shop';
    }
    protected static ?int    $navigationSort  = 3;
    protected static ?string $label           = 'Discount code';
    protected static ?string $pluralLabel     = 'Discount codes';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Code details')
                ->columns(2)
                ->schema([
                    TextInput::make('code')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->formatStateUsing(fn($state) => strtoupper((string) $state))
                        ->dehydrateStateUsing(fn($state) => strtoupper((string) $state))
                        ->placeholder('e.g. WELCOME20'),

                    TextInput::make('description')
                        ->placeholder('Internal label e.g. "New customer discount"'),

                    Select::make('type')
                        ->options(
                            collect(DiscountType::cases())
                                ->mapWithKeys(fn($c) => [$c->value => $c->label()])
                                ->toArray()
                        )
                        ->required()
                        ->live(),

                    TextInput::make('value')
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->suffix(fn(Get $get) => $get('type') === 'percent' ? '%' : 'KES'),
                ]),

            Section::make('Restrictions')
                ->columns(2)
                ->schema([
                    TextInput::make('minimum_order_amount')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->prefix('KES')
                        ->label('Minimum order amount'),

                    TextInput::make('per_customer_limit')
                        ->numeric()
                        ->default(1)
                        ->minValue(1)
                        ->label('Uses per customer'),

                    TextInput::make('uses_limit')
                        ->numeric()
                        ->minValue(1)
                        ->label('Total uses limit')
                        ->helperText('Leave empty for unlimited.'),

                    DateTimePicker::make('starts_at'),
                    DateTimePicker::make('expires_at'),

                    Toggle::make('is_active')
                        ->default(true),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->searchable()
                    ->copyable()
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                TextColumn::make('type')
                    ->formatStateUsing(
                        fn($state) => $state instanceof DiscountType
                            ? $state->label()
                            : ucfirst((string) $state)
                    ),

                TextColumn::make('value')
                    ->formatStateUsing(
                        fn($state, $record) =>
                        $record->type === DiscountType::Percent
                            ? "{$state}%"
                            : 'KES ' . number_format((float)$state, 0)
                    ),

                TextColumn::make('uses_count')
                    ->label('Uses')
                    ->formatStateUsing(
                        fn($state, $record) =>
                        $record->uses_limit
                            ? "{$state} / {$record->uses_limit}"
                            : (string) $state
                    ),

                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),

                TextColumn::make('expires_at')
                    ->label('Expires')
                    ->formatStateUsing(
                        fn($state) =>
                        $state ? \Carbon\Carbon::parse($state)->format('d M Y') : 'Never'
                    )
                    ->color(
                        fn($state) =>
                        blank($state)
                            ? 'success'
                            : (\Carbon\Carbon::parse($state)->isPast() ? 'danger' : 'success')
                    ),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Active'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListDiscountCodes::route('/'),
            'create' => CreateDiscountCode::route('/create'),
            'edit'   => EditDiscountCode::route('/{record}/edit'),
        ];
    }
}

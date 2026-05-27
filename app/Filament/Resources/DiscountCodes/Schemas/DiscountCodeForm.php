<?php

namespace App\Filament\Resources\DiscountCodes\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DiscountCodeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->required(),
                TextInput::make('description'),
                Select::make('type')
                    ->options(['percent' => 'Percent', 'fixed' => 'Fixed'])
                    ->required(),
                TextInput::make('value')
                    ->required()
                    ->numeric(),
                TextInput::make('minimum_order_amount')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('uses_limit')
                    ->numeric(),
                TextInput::make('uses_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('per_customer_limit')
                    ->required()
                    ->numeric()
                    ->default(1),
                Toggle::make('is_active')
                    ->required(),
                DateTimePicker::make('starts_at'),
                DateTimePicker::make('expires_at'),
            ]);
    }
}

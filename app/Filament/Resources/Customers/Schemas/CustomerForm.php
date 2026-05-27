<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')
                    ->numeric(),
                TextInput::make('first_name')
                    ->required(),
                TextInput::make('last_name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('phone')
                    ->tel(),
                TextInput::make('whatsapp_number'),
                Textarea::make('notes')
                    ->columnSpanFull(),
                TextInput::make('address_line1'),
                TextInput::make('address_line2'),
                TextInput::make('city'),
                TextInput::make('county'),
                TextInput::make('postal_code'),
                TextInput::make('country')
                    ->required()
                    ->default('KE'),
                Select::make('source')
                    ->options([
            'website' => 'Website',
            'whatsapp' => 'Whatsapp',
            'referral' => 'Referral',
            'instagram' => 'Instagram',
            'other' => 'Other',
        ])
                    ->default('website')
                    ->required(),
                Toggle::make('marketing_opt_in')
                    ->required(),
                DateTimePicker::make('last_ordered_at'),
            ]);
    }
}

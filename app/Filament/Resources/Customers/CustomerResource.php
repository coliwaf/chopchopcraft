<?php

namespace App\Filament\Resources\Customers;

use App\Filament\Resources\Customers\Pages\CreateCustomer;
use App\Filament\Resources\Customers\Pages\EditCustomer;
use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Filament\Resources\Customers\Pages\ViewCustomer;
use App\Filament\Resources\Customers\RelationManagers\OrdersRelationManager;
use App\Models\Customer;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CustomerResource extends Resource
{
    protected static ?string $model           = Customer::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::Users;
    public static function getNavigationGroup(): ?string
    {
        return 'CRM';
    }
    protected static ?int    $navigationSort  = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Contact info')
                ->columns(2)
                ->schema([
                    TextInput::make('first_name')->required(),
                    TextInput::make('last_name')->required(),
                    TextInput::make('email')->email()->required()->unique(ignoreRecord: true),
                    TextInput::make('phone')->tel()->placeholder('+254712345678'),
                    TextInput::make('whatsapp_number')->tel()->label('WhatsApp (if different)'),
                    Select::make('source')->options([
                        'website'   => 'Website',   'whatsapp'  => 'WhatsApp',
                        'referral'  => 'Referral',  'instagram' => 'Instagram',
                        'other'     => 'Other',
                    ]),
                ]),

            Section::make('Shipping address')
                ->columns(2)
                ->collapsed()
                ->schema([
                    TextInput::make('address_line1')->label('Address 1'),
                    TextInput::make('address_line2')->label('Address 2'),
                    TextInput::make('city'),
                    TextInput::make('county')->label('County / Region'),
                    TextInput::make('postal_code'),
                ]),

            Section::make('CRM notes')
                ->schema([
                    Textarea::make('notes')->rows(4)->label('Internal notes'),
                    Toggle::make('marketing_opt_in')->label('Marketing opt-in'),
                ]),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Contact')
                ->columns(3)
                ->schema([
                    TextEntry::make('full_name')->label('Name')->weight('bold'),
                    TextEntry::make('email'),
                    TextEntry::make('phone'),
                    TextEntry::make('whatsapp_number')->label('WhatsApp')->default('Same as phone'),
                    TextEntry::make('source')->badge(),
                    IconEntry::make('marketing_opt_in')->boolean()->label('Marketing'),
                ]),

            Section::make('Purchase summary')
                ->columns(3)
                ->schema([
                    TextEntry::make('orders_count')
                        ->label('Total orders')
                        ->getStateUsing(fn (Customer $r) => $r->orders()->count()),
                    TextEntry::make('total_spent')
                        ->label('Total spent')
                        ->getStateUsing(fn (Customer $r) => 'KES ' . number_format($r->total_spent, 2)),
                    TextEntry::make('last_ordered_at')->label('Last order')->dateTime('d M Y'),
                ]),

            Section::make('Notes')
                ->schema([TextEntry::make('notes')->default('No notes.')]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')
                    ->label('Name')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable()->weight('medium'),
                TextColumn::make('email')->searchable()->copyable(),
                TextColumn::make('phone')->searchable(),
                TextColumn::make('orders_count')->counts('orders')->label('Orders')->sortable(),
                TextColumn::make('total_spent')
                    ->label('Total spent')
                    ->getStateUsing(fn (Customer $r) => $r->total_spent)
                    ->money('KES')->sortable(),
                TextColumn::make('source')->badge()->color('gray'),
                TextColumn::make('last_ordered_at')->label('Last order')->since()->sortable(),
            ])
            ->filters([
                SelectFilter::make('source')->options([
                    'website' => 'Website', 'whatsapp' => 'WhatsApp',
                    'referral'=> 'Referral','instagram'=> 'Instagram',
                ]),
                Filter::make('with_orders')
                    ->query(fn ($q) => $q->has('orders'))->label('Has orders'),
                Filter::make('marketing_opt_in')
                    ->query(fn ($q) => $q->where('marketing_opt_in', true))->label('Marketing opt-in'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelationManagers(): array
    {
        return [OrdersRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListCustomers::route('/'),
            'create' => CreateCustomer::route('/create'),
            'view'   => ViewCustomer::route('/{record}'),
            'edit'   => EditCustomer::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources\Products;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Products\RelationManagers\VariantsRelationManager;
use App\Models\Product;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::Squares2x2;
    public static function getNavigationGroup(): ?string
    {
        return 'Shop';
    }
    protected static ?int    $navigationSort  = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Product details')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(
                            fn($state, callable $set) =>
                            $set('slug', Str::slug($state))
                        ),

                    TextInput::make('slug')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),

                    TextInput::make('base_price')
                        ->required()
                        ->numeric()
                        ->prefix('KES'),

                    Select::make('wood_type')
                        ->options([
                            'Acacia'   => 'Acacia',
                            'Walnut'   => 'Walnut',
                            'Teak'     => 'Teak',
                            'Bamboo'   => 'Bamboo',
                            'Olive'    => 'Olive',
                            'Mahogany' => 'Mahogany',
                            'Mixed'    => 'Mixed',
                        ])
                        ->searchable(),

                    Select::make('finish')
                        ->options([
                            'Natural' => 'Natural',
                            'Oiled'   => 'Oiled',
                            'Waxed'   => 'Waxed',
                            'Painted' => 'Painted',
                        ]),
                ]),

            Section::make('Description')
                ->schema([
                    Textarea::make('description')
                        ->rows(3)
                        ->maxLength(500)
                        ->helperText('Short description shown on product cards.'),

                    RichEditor::make('long_description')
                        ->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList', 'link'])
                        ->helperText('Full product page description.'),
                ]),

            Section::make('Media')
                ->schema([
                    /* SpatieMediaLibraryFileUpload::make('images')
                        ->collection('images')
                        ->multiple()
                        ->reorderable()
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->maxFiles(8)
                        ->image(), */
                    SpatieMediaLibraryFileUpload::make('images')
                        ->collection('images')
                        ->multiple()
                        ->reorderable()
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->maxFiles(8)
                        ->conversion('thumb'),   // ← show thumb preview in the upload widget
                ]),

            Section::make('Dimensions & care')
                ->columns(2)
                ->collapsed()
                ->schema([
                    KeyValue::make('dimensions')
                        ->keyLabel('Dimension')
                        ->valueLabel('Value')
                        ->columnSpanFull(),

                    Repeater::make('care_instructions')
                        ->simple(
                            TextInput::make('instruction')->required()
                        )
                        ->addActionLabel('Add care tip')
                        ->columnSpanFull(),
                ]),

            Section::make('Visibility')
                ->columns(3)
                ->schema([
                    Toggle::make('is_active')->label('Active')->default(true),
                    Toggle::make('is_featured')->label('Featured on homepage'),
                    TextInput::make('sort_order')->numeric()->default(0),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                /* SpatieMediaLibraryImageColumn::make('images')
                    ->collection('images')
                    ->label('')
                    ->width(150)->imageHeight(150), */
                SpatieMediaLibraryImageColumn::make('images')
                    ->collection('images')
                    ->conversion('thumb')    // ← explicitly use the thumb conversion
                    ->label('')
                    ->width(60)->height(60),

                TextColumn::make('name')
                    ->searchable()->sortable()->weight('bold'),

                TextColumn::make('wood_type')
                    ->badge()->color('gray'),

                TextColumn::make('base_price')
                    ->money('KES')->sortable(),

                TextColumn::make('total_stock')
                    ->label('Stock')
                    ->getStateUsing(fn(Product $record) => $record->variants->sum('stock_qty'))
                    ->badge()
                    ->color(fn($state) => match (true) {
                        $state === 0 => 'danger',
                        $state <= 5  => 'warning',
                        default      => 'success',
                    }),

                TextColumn::make('variants_count')
                    ->counts('variants')
                    ->label('Variants'),

                IconColumn::make('is_active')->boolean()->label('Active'),
                IconColumn::make('is_featured')->boolean()->label('Featured'),

                TextColumn::make('created_at')
                    ->dateTime('d M Y')->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Active'),
                TernaryFilter::make('is_featured')->label('Featured'),
                SelectFilter::make('wood_type')->options([
                    'Acacia' => 'Acacia',
                    'Walnut' => 'Walnut',
                    'Teak'   => 'Teak',
                    'Bamboo' => 'Bamboo',
                    'Olive'  => 'Olive',
                    'Mahogany' => 'Mahogany',
                ]),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order');
    }

    public static function getRelationManagers(): array
    {
        return [VariantsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit'   => EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}

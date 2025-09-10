<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Filament\Resources\PostResource\RelationManagers;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
                Forms\Components\TextInput::make('title')->required()->maxLength(255),
                Forms\Components\TextInput::make('slug')->unique(ignoreRecord: true)->helperText('Auto-filled, can edit'),
                Forms\Components\Select::make('category_id')
                    ->relationship('category','name')->searchable()->preload(),
                Forms\Components\Select::make('tags')
                    ->multiple()->relationship('tags','name')->preload(),
                Forms\Components\Select::make('status')
                    ->options(['draft'=>'Draft','published'=>'Published'])->default('draft'),
                Forms\Components\DateTimePicker::make('published_at')->seconds(false),
                Forms\Components\Textarea::make('content')->required()->columnSpanFull(),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
          return $table
        ->columns([
            Tables\Columns\TextColumn::make('title')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('category.name')->label('Category'),
            Tables\Columns\BadgeColumn::make('status')->colors([
                'warning' => 'draft',
                'success' => 'published',
            ]),
            Tables\Columns\TextColumn::make('published_at')->dateTime(),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\DeleteBulkAction::make(),
        ]);
    }
    // auto-assign the current user when creating from the admin
    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        if (empty($data['slug'])) {
            $data['slug'] = \Illuminate\Support\Str::slug($data['title']).'-'.\Illuminate\Support\Str::random(6);
        }
        return $data;
    }

    // optional: show only my posts in the list
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', auth()->id());
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
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}

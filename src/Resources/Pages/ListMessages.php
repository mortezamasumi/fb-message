<?php

namespace Mortezamasumi\FbMessage\Resources\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Mortezamasumi\FbMessage\Enums\MessageFolder;
use Mortezamasumi\FbMessage\Models\FbMessage;
use Mortezamasumi\FbMessage\Resources\FbMessageResource;
use Mortezamasumi\FbPersian\Facades\FbPersian;

class ListMessages extends ListRecords
{
    protected static string $resource = FbMessageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return collect(MessageFolder::cases())
            ->mapWithKeys(function ($folder) {
                $tab = Tab::make()
                    ->label($folder->getLabel())
                    ->modifyQueryUsing(fn (Builder $query) => $query->whereRelation($folder->value, 'id', Auth::id()))
                    ->icon($folder->getIcon());

                if ($folder === MessageFolder::INBOX) {
                    $tab->badge(FbPersian::digit(FbMessage::whereRelation('unread', 'id', Auth::id())->count()));
                }

                return [$folder->value => $tab];
            })
            ->toArray();
    }
}

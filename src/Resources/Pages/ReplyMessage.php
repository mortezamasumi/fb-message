<?php

namespace Mortezamasumi\FbMessage\Resources\Pages;

use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\HasUnsavedDataChangesAlert;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Resources\Pages\Concerns\HasRelationManagers;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Facades\FilamentView;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Mortezamasumi\FbMessage\Enums\MessageFolder;
use Mortezamasumi\FbMessage\Enums\MessageType;
use Mortezamasumi\FbMessage\Resources\FbMessageResource;
use Mortezamasumi\FbMessage\Traits\HasCreateNotificationMessage;

use function Filament\Support\is_app_url;

/**
 * @property-read Schema $form
 */
class ReplyMessage extends Page
{
    use HasRelationManagers;
    use HasUnsavedDataChangesAlert;
    use InteractsWithFormActions;
    use HasCreateNotificationMessage;
    use InteractsWithRecord;

    protected static string $resource = FbMessageResource::class;
    public ?array $data = [];
    public $original_record;
    public ?string $previousUrl = null;

    public function getTitle(): string|Htmlable
    {
        return __('fb-message::fb-message.reply.title');
    }

    public function getBreadcrumb(): string
    {
        return static::$breadcrumb ?? __('fb-message::fb-message.reply.breadcrumb');
    }

    public function mount(int|string $record): void
    {
        $this->original_record = $this->resolveRecord($record);

        $this->record = $this->original_record->replicate();

        $this->authorizeAccess();

        $this->callHook('beforeFill');

        $this->form->fill($this->getRecord()->attributesToArray());

        $this->callHook('afterFill');

        $this->data['to'] = $this->original_record->from->pluck('id')->toArray();

        $this->data['subject'] = Str::of(__('fb-message::fb-message.reply.subject_reply'))
            ->append(' : ')
            ->append($this->original_record->subject);

        $this->previousUrl = url()->previous();
    }

    protected function authorizeAccess(): void
    {
        abort_unless(static::getResource()::canCreate($this->getRecord()), 403);
    }

    public function save(bool $shouldRedirect = true): void
    {
        $this->authorizeAccess();

        try {
            $this->callHook('beforeValidate');

            $data = $this->form->getState(false);

            $this->callHook('afterValidate');

            $this->callHook('beforeSave');

            $this->record = $this->handleRecordCreation($data);

            $this
                ->record
                ->from()
                ->attach(
                    [
                        Auth::id() => [
                            'type' => MessageType::FROM,
                            'folder' => MessageFolder::SENT,
                        ],
                    ]
                );

            $this
                ->record
                ->to()
                ->attach(
                    $this
                        ->original_record
                        ->from
                        ->pluck('id')
                        ->mapWithKeys(fn ($item) => [$item => [
                            'type' => MessageType::TO,
                            'folder' => MessageFolder::INBOX,
                        ]])
                );

            $this->callHook('afterSave');
        } catch (Halt $exception) {
            return;
        }

        $this->rememberData();

        Notification::make()
            ->success()
            ->title($this->getCreatedNotificationMessage())
            ->send();

        if ($shouldRedirect && ($redirectUrl = $this->getRedirectUrl())) {
            $this->redirect($redirectUrl, navigate: FilamentView::hasSpaMode() && is_app_url($redirectUrl));
        }
    }

    protected function handleRecordCreation(array $data): Model
    {
        $record = new ($this->getModel())($data);

        if (
            static::getResource()::isScopedToTenant() &&
            ($tenant = Filament::getTenant())
        ) {
            return $this->associateRecordWithTenant($record, $tenant);
        }

        $record->save();

        return $record;
    }

    protected function getFormActions(): array
    {
        return [
            $this->getreplyFormAction(),
            $this->getCancelFormAction(),
        ];
    }

    protected function getreplyFormAction(): Action
    {
        return Action::make('reply')
            ->label(__('fb-message::fb-message.actions.reply'))
            ->submit('save')
            ->keyBindings(['mod+s']);
    }

    protected function getCancelFormAction(): Action
    {
        return Action::make('cancel')
            ->label(__('filament-panels::resources/pages/edit-record.form.actions.cancel.label'))
            ->url($this->previousUrl ?? static::getResource()::getUrl())
            ->color('gray');
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema
            ->columns($this->hasInlineLabels() ? 1 : 2)
            ->inlineLabel($this->hasInlineLabels())
            ->model($this->getModel())
            ->operation('reply')
            ->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return static::getResource()::form($schema);
    }

    protected function getRedirectUrl(): ?string
    {
        return $this->getResource()::getUrl('index');
    }

    public function content(Schema $schema): Schema
    {
        if ($this->hasCombinedRelationManagerTabsWithContent()) {
            return $schema
                ->components([
                    $this->getRelationManagersContentComponent(),
                ]);
        }

        return $schema
            ->components([
                $this->getFormContentComponent(),
                $this->getRelationManagersContentComponent(),
            ]);
    }

    public function getFormContentComponent(): Component
    {
        if (! $this->hasFormWrapper()) {
            return Group::make([
                EmbeddedSchema::make('form'),
                $this->getFormActionsContentComponent(),
            ]);
        }

        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler($this->getSubmitFormLivewireMethodName())
            ->footer([
                $this->getFormActionsContentComponent(),
            ]);
    }

    public function getFormActionsContentComponent(): Component
    {
        return Actions::make($this->getFormActions())
            ->alignment($this->getFormActionsAlignment())
            ->fullWidth($this->hasFullWidthFormActions())
            ->sticky($this->areFormActionsSticky());
    }

    public function hasFormWrapper(): bool
    {
        return true;
    }

    protected function getSubmitFormLivewireMethodName(): string
    {
        return 'save';
    }
}

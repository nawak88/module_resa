<?php

namespace Modules\Resa\Filament\Widgets\RessourceGanttWidget\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth as LaravelAuth;
use Illuminate\Support\HtmlString;

class SeConnecterAction
{
    public static function make(object $widget): Action
    {
        $ssoName = match (config('skeletor.reseau_de_deploiement')) {
            'sic21'  => 'POLARIS Online',
            default  => 'Mindef Connect',
        };

        $pageUrl = url()->current();

        return Action::make('seConnecter')
            ->label('Connexion')
            ->modalHeading('Connexion')
            ->modalSubmitActionLabel('Connexion locale')
            ->modalWidth('sm')
            ->schema([
                Placeholder::make('sso_block')
                    ->label('')
                    ->content(new HtmlString(
                        '<button type="button" wire:click="redirectSso" class="inline-flex items-center justify-center gap-2 w-full rounded-lg border border-primary-600 px-4 py-2.5 text-sm font-semibold text-primary-700 hover:bg-primary-50 dark:border-primary-400 dark:text-primary-400 dark:hover:bg-primary-950">'
                        . '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>'
                        . 'Se connecter via ' . e($ssoName)
                        . '</button>'
                        . '<div class="my-4 flex items-center gap-3"><div class="flex-1 border-t border-gray-200 dark:border-gray-700"></div><span class="text-xs font-medium text-gray-400">OU</span><div class="flex-1 border-t border-gray-200 dark:border-gray-700"></div></div>'
                    )),
                TextInput::make('email')
                    ->label('E-mail')
                    ->email()
                    ->required()
                    ->autofocus(),
                TextInput::make('password')
                    ->label('Mot de passe')
                    ->password()
                    ->revealable()
                    ->required(),
            ])
            ->action(function (array $data) use ($pageUrl, $widget): void {
                if (LaravelAuth::attempt(['email' => $data['email'], 'password' => $data['password']])) {
                    session()->regenerate();
                    $widget->redirect($pageUrl);
                } else {
                    Notification::make()
                        ->title('Identifiants incorrects')
                        ->body('Email ou mot de passe invalide.')
                        ->danger()
                        ->send();
                }
            });
    }
}

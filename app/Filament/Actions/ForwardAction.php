<?php

namespace App\Filament\Actions;

use Filament\Forms;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action as NotificationAction;
use App\Models\Doctor;

class ForwardAction
{
    public static function forward(string $resourceClass): Action
    {
        return Action::make('forward')
            ->label('Notify Next Actor')
            ->icon('heroicon-o-share')
            ->color('info')
            ->form([
                Forms\Components\Select::make('doctor_id')
                    ->label('Staff Member / Doctor')
                    ->options(function () {
                        return Doctor::with('user')
                            ->whereHas('user') // Only include doctors with user accounts
                            ->get()
                            ->mapWithKeys(function ($doctor) {
                                return [$doctor->id => "{$doctor->display_name}"];
                            });
                    })
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        // You could add logic here if needed
                    }),

                Forms\Components\Textarea::make('message')
                    ->label('More Descriptive Optional Message')
                    ->placeholder('Add a custom message (optional)...')
                    ->maxLength(500)
                    ->rows(4)
                    ->helperText('Max 500 characters'),
            ])
            ->action(function ($record, array $data, Action $action) use ($resourceClass) {
                try {
                    $doctor = Doctor::find($data['doctor_id']);

                    if (! $doctor?->user) {
                        throw new \Exception('Selected doctor has no user account.');
                    }

                    $resourceLabel = $resourceClass::getModelLabel();
                    $customMessage = $data['message'];

                    // Use custom message or default template
                    $notificationMessage = $customMessage
                        ?: "A {$resourceLabel} has been shared with you by " . auth()->user()->name . ".";

                    // Try to get URL - fallback to edit if view doesn't exist
                    try {
                        $url = $resourceClass::getUrl('view', ['record' => $record]);
                    } catch (\Exception $e) {
                        try {
                            $url = $resourceClass::getUrl('edit', ['record' => $record]);
                        } catch (\Exception $e) {
                            $url = null;
                        }
                    }

                    // Build actions array
                    $actions = [];
                    if ($url) {
                        $actions[] = NotificationAction::make('open')
                            ->label('View Record')
                            ->url($url)
                            ->button()
                            ->outlined();
                    }

                    // Send notification using sendToDatabase
                    Notification::make()
                        ->title("Shared {$resourceLabel}")
                        ->body($notificationMessage)
                        ->success()
                        ->icon('heroicon-o-information-circle')
                        ->actions($actions)
                        ->sendToDatabase($doctor->user);

                    // Success feedback
                    Notification::make()
                        ->title('Shared Successfully')
                        ->body("Shared with  {$doctor->display_name}")
                        ->success()
                        ->send();

                    // Close the modal
                    $action->success();
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Error')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            })
            ->modalHeading('Share with Doctor')
            ->modalSubmitActionLabel('Send')
            ->modalWidth('lg')
            ->hidden(fn($record) => !$record)
            ->disabled(fn($record) => !$record);
    }
}

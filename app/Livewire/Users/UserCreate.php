<?php

namespace App\Livewire\Users;

use App\Enums\UserType;
use App\Models\User as UserModel;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Radio;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class UserCreate extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('first_name')->required(),
                TextInput::make('last_name')->required(),
                TextInput::make('email')
                    ->email()
                    ->maxLength(255)
                    ->unique(table: UserModel::class)
                    ->required(),
                TextInput::make('password')->password()->required(),
                TextInput::make('country'),
                TextInput::make('phone'),
                Radio::make('type')
                    ->options([
                        UserType::USER->value  => 'User',
                        UserType::ADMIN->value => 'Admin',
                    ])
                    ->label('User type')
                    ->inline()
                    ->default(UserType::USER->value)
                    ->enum(UserType::class),
            ])
            ->statePath('data')
            ->model(UserModel::class);
    }

    public function create(): void
    {
        $data = $this->form->getState();
        $data['password'] = Hash::make($data['password']);
        $user = UserModel::create($data);

        $this->redirectRoute('users.show', ['user' => $user]);
    }

    public function render(): View
    {
        return view('livewire.users.create');
    }
}
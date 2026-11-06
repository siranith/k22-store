<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageExchangeRate extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'SETTINGS';
    protected static ?string $navigationLabel = 'Exchange Rate';
    protected static ?string $title = 'Exchange Rate Settings';
    protected static ?string $slug = 'settings/exchange-rate';
    protected static ?int $navigationSort = 1;
    protected static string $view = 'filament.pages.manage-exchange-rate';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'usd_to_khr_rate' => Setting::getExchangeRate(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('អត្រាប្តូរប្រាក់ (USD to KHR)')
                    ->description('Set the default USD to KHR (៛) exchange rate used for calculating Cash on Delivery (COD) amounts on sales receipts.')
                    ->icon('heroicon-o-banknotes')
                    ->schema([
                        Forms\Components\TextInput::make('usd_to_khr_rate')
                            ->label('Exchange Rate ($1 USD = ? ៛ KHR)')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->prefix('$1 = ៛')
                            ->helperText('Standard rates in Cambodia are usually 4,000 to 4,150 KHR per 1 USD.')
                            ->placeholder('4000')
                            ->live(debounce: 300),
                    ]),
            ])
            ->statePath('data');
    }

    public function setPresetRate(int $rate): void
    {
        $this->data['usd_to_khr_rate'] = $rate;
        $this->form->fill([
            'usd_to_khr_rate' => $rate,
        ]);

        Notification::make()
            ->title("Rate selected: ៛{$rate}")
            ->body('Click "Save Exchange Rate" below to apply.')
            ->info()
            ->send();
    }

    public function getCurrentRateProperty(): float
    {
        return (float) ($this->data['usd_to_khr_rate'] ?? Setting::getExchangeRate());
    }

    public function save(): void
    {
        $state = $this->form->getState();
        $rate = (float) $state['usd_to_khr_rate'];

        Setting::setExchangeRate($rate);

        Notification::make()
            ->title('Exchange rate saved successfully!')
            ->body("Active rate: $1 USD = ៛" . number_format($rate) . ' KHR')
            ->success()
            ->send();
    }
}

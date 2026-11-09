<x-filament-panels::page>
    <x-filament-panels::form wire:submit="save">
        {{ $this->form }}

        <div class="flex items-center gap-3 pt-4">
            <x-filament::button type="submit" size="lg">
                Save Settings
            </x-filament::button>
        </div>
    </x-filament-panels::form>
</x-filament-panels::page>

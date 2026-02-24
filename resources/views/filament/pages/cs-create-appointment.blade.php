<x-filament-panels::page>
    <div class="fi-main">
        <form wire:submit="create">
            {{ $this->form }}

            <div class="mt-6">
                <x-filament::button type="submit" color="primary">
                    Buat Appointment
                </x-filament::button>
            </div>
        </form>
    </div>
</x-filament-panels::page>

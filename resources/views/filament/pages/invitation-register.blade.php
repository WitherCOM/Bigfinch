<x-filament::page>
    <div class="max-w-md mx-auto">
        <form wire:submit.prevent="register" class="space-y-6">
            {{ $this->form }}

            <x-filament::button type="submit" class="w-full">
                Register
            </x-filament::button>
        </form>
    </div>
</x-filament::page>


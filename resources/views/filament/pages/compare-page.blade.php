<x-filament-panels::page>
    <div class="gap-6" style="display:grid; grid-template-columns: repeat({{ count($this->transactions) }}, minmax(0, 1fr));">
        @foreach ($this->transactions as $transaction)
            {{ $this->compareSchema(\Filament\Schemas\Schema::make($this), $transaction) }}
        @endforeach
    </div>
</x-filament-panels::page>

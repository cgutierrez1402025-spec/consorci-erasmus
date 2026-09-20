<x-filament-panels::page>
    <form wire:submit="calculate">
        {{ $this->form }}

        <x-filament::button type="submit" class="mt-6" icon="heroicon-o-calculator">
            Calcular ayuda
        </x-filament::button>
    </form>

    @if ($result)
        <x-filament::section class="mt-6">
            <x-slot name="heading">Resultado de la estimación</x-slot>

            @if (isset($result['duration']))
                <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                    Duración bajo convenio de 30 días: {{ $result['duration']['total_days'] }} días
                    ({{ $result['duration']['full_months'] }} meses y {{ $result['duration']['extra_days'] }} días).
                </p>
            @endif

            <dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($this->formattedResult as $label => $value)
                    <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                        <dt class="text-sm text-gray-500 dark:text-gray-400">{{ $label }}</dt>
                        <dd class="mt-1 text-lg font-semibold">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
        </x-filament::section>
    @endif
</x-filament-panels::page>

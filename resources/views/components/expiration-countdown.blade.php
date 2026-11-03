@if($boleto->estado === 'pendiente' && $boleto->expira_en)
    <div
        class="mt-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-900"
        role="timer"
        aria-live="polite"
        x-data="{
            segundos: {{ $boleto->segundos_restantes }},
            intervalo: null,
            formato() {
                const minutos = Math.floor(this.segundos / 60);
                const segundos = this.segundos % 60;
                return String(minutos).padStart(2, '0') + ':' + String(segundos).padStart(2, '0');
            },
            destroy() {
                window.clearInterval(this.intervalo);
            }
        }"
        x-init="intervalo = window.setInterval(() => {
            if (segundos > 0) {
                segundos--;
            } else {
                window.clearInterval(intervalo);
            }
        }, 1000)"
    >
        <p class="font-semibold">
            Vence: {{ $boleto->expira_en->timezone(config('app.display_timezone'))->format('d/m/Y H:i:s') }}
        </p>
        <p x-show="segundos > 0">
            Tiempo restante: <span x-text="formato()">{{ $boleto->minutos_restantes }} min</span>
        </p>
        <p x-show="segundos <= 0" class="font-semibold">
            El plazo terminó. El servidor validará el vencimiento al continuar.
        </p>
    </div>
@endif

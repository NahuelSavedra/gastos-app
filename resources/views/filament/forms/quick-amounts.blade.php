<div class="flex gap-2 flex-wrap" x-data="{
    amounts: [100, 500, 1000, 2000, 5000, 10000],
    setAmount(amount) {
        // Buscar el input de amount y establecer su valor
        const amountInput = document.querySelector('input[wire\\:model*=\\'amount\\']');
        if (amountInput) {
            amountInput.value = amount;
            amountInput.dispatchEvent(new Event('input', { bubbles: true }));
        }
    }
}">
    <template x-for="amount in amounts" :key="amount">
        <button
            type="button"
            @click="setAmount(amount)"
            class="px-3 py-1.5 text-sm bg-gray-100 hover:bg-primary-100 hover:text-primary-700 rounded-lg transition-colors font-medium"
            x-text="'$' + amount.toLocaleString()"
        ></button>
    </template>
</div>

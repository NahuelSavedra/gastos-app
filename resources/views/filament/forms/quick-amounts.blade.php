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
            class="px-3 py-1.5 text-sm bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:bg-primary-100 dark:hover:bg-primary-900/30 hover:text-primary-700 dark:hover:text-primary-400 rounded-lg transition-colors font-medium border border-zinc-200 dark:border-zinc-700"
            x-text="'$' + amount.toLocaleString()"
        ></button>
    </template>
</div>

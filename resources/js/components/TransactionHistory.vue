<script setup>
defineProps({
  transactions: {
    type: Array,
    default: () => [
      { id: 'TRX-9821', type: 'Depósito', account: 'Ahorro ***4521', amount: '+$1,500.00', date: '2026-11-08 14:30', status: 'Completado' },
      { id: 'TRX-9820', type: 'Transferencia', account: 'Corriente ***8812', amount: '-$450.00', date: '2026-11-08 12:15', status: 'Completado' },
      { id: 'TRX-9819', type: 'Retiro ATM', account: 'Ahorro ***4521', amount: '-$100.00', date: '2026-11-07 09:45', status: 'Pendiente' },
      { id: 'TRX-9818', type: 'Préstamo Cuota', account: 'Préstamo #302', amount: '-$320.50', date: '2026-11-06 16:00', status: 'Completado' },
    ]
  }
})

const getStatusClass = (status) => {
  switch (status) {
    case 'Completado':
      return 'bg-emerald-50 text-emerald-700 border-emerald-200'
    case 'Pendiente':
      return 'bg-amber-50 text-amber-700 border-amber-200'
    case 'Rechazado':
      return 'bg-rose-50 text-rose-700 border-rose-200'
    default:
      return 'bg-slate-50 text-slate-700 border-slate-200'
  }
}
</script>

<template>
  <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden my-6">
    <div class="p-6 border-b border-slate-100 flex items-center justify-between">
      <div>
        <h3 class="text-lg font-bold text-slate-800">Transacciones Recientes</h3>
        <p class="text-sm text-slate-500">Últimos movimientos registrados en las cuentas del sistema.</p>
      </div>
      <button class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-lg transition-colors">
        Ver todas
      </button>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-slate-50/75 text-xs font-semibold uppercase tracking-wider text-slate-500 border-b border-slate-100">
            <th class="py-3 px-6">ID Transacción</th>
            <th class="py-3 px-6">Tipo</th>
            <th class="py-3 px-6">Cuenta</th>
            <th class="py-3 px-6">Fecha / Hora</th>
            <th class="py-3 px-6">Estado</th>
            <th class="py-3 px-6 text-right">Monto</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-sm text-slate-600">
          <tr v-for="tx in transactions" :key="tx.id" class="hover:bg-slate-50/50 transition-colors">
            <td class="py-4 px-6 font-medium text-slate-800">{{ tx.id }}</td>
            <td class="py-4 px-6">{{ tx.type }}</td>
            <td class="py-4 px-6 text-slate-500">{{ tx.account }}</td>
            <td class="py-4 px-6 text-slate-500">{{ tx.date }}</td>
            <td class="py-4 px-6">
              <span :class="[getStatusClass(tx.status), 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border']">
                {{ tx.status }}
              </span>
            </td>
            <td :class="[tx.amount.startsWith('+') ? 'text-emerald-600' : 'text-slate-800', 'py-4 px-6 text-right font-semibold']">
              {{ tx.amount }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
### Entregable 2: Mejora Frontend (Componente de Tarjetas de Métricas Resumen)

He diseñado una mejora visual y funcional reutilizable usando **Vue 3 (`<script setup>`)** y **Tailwind CSS**. Este componente servirá para presentar de forma atractiva las métricas principales en el Dashboard (Total Clientes, Cuentas Activas, Saldo Total, Préstamos Pendientes, etc.) con formato de moneda, indicadores de tendencia y diseño responsivo.

Crea un archivo nuevo en tu proyecto en la ruta:  
`resources/js/components/DashboardMetrics.vue`

```vue
<script setup>
defineProps({
  metrics: {
    type: Array,
    default: () => [
      { title: 'Total Clientes', value: '1,248', change: '+12%', isPositive: true, icon: 'users' },
      { title: 'Cuentas Activas', value: '3,540', change: '+5%', isPositive: true, icon: 'credit-card' },
      { title: 'Depósitos del Mes', value: '$452,800.00', change: '+18.4%', isPositive: true, icon: 'arrow-down-right' },
      { title: 'Préstamos Emitidos', value: '$128,400.00', change: '-2.1%', isPositive: false, icon: 'banknotes' }
    ]
  }
})
</script>

<template>
  <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 my-6">
    <div
      v-for="(item, index) in metrics"
      :key="index"
      class="relative overflow-hidden rounded-xl bg-white p-6 shadow-sm border border-slate-100 hover:shadow-md transition-all duration-200"
    >
      <div class="flex items-center justify-between">
        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">
          {{ item.title }}
        </span>
        <span
          :class="[
            item.isPositive ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200',
            'inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-xs font-medium'
          ]"
        >
          {{ item.change }}
        </span>
      </div>

      <div class="mt-4 flex items-baseline justify-between">
        <h3 class="text-2xl font-bold tracking-tight text-slate-800">
          {{ item.value }}
        </h3>
      </div>
    </div>
  </div>
</template>
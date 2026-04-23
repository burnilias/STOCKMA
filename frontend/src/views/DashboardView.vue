<script setup>
import { nextTick, onMounted, onUnmounted, ref, shallowRef } from 'vue'
import Chart from 'chart.js/auto'
import api from '@/api/client'

const loading = ref(true)
const kpis = ref({
  total_products: 0,
  valeur_totale: 0,
  alertes_count: 0,
  out_of_stock_count: 0,
})
const recent = ref([])
const chartRef = shallowRef(null)
let chartInstance = null

onMounted(async () => {
  try {
    const { data } = await api.get('/reports/dashboard')
    if (data.success) {
      kpis.value = data.data.kpis
      recent.value = data.data.recent_movements
      loading.value = false
      await nextTick()
      const labels = data.data.chart.labels
      const ctx = chartRef.value?.getContext('2d')
      if (ctx) {
        chartInstance = new Chart(ctx, {
          type: 'line',
          data: {
            labels,
            datasets: [
              {
                label: 'Entrées',
                data: data.data.chart.entries,
                borderColor: '#6366f1',
                backgroundColor: (context) => {
                  const ctx = context.chart.ctx;
                  const gradient = ctx.createLinearGradient(0, 0, 0, 300);
                  gradient.addColorStop(0, 'rgba(99, 102, 241, 0.15)');
                  gradient.addColorStop(1, 'rgba(99, 102, 241, 0)');
                  return gradient;
                },
                fill: true,
                tension: 0.4,
                borderWidth: 4,
                pointRadius: 4,
                pointBackgroundColor: '#6366f1',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointHoverRadius: 6,
                pointHoverBackgroundColor: '#6366f1',
                pointHoverBorderColor: '#fff',
                pointHoverBorderWidth: 3,
              },
              {
                label: 'Sorties',
                data: data.data.chart.exits,
                borderColor: '#94a3b8',
                backgroundColor: 'transparent',
                fill: false,
                tension: 0.4,
                borderWidth: 2,
                borderDash: [5, 5],
                pointRadius: 0,
                pointHoverRadius: 4,
              },
            ],
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
              intersect: false,
              mode: 'index',
            },
            plugins: {
              legend: {
                display: false,
              },
              tooltip: {
                backgroundColor: '#fff',
                titleColor: '#1e293b',
                bodyColor: '#64748b',
                borderColor: '#e2e8f0',
                borderWidth: 1,
                padding: 12,
                boxPadding: 6,
                usePointStyle: true,
                titleFont: { size: 12, weight: 'bold' },
                bodyFont: { size: 11, weight: '600' },
                cornerRadius: 12,
                displayColors: true,
              },
            },
            scales: {
              x: {
                grid: { display: false },
                ticks: { 
                  font: { size: 10, weight: '700' }, 
                  color: '#94a3b8',
                  padding: 10
                },
              },
              y: {
                beginAtZero: true,
                border: { display: false },
                grid: { 
                  color: '#f1f5f9',
                  drawTicks: false,
                },
                ticks: { 
                  font: { size: 10, weight: '700' }, 
                  color: '#94a3b8',
                  padding: 10,
                  maxTicksLimit: 6
                },
              },
            },
          },
        })
      }
    }
  } finally {
    loading.value = false
  }
})

onUnmounted(() => {
  chartInstance?.destroy()
  chartInstance = null
})
</script>

<template>
  <div class="space-y-10">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-3xl font-black tracking-tight text-slate-900">Dashboard</h2>
        <p class="text-sm font-bold text-slate-400">Welcome back to your inventory control center.</p>
      </div>
      <div class="flex items-center gap-3 bg-white border border-slate-200 px-4 py-2 rounded-2xl shadow-sm">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4 text-brand-600">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
        </svg>
        <span class="text-xs font-black uppercase tracking-widest text-slate-600">
          {{ new Date().toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' }) }}
        </span>
      </div>
    </div>

    <div v-if="loading" class="flex h-64 items-center justify-center">
      <div class="h-12 w-12 animate-spin rounded-full border-4 border-slate-100 border-t-brand-600"></div>
    </div>

    <template v-else>
      <!-- KPIs -->
      <div class="grid grid-cols-1 gap-4 sm:gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <div class="kpi-card group" style="animation-delay: 0.1s">
          <div class="flex items-start justify-between">
            <div>
              <p class="kpi-label">Active Products</p>
              <h3 class="kpi-value !text-3xl sm:!text-4xl">{{ kpis.total_products }}</h3>
            </div>
            <div class="h-10 w-10 sm:h-12 sm:w-12 rounded-xl sm:rounded-2xl bg-brand-50 flex items-center justify-center text-brand-600 border border-brand-100 group-hover:scale-110 transition-transform">
              <NavIcon name="cube" class="h-5 w-5 sm:h-6 sm:w-6" />
            </div>
          </div>
          <div class="mt-4 sm:mt-6 flex items-center gap-2">
            <div class="flex h-5 w-5 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3 w-3">
                <path fill-rule="evenodd" d="M10 17a.75.75 0 01-.75-.75V5.612L5.29 9.77a.75.75 0 01-1.08-1.04l5.25-5.5a.75.75 0 011.08 0l5.25 5.5a.75.75 0 11-1.08 1.04l-3.96-4.158V16.25A.75.75 0 0110 17z" clip-rule="evenodd" />
              </svg>
            </div>
            <span class="text-[9px] sm:text-[10px] font-black uppercase tracking-widest text-emerald-600">Operational</span>
          </div>
        </div>

        <div class="kpi-card group" style="animation-delay: 0.15s">
          <div class="flex items-start justify-between">
            <div>
              <p class="kpi-label text-indigo-600">Valeur du stock</p>
              <h3 class="kpi-value !text-3xl sm:!text-4xl">{{ kpis.valeur_totale?.toFixed(2) }} DH</h3>
            </div>
            <div class="h-10 w-10 sm:h-12 sm:w-12 rounded-xl sm:rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 border border-indigo-100 group-hover:scale-110 transition-transform">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5 sm:h-6 sm:w-6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
          </div>
          <div class="mt-4 sm:mt-6 flex items-center gap-2">
            <span class="text-[9px] sm:text-[10px] font-black uppercase tracking-widest text-indigo-600">Valeur totale</span>
          </div>
        </div>

        <div class="kpi-card group" style="animation-delay: 0.2s">
          <div class="flex items-start justify-between">
            <div>
              <p class="kpi-label text-amber-600">Stock Alerts</p>
              <h3 class="kpi-value !text-3xl sm:!text-4xl" :class="kpis.alertes_count > 0 ? 'text-amber-600' : 'text-slate-900'">{{ kpis.alertes_count }}</h3>
            </div>
            <div class="h-10 w-10 sm:h-12 sm:w-12 rounded-xl sm:rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600 border border-amber-100 group-hover:scale-110 transition-transform">
              <NavIcon name="warning" class="h-5 w-5 sm:h-6 sm:w-6" />
            </div>
          </div>
          <div class="mt-4 sm:mt-6 flex items-center gap-2">
            <span class="text-[9px] sm:text-[10px] font-black uppercase tracking-widest" :class="kpis.alertes_count > 0 ? 'text-amber-600' : 'text-slate-400'">
              {{ kpis.alertes_count > 0 ? 'Critical Attention' : 'All levels normal' }}
            </span>
          </div>
        </div>

        <div class="kpi-card group" style="animation-delay: 0.3s">
          <div class="flex items-start justify-between">
            <div>
              <p class="kpi-label text-rose-600">Out of Stock</p>
              <h3 class="kpi-value !text-3xl sm:!text-4xl" :class="kpis.out_of_stock_count > 0 ? 'text-rose-600' : 'text-slate-900'">{{ kpis.out_of_stock_count }}</h3>
            </div>
            <div class="h-10 w-10 sm:h-12 sm:w-12 rounded-xl sm:rounded-2xl bg-rose-50 flex items-center justify-center text-rose-600 border border-rose-100 group-hover:scale-110 transition-transform">
              <NavIcon name="bell" class="h-5 w-5 sm:h-6 sm:w-6" />
            </div>
          </div>
          <div class="mt-4 sm:mt-6 flex items-center gap-2 text-[9px] sm:text-[10px] font-black uppercase tracking-widest" :class="kpis.out_of_stock_count > 0 ? 'text-rose-600' : 'text-slate-400'">
            {{ kpis.out_of_stock_count > 0 ? 'Immediate Restock' : 'Inventory Healthy' }}
          </div>
        </div>
      </div>

      <!-- Graph & Movements -->
      <div class="grid grid-cols-1 gap-6 sm:gap-10 xl:grid-cols-5">
        <div class="xl:col-span-3 space-y-6">
          <div class="flex items-center justify-between px-1">
            <h3 class="text-[10px] sm:text-xs font-black uppercase tracking-[0.2em] text-slate-400">Weekly Activity</h3>
            <div class="flex items-center gap-3 sm:gap-4">
              <div class="flex items-center gap-2">
                <span class="h-1.5 w-1.5 rounded-full bg-brand-600 shadow-sm"></span>
                <span class="text-[9px] sm:text-[10px] font-bold text-slate-400 uppercase">Entries</span>
              </div>
              <div class="flex items-center gap-2">
                <span class="h-1.5 w-1.5 rounded-full bg-slate-300"></span>
                <span class="text-[9px] sm:text-[10px] font-bold text-slate-400 uppercase">Exits</span>
              </div>
            </div>
          </div>
          <div class="card h-[300px] sm:h-[350px] !p-4 sm:!p-8">
            <canvas ref="chartRef" />
          </div>
        </div>

        <div class="xl:col-span-2 space-y-6">
          <div class="flex items-center justify-between px-1">
            <h3 class="text-[10px] sm:text-xs font-black uppercase tracking-[0.2em] text-slate-400">Recent Movements</h3>
            <RouterLink to="/movements" class="text-[9px] sm:text-[10px] font-black uppercase tracking-widest text-brand-600 hover:text-brand-700 transition-colors">View All History →</RouterLink>
          </div>
          <div class="table-shell overflow-hidden">
            <div class="overflow-x-auto custom-scrollbar">
              <table class="w-full min-w-[300px]">
                <thead class="table-head">
                  <tr>
                    <th class="text-left">Product</th>
                    <th class="text-right">Quantity</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                  <tr v-for="m in recent.slice(0, 4)" :key="m.id" class="table-row">
                    <td>
                      <div class="flex items-center gap-3">
                        <div 
                          class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg shadow-sm"
                          :class="m.type === 'entry' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600'"
                        >
                          <svg v-if="m.type === 'entry'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5">
                            <path fill-rule="evenodd" d="M10 18a.75.75 0 01-.75-.75V4.66L5.47 8.44a.75.75 0 11-1.04-1.08l5-5a.75.75 0 011.08 0l5 5a.75.75 0 11-1.04 1.08l-3.78-3.78v12.59A.75.75 0 0110 18z" clip-rule="evenodd" />
                          </svg>
                          <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5">
                            <path fill-rule="evenodd" d="M10 2a.75.75 0 01.75.75v12.59l3.78-3.78a.75.75 0 111.04 1.08l-5 5a.75.75 0 01-1.08 0l-5-5a.75.75 0 111.04-1.08l3.78 3.78V2.75A.75.75 0 0110 2z" clip-rule="evenodd" />
                          </svg>
                        </div>
                        <div class="min-w-0">
                          <p class="table-cell-main truncate">{{ m.product_name }}</p>
                          <p class="table-cell-sub truncate">{{ m.date }} • {{ m.user_name }}</p>
                        </div>
                      </div>
                    </td>
                    <td class="text-right">
                      <div class="flex flex-col items-end gap-1">
                        <div class="flex items-center gap-0.5">
                          <span v-if="m.type === 'entry'" class="font-black text-emerald-600">+</span>
                          <span v-else class="font-black text-rose-600">-</span>
                          <span class="font-black" :class="m.type === 'entry' ? 'text-emerald-600' : 'text-rose-600'">{{ m.quantity }}</span>
                        </div>
                        <span class="badge" :class="m.type === 'entry' ? 'badge-success' : 'badge-error'">
                          {{ m.type === 'entry' ? 'entrée' : 'sortie' }}
                        </span>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="!recent.length">
                    <td colspan="2" class="py-20 text-center">
                      <p class="text-[10px] font-black uppercase tracking-widest text-slate-300">No recent activity</p>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

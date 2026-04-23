<script setup>
import { onMounted, ref } from 'vue'
import api from '@/api/client'

const items = ref([])
const meta = ref({ current_page: 1, last_page: 1, total: 0 })
const search = ref('')
const loading = ref(false)
const page = ref(1)

async function load() {
  loading.value = true
  try {
    const { data } = await api.get('/reports/low-stock', {
      params: { 
        page: page.value,
        per_page: 6,
        search: search.value || undefined 
      },
    })
    if (data.success) {
      items.value = data.data
      meta.value = data.meta
    }
  } finally {
    loading.value = false
  }
}

onMounted(load)

function statusClass(s) {
  if (s === 'low') return 'badge badge-warning'
  return 'badge badge-error'
}

function statusLabel(s) {
  return s === 'low' ? 'Faible' : 'Rupture'
}
</script>

<template>
  <div class="space-y-5">
    <div class="flex flex-wrap items-center gap-2">
      <input
        v-model="search"
        type="search"
        placeholder="Filtrer par nom…"
        class="input-field max-w-xs"
        @keyup.enter="load"
      />
      <button type="button" class="btn-secondary text-sm" @click="load">Appliquer</button>
    </div>

    <div class="table-shell">
      <div v-if="loading" class="p-10 text-center text-sm text-slate-400">
        <div class="inline-block h-6 w-6 animate-spin rounded-full border-2 border-slate-200 border-t-brand-600"></div>
        <p class="mt-2 font-bold uppercase tracking-widest text-[10px]">Chargement des alertes...</p>
      </div>
      <table v-else class="w-full">
        <thead class="table-head">
          <tr>
            <th>Produit</th>
            <th>Catégorie</th>
            <th class="text-right">Prix Unitaire</th>
            <th class="text-right">Stock Actuel</th>
            <th>Statut</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
          <tr v-for="p in items" :key="p.id_product" class="table-row">
            <td>
              <p class="table-cell-main">{{ p.nom }}</p>
              <p class="table-cell-sub">ID: #{{ p.id_product }}</p>
            </td>
            <td>
              <span class="badge badge-info">{{ p.category?.nom_categorie || 'Non classé' }}</span>
            </td>
            <td class="text-right">
              <span class="table-cell-main">{{ Number(p.prix ?? 0).toFixed(2) }}</span>
              <span class="ml-1 text-[10px] font-black text-slate-400">DH</span>
            </td>
            <td class="text-right">
              <span class="table-cell-highlight text-rose-600">{{ p.quantite }}</span>
            </td>
            <td>
              <span :class="statusClass(p.stock_status)">
                {{ statusLabel(p.stock_status) }}
              </span>
            </td>
          </tr>
          <tr v-if="!items.length">
            <td colspan="5" class="py-20 text-center text-sm text-slate-400">
              <p class="text-[10px] font-black uppercase tracking-widest">Tout est en stock</p>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="meta.last_page > 1" class="flex flex-col items-center gap-4 pt-4">
      <div class="flex items-center justify-center gap-3">
        <button type="button" :disabled="page <= 1" class="btn-secondary !py-2 !px-4 !text-[10px] uppercase tracking-widest disabled:opacity-30" @click="page--">
          Précédent
        </button>
        
        <div class="flex items-center gap-1">
          <button 
            v-for="p in meta.last_page" 
            :key="p"
            @click="page = p"
            class="h-8 w-8 rounded-lg text-[10px] font-black transition-all"
            :class="page === p ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/20' : 'bg-white border border-slate-100 text-slate-400 hover:border-brand-200 hover:text-brand-600'"
          >
            {{ p }}
          </button>
        </div>

        <button
          type="button"
          :disabled="page >= meta.last_page"
          class="btn-secondary !py-2 !px-4 !text-[10px] uppercase tracking-widest disabled:opacity-30"
          @click="page++"
        >
          Suivant
        </button>
      </div>
      <div class="flex items-center gap-4 text-[10px] font-black uppercase tracking-widest text-slate-400">
        <span>Page {{ meta.current_page }} sur {{ meta.last_page }}</span>
        <span class="h-1 w-1 rounded-full bg-slate-200"></span>
        <span>Total : {{ meta.total }} éléments</span>
      </div>
    </div>
  </div>
</template>

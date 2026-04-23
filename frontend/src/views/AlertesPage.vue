<script setup>
import { onMounted, ref } from 'vue'
import api from '@/api/client'

const rows = ref([])
const meta = ref({ current_page: 1, last_page: 1, total: 0 })
const page = ref(1)
const loading = ref(false)

async function load() {
  loading.value = true
  try {
    const { data } = await api.get('/alertes', { params: { page: page.value, per_page: 6 } })
    if (data.success) {
      rows.value = data.data
      meta.value = data.meta
    }
  } finally {
    loading.value = false
  }
}

onMounted(load)
</script>

<template>
  <div class="space-y-5">
    <p class="max-w-2xl text-sm leading-relaxed text-slate-500 font-medium">
      Une alerte est considérée comme <span class="text-rose-600 font-bold">active</span> lorsque la quantité disponible est inférieure ou égale au seuil défini pour le
      produit.
    </p>
    <div class="table-shell">
      <div v-if="loading" class="p-10 text-center text-sm text-slate-400">
        <div class="inline-block h-6 w-6 animate-spin rounded-full border-2 border-slate-200 border-t-brand-600"></div>
        <p class="mt-2 font-bold uppercase tracking-widest text-[10px]">Analyse des seuils...</p>
      </div>
      <table v-else class="w-full">
        <thead class="table-head">
          <tr>
            <th>Date Détection</th>
            <th>Produit Concerné</th>
            <th class="text-right">Stock Actuel</th>
            <th class="text-right">Seuil Critique</th>
            <th>État Système</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
          <tr v-for="a in rows" :key="a.id_alerte" class="table-row">
            <td>
              <span class="table-cell-sub">{{ a.date_alerte }}</span>
            </td>
            <td>
              <p class="table-cell-main">{{ a.product?.nom }}</p>
              <p class="text-[10px] font-bold text-slate-400">REF: #{{ a.product?.id_product }}</p>
            </td>
            <td class="text-right">
              <span class="table-cell-highlight text-rose-600">{{ a.product?.quantite }}</span>
            </td>
            <td class="text-right">
              <span class="table-cell-main text-slate-400">{{ a.seuil_min }}</span>
            </td>
            <td>
              <span
                class="badge"
                :class="a.alerte_active ? 'badge-error' : 'badge-info'"
              >
                {{ a.alerte_active ? 'Critique' : 'Résolu' }}
              </span>
            </td>
          </tr>
          <tr v-if="!rows.length">
            <td colspan="5" class="py-20 text-center text-sm text-slate-400">
              <p class="text-[10px] font-black uppercase tracking-widest">Aucune alerte en cours</p>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="meta.last_page > 1" class="flex flex-col items-center gap-4 pt-4">
      <div class="flex items-center justify-center gap-3">
        <button type="button" :disabled="page <= 1" class="btn-secondary !py-2 !px-4 !text-[10px] uppercase tracking-widest disabled:opacity-30" @click="page--; load()">
          Précédent
        </button>
        
        <div class="flex items-center gap-1">
          <button 
            v-for="p in meta.last_page" 
            :key="p"
            @click="page = p; load()"
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
          @click="page++; load()"
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

<script setup>
import { onMounted, ref, watch } from 'vue'
import { useToastStore } from '@/stores/toast'
import api from '@/api/client'

const toast = useToastStore()
const movements = ref([])
const meta = ref({ current_page: 1, last_page: 1 })
const products = ref([])
const loading = ref(false)
const filters = ref({
  product_id: '',
  type: '',
  date_from: '',
  date_to: '',
})
const page = ref(1)

async function loadProducts() {
  const { data } = await api.get('/products', { params: { per_page: 200 } })
  if (data.success) products.value = data.data
}

async function load() {
  loading.value = true
  try {
    const { data } = await api.get('/reports/movement-history', {
      params: {
        page: page.value,
        per_page: 6,
        product_id: filters.value.product_id || undefined,
        type: filters.value.type || undefined,
        date_from: filters.value.date_from || undefined,
        date_to: filters.value.date_to || undefined,
      },
    })
    if (data.success) {
      movements.value = data.data
      meta.value = data.meta
    }
  } catch (e) {
    toast.show('Erreur chargement', 'error')
  } finally {
    loading.value = false
  }
}

function resetFilters() {
  filters.value = {
    product_id: '',
    type: '',
    date_from: '',
    date_to: '',
  }
  page.value = 1
}

onMounted(async () => {
  await loadProducts()
  await load()
})

watch(
  () => ({ ...filters.value }),
  () => {
    page.value = 1
  },
  { deep: true },
)
watch([page, filters], () => load(), { deep: true })
</script>

<template>
  <div class="space-y-5">
    <div class="card flex flex-wrap items-end gap-4 p-4">
      <div>
        <label class="mb-1 block text-xs font-medium text-stone-500">Produit</label>
        <select v-model="filters.product_id" class="input-field min-w-[11rem] py-1.5 text-sm">
          <option value="">Tous</option>
          <option v-for="p in products" :key="p.id_product" :value="p.id_product">{{ p.nom }}</option>
        </select>
      </div>
      <div>
        <label class="mb-1 block text-xs font-medium text-stone-500">Type</label>
        <select v-model="filters.type" class="input-field min-w-[8rem] py-1.5 text-sm">
          <option value="">Tous</option>
          <option value="entry">Entrée</option>
          <option value="exit">Sortie</option>
        </select>
      </div>
      <div>
        <label class="mb-1 block text-xs font-medium text-stone-500">Du</label>
        <input v-model="filters.date_from" type="date" class="input-field py-1.5 text-sm" />
      </div>
      <div>
        <label class="mb-1 block text-xs font-medium text-stone-500">Au</label>
        <input v-model="filters.date_to" type="date" class="input-field py-1.5 text-sm" />
      </div>
      <button class="btn-secondary !py-2 !px-4 !text-[10px] uppercase tracking-widest" @click="resetFilters">
        Réinitialiser
      </button>
    </div>

    <div class="table-shell">
      <div v-if="loading" class="p-10 text-center text-sm text-slate-400">
        <div class="inline-block h-6 w-6 animate-spin rounded-full border-2 border-slate-200 border-t-brand-600"></div>
        <p class="mt-2 font-bold uppercase tracking-widest text-[10px]">Chargement de l'historique...</p>
      </div>
      <table v-else class="w-full">
        <thead class="table-head">
          <tr>
            <th>Date & Heure</th>
            <th>Produit</th>
            <th>Type</th>
            <th class="text-right">Quantité</th>
            <th>Utilisateur</th>
            <th>Détails</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
          <tr v-for="m in movements" :key="m.id" class="table-row">
            <td>
              <span class="table-cell-sub">{{ m.date }}</span>
            </td>
            <td>
              <p class="table-cell-main">{{ m.product?.nom }}</p>
              <p class="text-[10px] font-bold text-slate-400">ID: #{{ m.product?.id_product }}</p>
            </td>
            <td>
              <span class="badge" :class="m.type === 'entry' ? 'badge-success' : 'badge-error'">
                {{ m.type === 'entry' ? 'entrée' : 'sortie' }}
              </span>
            </td>
            <td class="text-right">
              <div class="flex items-center gap-0.5 justify-end">
                <span v-if="m.type === 'entry'" class="font-black text-emerald-600">+</span>
                <span v-else class="font-black text-rose-600">-</span>
                <span class="font-black" :class="m.type === 'entry' ? 'text-emerald-600' : 'text-rose-600'">{{ m.quantity }}</span>
              </div>
            </td>
            <td>
              <div class="flex items-center gap-2">
                <div class="h-6 w-6 rounded-full bg-slate-100 flex items-center justify-center text-[8px] font-black text-slate-500 border border-slate-200">
                  {{ m.user?.nom?.substring(0, 2).toUpperCase() }}
                </div>
                <span class="table-cell-main !text-xs">{{ m.user?.nom }}</span>
              </div>
            </td>
            <td>
              <div class="flex flex-wrap gap-1">
                <span v-if="m.supplier" class="badge badge-info !lowercase first-letter:uppercase">Fourn: {{ m.supplier }}</span>
                <span v-if="m.reason" class="badge badge-info !lowercase first-letter:uppercase">{{ m.reason }}</span>
                <span v-if="m.note" class="badge badge-info !lowercase first-letter:uppercase">{{ m.note }}</span>
                <span v-if="!m.supplier && !m.reason && !m.note" class="text-slate-300 italic text-[10px]">Aucun détail</span>
              </div>
            </td>
          </tr>
          <tr v-if="!movements.length">
            <td colspan="6" class="py-20 text-center">
              <p class="text-[10px] font-black uppercase tracking-widest text-slate-300">Aucun mouvement trouvé</p>
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

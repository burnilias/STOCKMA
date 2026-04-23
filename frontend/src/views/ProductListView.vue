<script setup>
import { onMounted, ref, watch } from 'vue'
import { RouterLink } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useToastStore } from '@/stores/toast'
import api from '@/api/client'

const auth = useAuthStore()
const toast = useToastStore()

const products = ref([])
const meta = ref({ current_page: 1, last_page: 1, total: 0 })
const search = ref('')
const categoryId = ref('')
const categories = ref([])
const loading = ref(false)
const page = ref(1)

async function load() {
  loading.value = true
  try {
    const { data } = await api.get('/products', {
      params: {
        page: page.value,
        per_page: 6,
        search: search.value || undefined,
        id_categorie: categoryId.value || undefined,
      },
    })
    if (data.success) {
      products.value = data.data
      meta.value = data.meta
    }
  } catch (e) {
    toast.show(e.response?.data?.message || 'Erreur chargement', 'error')
  } finally {
    loading.value = false
  }
}

async function loadCategories() {
  const { data } = await api.get('/categories')
  if (data.success) categories.value = data.data
}

onMounted(async () => {
  await loadCategories()
  await load()
})

watch([search, categoryId], () => {
  page.value = 1
})
watch([page, search, categoryId], () => load())

function statusBadgeClass(s) {
  if (s === 'ok') return 'badge badge-success'
  if (s === 'low') return 'badge badge-warning'
  return 'badge badge-error'
}

function statusLabel(s) {
  if (s === 'ok') return 'En Stock'
  if (s === 'low') return 'Stock Faible'
  return 'Rupture'
}

async function remove(p) {
  if (!confirm(`Supprimer « ${p.nom} » ?`)) return
  try {
    await api.delete(`/products/${p.id_product}`)
    toast.show('Produit supprimé.')
    load()
  } catch (e) {
    toast.show(e.response?.data?.message || 'Erreur', 'error')
  }
}
</script>

<template>
  <div class="space-y-5">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
      <div class="flex w-full flex-wrap gap-2 sm:w-auto">
        <input
          v-model="search"
          type="search"
          placeholder="Rechercher par nom…"
          class="input-field min-w-[200px] max-w-xs"
        />
        <select v-model="categoryId" class="input-field max-w-xs">
          <option value="">Toutes les catégories</option>
          <option v-for="c in categories" :key="c.id_categorie" :value="c.id_categorie">
            {{ c.nom_categorie }}
          </option>
        </select>
      </div>
      <RouterLink v-if="auth.isAdmin" to="/products/new" class="btn-primary shrink-0">
        Nouveau produit
      </RouterLink>
    </div>

    <div class="table-shell">
      <div v-if="loading" class="p-10 text-center text-sm text-slate-400">
        <div class="inline-block h-6 w-6 animate-spin rounded-full border-2 border-slate-200 border-t-brand-600"></div>
        <p class="mt-2 font-bold uppercase tracking-widest text-[10px]">Chargement des produits...</p>
      </div>
      <table v-else class="w-full">
        <thead class="table-head">
          <tr>
            <th>Produit</th>
            <th>Catégorie</th>
            <th class="text-right">Prix Unitaire</th>
            <th class="text-right">Stock</th>
            <th>Statut</th>
            <th v-if="auth.isAdmin" class="text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
          <tr v-for="p in products" :key="p.id_product" class="table-row">
            <td>
              <p class="table-cell-main">{{ p.nom }}</p>
              <p class="text-[10px] font-bold text-slate-400">REF: #{{ p.id_product }}</p>
            </td>
            <td>
              <span class="badge badge-info">{{ p.category?.nom_categorie || 'Non classé' }}</span>
            </td>
            <td class="text-right">
              <span class="table-cell-main">{{ p.prix?.toFixed(2) }}</span>
              <span class="ml-1 text-[10px] font-black text-slate-400">DH</span>
            </td>
            <td class="text-right">
              <span class="table-cell-highlight">{{ p.quantite }}</span>
            </td>
            <td>
              <span :class="statusBadgeClass(p.stock_status)">
                {{ statusLabel(p.stock_status) }}
              </span>
            </td>
            <td v-if="auth.isAdmin" class="text-right">
              <div class="flex items-center justify-end gap-2">
                <RouterLink :to="`/products/${p.id_product}/edit`" class="btn-secondary !px-3 !py-1.5 !text-[10px] uppercase tracking-widest"> 
                  Modifier 
                </RouterLink>
                <button type="button" class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 transition-colors" @click="remove(p)">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.10 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                  </svg>
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="!products.length">
            <td :colspan="auth.isAdmin ? 6 : 5" class="py-20 text-center">
              <p class="text-[10px] font-black uppercase tracking-widest text-slate-300">Aucun produit trouvé</p>
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

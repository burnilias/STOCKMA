<script setup>
import { onMounted, ref } from 'vue'
import { useToastStore } from '@/stores/toast'
import api from '@/api/client'

const toast = useToastStore()
const categories = ref([])
const loading = ref(false)
const editing = ref(null)
const nom = ref('')

const meta = ref({ current_page: 1, last_page: 1, total: 0 })
const page = ref(1)

async function load() {
  loading.value = true
  try {
    const { data } = await api.get('/categories', { params: { page: page.value, per_page: 6 } })
    if (data.success) {
      categories.value = data.data
      meta.value = data.meta
    }
  } finally {
    loading.value = false
  }
}

onMounted(load)

function startCreate() {
  editing.value = 'new'
  nom.value = ''
}

function startEdit(c) {
  editing.value = c.id_categorie
  nom.value = c.nom_categorie
}

function cancel() {
  editing.value = null
}

async function save() {
  try {
    if (editing.value === 'new') {
      await api.post('/categories', { nom_categorie: nom.value })
      toast.show('Catégorie créée.')
    } else {
      await api.put(`/categories/${editing.value}`, { nom_categorie: nom.value })
      toast.show('Catégorie mise à jour.')
    }
    editing.value = null
    load()
  } catch (e) {
    toast.show(
      e.response?.data?.message ||
        Object.values(e.response?.data?.errors || {})
          .flat()
          .join(' ') ||
      'Erreur',
      'error',
    )
  }
}

async function remove(c) {
  if (!confirm(`Supprimer « ${c.nom_categorie} » ?`)) return
  try {
    await api.delete(`/categories/${c.id_categorie}`)
    toast.show('Catégorie supprimée.')
    load()
  } catch (e) {
    toast.show(e.response?.data?.message || 'Erreur', 'error')
  }
}
</script>

<template>
  <div class="space-y-5">
    <button type="button" class="btn-primary" @click="startCreate">Nouvelle catégorie</button>

    <div v-if="editing" class="card max-w-md space-y-3 p-5">
      <input v-model="nom" class="input-field" placeholder="Nom de la catégorie" />
      <div class="flex flex-wrap gap-2">
        <button type="button" class="btn-primary px-3 py-1.5 text-xs" @click="save">Enregistrer</button>
        <button type="button" class="btn-secondary px-3 py-1.5 text-xs" @click="cancel">Annuler</button>
      </div>
    </div>

    <div class="table-shell">
      <div v-if="loading" class="p-10 text-center text-sm text-slate-400">
        <div class="inline-block h-6 w-6 animate-spin rounded-full border-2 border-slate-200 border-t-brand-600"></div>
        <p class="mt-2 font-bold uppercase tracking-widest text-[10px]">Chargement des catégories...</p>
      </div>
      <table v-else class="w-full">
        <thead class="table-head">
          <tr>
            <th>ID</th>
            <th>Nom de la Catégorie</th>
            <th class="text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
          <tr v-for="c in categories" :key="c.id_categorie" class="table-row">
            <td>
              <span class="table-cell-sub">#{{ c.id_categorie }}</span>
            </td>
            <td>
              <span class="table-cell-main">{{ c.nom_categorie }}</span>
            </td>
            <td class="text-right">
              <div class="flex items-center justify-end gap-2">
                <button type="button" class="btn-secondary !px-3 !py-1.5 !text-[10px] uppercase tracking-widest" @click="startEdit(c)">
                  Modifier
                </button>
                <button type="button" class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 transition-colors" @click="remove(c)">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                  </svg>
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="!categories.length">
            <td colspan="3" class="py-20 text-center">
              <p class="text-[10px] font-black uppercase tracking-widest text-slate-300">Aucune catégorie</p>
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

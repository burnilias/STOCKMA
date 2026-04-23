<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { useToastStore } from '@/stores/toast'
import api from '@/api/client'

const route = useRoute()
const router = useRouter()
const toast = useToastStore()

const isEdit = ref(false)
const loading = ref(false)
const categories = ref([])
const errors = ref({})
const form = ref({
  id_categorie: '',
  nom: '',
  prix: 0,
  quantite: 0,
})

onMounted(async () => {
  const { data: catData } = await api.get('/categories')
  if (catData.success) categories.value = catData.data

  const id = route.params.id
  if (id) {
    isEdit.value = true
    loading.value = true
    try {
      const { data } = await api.get(`/products/${id}`)
      if (data.success) {
        const p = data.data
        form.value = {
          id_categorie: p.id_categorie,
          nom: p.nom,
          prix: p.prix,
          quantite: p.quantite,
        }
      }
    } finally {
      loading.value = false
    }
  }
})

async function submit() {
  loading.value = true
  errors.value = {}
  try {
    if (isEdit.value) {
      await api.put(`/products/${route.params.id}`, form.value)
      toast.show('Produit mis à jour.')
    } else {
      await api.post('/products', form.value)
      toast.show('Produit créé.')
    }
    router.push('/products')
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data.errors
    }
    const msg =
      e.response?.data?.message ||
      Object.values(e.response?.data?.errors || {})
        .flat()
        .join(' ') ||
      'Erreur'
    toast.show(msg, 'error')
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="form-page-wrap">
    <div v-if="loading && isEdit" class="flex flex-col items-center justify-center p-20">
      <div class="h-8 w-8 animate-spin rounded-full border-2 border-slate-200 border-t-brand-600"></div>
      <p class="mt-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Chargement du produit...</p>
    </div>
    <form v-else class="card max-w-2xl mx-auto space-y-6 p-8" @submit.prevent="submit">
      <div class="border-b border-slate-100 pb-5">
        <h2 class="text-xl font-bold text-slate-900 tracking-tight">
          {{ isEdit ? 'Modifier le produit' : 'Nouveau produit' }}
        </h2>
        <p class="mt-1 text-sm text-slate-500">
          {{ isEdit ? 'Mettre à jour les informations du produit existant.' : 'Ajouter une nouvelle référence au catalogue.' }}
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="md:col-span-2">
          <label class="mb-2 block text-[10px] font-black uppercase tracking-widest text-slate-500">Catégorie</label>
          <select v-model="form.id_categorie" required class="input-field" :class="{'border-rose-500': errors.id_categorie}">
            <option disabled value="">Choisir une catégorie…</option>
            <option v-for="c in categories" :key="c.id_categorie" :value="c.id_categorie">
              {{ c.nom_categorie }}
            </option>
          </select>
          <p v-if="errors.id_categorie" class="mt-1 text-[10px] font-bold text-rose-500">{{ errors.id_categorie[0] }}</p>
        </div>

        <div class="md:col-span-2">
          <label class="mb-2 block text-[10px] font-black uppercase tracking-widest text-slate-500">Nom du produit</label>
          <input v-model="form.nom" required class="input-field" :class="{'border-rose-500': errors.nom}" placeholder="Ex. Laptop Dell XPS 15..." />
          <p v-if="errors.nom" class="mt-1 text-[10px] font-bold text-rose-500">{{ errors.nom[0] }}</p>
        </div>

        <div>
          <label class="mb-2 block text-[10px] font-black uppercase tracking-widest text-slate-500">Prix de vente (DH)</label>
          <input
            v-model.number="form.prix"
            type="number"
            min="0"
            step="0.01"
            required
            class="input-field tabular-nums"
            :class="{'border-rose-500': errors.prix}"
            placeholder="0.00"
          />
          <p v-if="errors.prix" class="mt-1 text-[10px] font-bold text-rose-500">{{ errors.prix[0] }}</p>
        </div>

        <div>
          <label class="mb-2 block text-[10px] font-black uppercase tracking-widest text-slate-500">Stock Initial</label>
          <input
            v-model.number="form.quantite"
            type="number"
            min="0"
            step="1"
            required
            class="input-field tabular-nums"
            :class="{'border-rose-500': errors.quantite}"
            placeholder="0"
            :disabled="isEdit"
          />
          <p v-if="errors.quantite" class="mt-1 text-[10px] font-bold text-rose-500">{{ errors.quantite[0] }}</p>
          <p v-if="isEdit" class="mt-1 text-[10px] text-slate-400 italic">Le stock se gère via les entrées/sorties.</p>
        </div>
      </div>

      <div class="flex items-center gap-3 pt-4">
        <button type="submit" :disabled="loading" class="btn-primary flex-1 py-4 text-base">
          {{ loading ? 'Enregistrement...' : (isEdit ? 'Mettre à jour' : 'Créer le produit') }}
        </button>
        <RouterLink to="/products" class="btn-secondary py-4 px-8 text-base">
          Annuler
        </RouterLink>
      </div>
    </form>
  </div>
</template>

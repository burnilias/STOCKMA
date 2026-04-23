<script setup>
import { onMounted, ref } from 'vue'
import { useToastStore } from '@/stores/toast'
import api from '@/api/client'

const toast = useToastStore()
const products = ref([])
const loading = ref(false)
const form = ref({
  product_id: '',
  quantity: 1,
  date: new Date().toISOString().slice(0, 10),
  supplier: '',
  note: '',
})

onMounted(async () => {
  const { data } = await api.get('/products', { params: { per_page: 100 } })
  if (data.success) products.value = data.data
})

async function submit() {
  loading.value = true
  try {
    await api.post('/stock-movements', {
      ...form.value,
      type: 'entry',
      product_id: Number(form.value.product_id),
      quantity: Number(form.value.quantity),
    })
    toast.show('Entrée enregistrée.')
    form.value.supplier = ''
    form.value.note = ''
  } catch (e) {
    toast.show(
      e.response?.data?.message ||
        Object.values(e.response?.data?.errors || {})
          .flat()
          .join(' ') ||
      'Erreur',
      'error',
    )
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="mx-auto max-w-lg py-6">
    <form class="card space-y-6 !p-8" @submit.prevent="submit">
      <div class="space-y-1">
        <h2 class="text-xl font-black tracking-tight text-slate-900">Réception de Stock</h2>
        <p class="text-xs font-bold text-slate-400">Enregistrez une nouvelle entrée de marchandise.</p>
      </div>

      <div class="space-y-4">
        <div class="space-y-1.5">
          <label class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Sélection du Produit</label>
          <select v-model="form.product_id" required class="input-field !py-2.5 !text-xs">
            <option disabled value="">Choisir un produit…</option>
            <option v-for="p in products" :key="p.id_product" :value="p.id_product">
              {{ p.nom }} (Actuel: {{ p.quantite }})
            </option>
          </select>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-1.5">
            <label class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Quantité</label>
            <input
              v-model.number="form.quantity"
              type="number"
              min="1"
              step="1"
              required
              class="input-field tabular-nums !py-2.5 !text-xs"
              placeholder="0"
            />
          </div>
          <div class="space-y-1.5">
            <label class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Date</label>
            <input v-model="form.date" type="date" required class="input-field !py-2.5 !text-xs" />
          </div>
        </div>

        <div class="space-y-1.5">
          <label class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Source / Fournisseur</label>
          <input v-model="form.supplier" class="input-field !py-2.5 !text-xs" placeholder="Nom de l'expéditeur…" />
        </div>

        <div class="space-y-1.5">
          <label class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Notes</label>
          <textarea v-model="form.note" rows="2" class="input-field resize-none !py-2.5 !text-xs" placeholder="Détails (BL, transport…)" />
        </div>
      </div>

      <button type="submit" :disabled="loading" class="btn-primary w-full !py-3.5 !text-sm shadow-xl shadow-brand-600/20">
        <template v-if="loading">
          <div class="h-4 w-4 animate-spin rounded-full border-2 border-white/20 border-t-white mr-2"></div>
          Traitement...
        </template>
        <template v-else>Confirmer l'entrée</template>
      </button>
    </form>
  </div>
</template>

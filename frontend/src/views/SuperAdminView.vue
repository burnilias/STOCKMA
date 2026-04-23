<script setup>
import { computed, ref, onMounted } from 'vue'
import api from '@/api/client'
import { useToastStore } from '@/stores/toast'

const toast = useToastStore()
const loading = ref(true)
const companies = ref([])
const selectedCompany = ref(null)
const showModal = ref(false)
const showDeleteConfirm = ref(false)
const companyToDelete = ref(null)
const modalMode = ref('create')

const stats = computed(() => ({
  total: companies.value.length,
  actif: companies.value.filter(c => c.statut === 'actif').length,
  inactif: companies.value.filter(c => c.statut === 'inactif').length,
}))

const form = ref({
  company_nom: '',
  company_email: '',
  company_telephone: '',
  company_adresse: '',
  nom: '',
  email: '',
  password: '',
  password_confirmation: '',
})

onMounted(async () => {
  await fetchCompanies()
})

async function fetchCompanies() {
  loading.value = true
  try {
    const { data } = await api.get('/companies')
    if (data.success) {
      companies.value = data.data
    }
  } catch {
    toast.show('Erreur lors du chargement des entreprises.', 'error')
  } finally {
    loading.value = false
  }
}

function openCreate() {
  modalMode.value = 'create'
  form.value = { company_nom: '', company_email: '', company_telephone: '', company_adresse: '', nom: '', email: '', password: '', password_confirmation: '' }
  selectedCompany.value = null
  showModal.value = true
}

function openEdit(company) {
  modalMode.value = 'edit'
  selectedCompany.value = company
  form.value = { company_nom: company.nom, company_email: company.email, company_telephone: company.telephone || '', company_adresse: company.adresse || '', nom: '', email: '', password: '', password_confirmation: '' }
  showModal.value = true
}

async function submitForm() {
  try {
    if (modalMode.value === 'create') {
      await api.post('/companies', form.value)
      toast.show('Entreprise créée avec succès.', 'success')
    } else {
      await api.put(`/companies/${selectedCompany.value.id}`, {
        nom: form.value.company_nom,
        email: form.value.company_email,
        telephone: form.value.company_telephone,
        adresse: form.value.company_adresse,
      })
      toast.show('Entreprise mise à jour.', 'success')
    }
    showModal.value = false
    await fetchCompanies()
  } catch (e) {
    toast.show(e.response?.data?.message || 'Une erreur est survenue.', 'error')
  }
}

async function toggleStatut(company) {
  const newStatut = company.statut === 'actif' ? 'inactif' : 'actif'
  try {
    await api.patch(`/companies/${company.id}/statut`, { statut: newStatut })
    toast.show(`Entreprise ${newStatut === 'actif' ? 'activée' : 'désactivée'}.`, 'success')
    await fetchCompanies()
  } catch {
    toast.show('Erreur lors de la mise à jour du statut.', 'error')
  }
}

async function deleteCompany(company) {
  companyToDelete.value = company
  showDeleteConfirm.value = true
}

async function confirmDelete() {
  if (!companyToDelete.value) return
  try {
    await api.delete(`/companies/${companyToDelete.value.id}`)
    toast.show('Entreprise supprimée.', 'success')
    showDeleteConfirm.value = false
    companyToDelete.value = null
    await fetchCompanies()
  } catch {
    toast.show('Erreur lors de la suppression.', 'error')
  }
}

function formatDate(date) {
  return new Date(date).toLocaleDateString('fr-FR', { year: 'numeric', month: 'short', day: 'numeric' })
}
</script>

<template>
  <div class="space-y-8">
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-3xl font-black tracking-tight text-slate-900">Super Admin</h2>
        <p class="text-sm font-bold text-slate-400">Vue globale — Gestion des entreprises</p>
      </div>
      <button class="btn-primary" @click="openCreate">
        + Nouvelle entreprise
      </button>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
      <div class="kpi-card group">
        <div class="flex items-start justify-between">
          <div>
            <p class="kpi-label">Total Entreprises</p>
            <h3 class="kpi-value">{{ stats.total }}</h3>
          </div>
          <div class="h-10 w-10 rounded-xl bg-brand-50 flex items-center justify-center text-brand-600 border border-brand-100">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" /></svg>
          </div>
        </div>
      </div>
      <div class="kpi-card group">
        <div class="flex items-start justify-between">
          <div>
            <p class="kpi-label text-emerald-600">Actives</p>
            <h3 class="kpi-value">{{ stats.actif }}</h3>
          </div>
          <div class="h-10 w-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 border border-emerald-100">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
          </div>
        </div>
      </div>
      <div class="kpi-card group">
        <div class="flex items-start justify-between">
          <div>
            <p class="kpi-label text-slate-600">Inactives</p>
            <h3 class="kpi-value">{{ stats.inactif }}</h3>
          </div>
          <div class="h-10 w-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 border border-slate-100">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
          </div>
        </div>
      </div>
    </div>

    <div class="table-shell">
      <div class="border-b border-slate-200/90 px-4 py-3 text-sm font-semibold text-slate-800">
        Toutes les entreprises
      </div>
      <div v-if="loading" class="flex h-48 items-center justify-center">
        <div class="h-10 w-10 animate-spin rounded-full border-4 border-slate-100 border-t-brand-600"></div>
      </div>
      <table v-else class="w-full text-sm">
        <thead class="table-head">
          <tr>
            <th class="px-4 py-2.5 text-left">Entreprise</th>
            <th class="px-4 py-2.5 text-left">Email</th>
            <th class="px-4 py-2.5 text-center">Statut</th>
            <th class="px-4 py-2.5 text-center">Utilisateurs</th>
            <th class="px-4 py-2.5 text-center">Produits</th>
            <th class="px-4 py-2.5 text-left">Créée le</th>
            <th class="px-4 py-2.5 text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="company in companies" :key="company.id" class="table-row-divider">
            <td class="px-4 py-3 font-medium text-slate-900">{{ company.nom }}</td>
            <td class="px-4 py-3 text-slate-600">{{ company.email }}</td>
            <td class="px-4 py-3 text-center">
              <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold uppercase tracking-wider" :class="company.statut === 'actif' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'">
                {{ company.statut }}
              </span>
            </td>
            <td class="px-4 py-3 text-center tabular-nums text-slate-700">{{ company.users_count ?? 0 }}</td>
            <td class="px-4 py-3 text-center tabular-nums text-slate-700">{{ company.products_count ?? 0 }}</td>
            <td class="px-4 py-3 text-slate-500">{{ formatDate(company.created_at) }}</td>
            <td class="px-4 py-3">
              <div class="flex items-center justify-end gap-2">
                <button class="rounded-lg p-2 text-slate-400 hover:bg-slate-50 hover:text-brand-600 transition-all" title="Modifier" @click="openEdit(company)">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" /></svg>
                </button>
                <button class="rounded-lg p-2 text-slate-400 hover:bg-slate-50 hover:text-amber-600 transition-all" :title="company.statut === 'actif' ? 'Désactiver' : 'Activer'" @click="toggleStatut(company)">
                  <svg v-if="company.statut === 'actif'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                  <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </button>
                <button class="rounded-lg p-2 text-slate-400 hover:bg-rose-50 hover:text-rose-600 transition-all" title="Supprimer" @click="deleteCompany(company)">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="!companies.length">
            <td colspan="7" class="px-4 py-12 text-center text-sm text-slate-500">Aucune entreprise enregistrée.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <Transition name="fade">
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm p-4">
        <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl">
          <h3 class="text-lg font-black text-slate-900 mb-6">{{ modalMode === 'create' ? 'Nouvelle entreprise' : 'Modifier l\'entreprise' }}</h3>
          <form class="space-y-4" @submit.prevent="submitForm">
            <div class="space-y-2">
              <label class="text-xs font-black uppercase tracking-widest text-slate-500">Nom de l'entreprise *</label>
              <input v-model="form.company_nom" type="text" required class="input-field" />
            </div>
            <div class="space-y-2">
              <label class="text-xs font-black uppercase tracking-widest text-slate-500">Email *</label>
              <input v-model="form.company_email" type="email" required class="input-field" />
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div class="space-y-2">
                <label class="text-xs font-black uppercase tracking-widest text-slate-500">Téléphone</label>
                <input v-model="form.company_telephone" type="text" class="input-field" />
              </div>
              <div class="space-y-2">
                <label class="text-xs font-black uppercase tracking-widest text-slate-500">Adresse</label>
                <input v-model="form.company_adresse" type="text" class="input-field" />
              </div>
            </div>
            <template v-if="modalMode === 'create'">
              <hr class="border-slate-100" />
              <p class="text-xs font-black uppercase tracking-widest text-slate-400">Administrateur</p>
              <div class="space-y-2">
                <label class="text-xs font-black uppercase tracking-widest text-slate-500">Nom complet *</label>
                <input v-model="form.nom" type="text" required class="input-field" />
              </div>
              <div class="space-y-2">
                <label class="text-xs font-black uppercase tracking-widest text-slate-500">Email admin *</label>
                <input v-model="form.email" type="email" required class="input-field" />
              </div>
              <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                  <label class="text-xs font-black uppercase tracking-widest text-slate-500">Mot de passe *</label>
                  <input v-model="form.password" type="password" required class="input-field" />
                </div>
                <div class="space-y-2">
                  <label class="text-xs font-black uppercase tracking-widest text-slate-500">Confirmation *</label>
                  <input v-model="form.password_confirmation" type="password" required class="input-field" />
                </div>
              </div>
            </template>
            <div class="flex justify-end gap-3 pt-4">
              <button type="button" class="btn-ghost" @click="showModal = false">Annuler</button>
              <button type="submit" class="btn-primary">{{ modalMode === 'create' ? 'Créer' : 'Enregistrer' }}</button>
            </div>
          </form>
        </div>
      </div>
    </Transition>

    <!-- Modal Confirmation Suppression -->
    <Teleport to="body">
      <Transition name="fade">
        <div v-if="showDeleteConfirm" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
          <div class="card w-full max-w-md !p-8 space-y-6 shadow-2xl">
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-rose-50 text-rose-600 border border-rose-100 mx-auto">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-8 w-8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
              </svg>
            </div>
            
            <div class="text-center space-y-2">
              <h3 class="text-xl font-black text-slate-900">Confirmer la suppression</h3>
              <p class="text-sm font-medium text-slate-500">
                Êtes-vous sûr de vouloir supprimer l'entreprise <span class="font-bold text-slate-900">"{{ companyToDelete?.nom }}"</span> ?
                <br>Cette action supprimera tous les produits et utilisateurs associés.
              </p>
            </div>

            <div class="flex gap-3">
              <button class="btn-secondary flex-1 !py-3 !text-xs uppercase tracking-widest" @click="showDeleteConfirm = false">
                Annuler
              </button>
              <button class="btn-primary flex-1 !bg-rose-600 !border-rose-600 !py-3 !text-xs uppercase tracking-widest hover:!bg-rose-700" @click="confirmDelete">
                Supprimer
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import { useToastStore } from '@/stores/toast'
import api from '@/api/client'

const toast = useToastStore()
const users = ref([])
const meta = ref({})
const page = ref(1)
const loading = ref(false)
const editing = ref(null)
const form = reactive({
  nom: '',
  email: '',
  mot_de_passe: '',
  role: 'employee',
})

async function load() {
  loading.value = true
  try {
    const { data } = await api.get('/users', { 
      params: { 
        page: page.value,
        per_page: 6 
      } 
    })
    if (data.success) {
      users.value = data.data
      meta.value = data.meta
    }
  } catch (e) {
    toast.show('Erreur chargement', 'error')
  } finally {
    loading.value = false
  }
}

onMounted(load)

function startCreate() {
  editing.value = 'new'
  form.nom = ''
  form.email = ''
  form.mot_de_passe = ''
  form.role = 'employee'
}

function startEdit(u) {
  editing.value = u.id_personne
  form.nom = u.nom
  form.email = u.email
  form.mot_de_passe = ''
  form.role = u.role
}

function cancelEdit() {
  editing.value = null
}

async function save() {
  try {
    if (editing.value === 'new') {
      await api.post('/users', { ...form })
      toast.show('Utilisateur créé.')
    } else {
      const payload = { nom: form.nom, email: form.email, role: form.role }
      if (form.mot_de_passe) payload.mot_de_passe = form.mot_de_passe
      await api.put(`/users/${editing.value}`, payload)
      toast.show('Utilisateur mis à jour.')
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

async function remove(u) {
  if (!confirm(`Supprimer ${u.email} ?`)) return
  try {
    await api.delete(`/users/${u.id_personne}`)
    toast.show('Utilisateur supprimé.')
    load()
  } catch (e) {
    toast.show(e.response?.data?.message || 'Erreur', 'error')
  }
}
</script>

<template>
  <div class="space-y-6">
    <button type="button" class="btn-primary" @click="startCreate">Nouvel utilisateur</button>

    <div v-if="editing" class="card max-w-md space-y-4 p-6">
      <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">{{ editing === 'new' ? 'Créer un compte' : 'Modifier le compte' }}</h3>
      <div>
        <label class="mb-1.5 block text-[10px] font-black uppercase tracking-widest text-slate-500">Nom</label>
        <input v-model="form.nom" class="input-field" placeholder="Nom complet" />
      </div>
      <div>
        <label class="mb-1.5 block text-[10px] font-black uppercase tracking-widest text-slate-500">Email</label>
        <input v-model="form.email" type="email" class="input-field" placeholder="email@exemple.com" />
      </div>
      <div>
        <label class="mb-1.5 block text-[10px] font-black uppercase tracking-widest text-slate-500">Mot de passe</label>
        <input
          v-model="form.mot_de_passe"
          type="password"
          class="input-field"
          :placeholder="editing !== 'new' ? 'Laisser vide pour conserver' : 'Saisir un mot de passe'"
        />
      </div>
      <div>
        <label class="mb-1.5 block text-[10px] font-black uppercase tracking-widest text-slate-500">Rôle</label>
        <select v-model="form.role" class="input-field">
          <option value="employee">Employé</option>
          <option value="admin">Administrateur</option>
        </select>
      </div>
      <div class="flex flex-wrap gap-2 pt-2">
        <button type="button" class="btn-primary" @click="save">Enregistrer</button>
        <button type="button" class="btn-secondary" @click="cancelEdit">Annuler</button>
      </div>
    </div>

    <div class="table-shell">
      <div v-if="loading" class="p-10 text-center text-sm text-slate-400">
        <div class="inline-block h-6 w-6 animate-spin rounded-full border-2 border-slate-200 border-t-brand-600"></div>
        <p class="mt-2 font-bold uppercase tracking-widest text-[10px]">Chargement des utilisateurs...</p>
      </div>
      <table v-else class="w-full">
        <thead class="table-head">
          <tr>
            <th>Utilisateur</th>
            <th>Email</th>
            <th>Rôle</th>
            <th class="text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
          <tr v-for="u in users" :key="u.id_personne" class="table-row">
            <td>
              <div class="flex items-center gap-3">
                <div class="h-8 w-8 rounded-full bg-slate-100 flex items-center justify-center text-[10px] font-black text-slate-500 border border-slate-200">
                  {{ u.nom?.substring(0, 2).toUpperCase() }}
                </div>
                <span class="table-cell-main">{{ u.nom }}</span>
              </div>
            </td>
            <td>
              <span class="table-cell-sub !lowercase">{{ u.email }}</span>
            </td>
            <td>
              <span class="badge" :class="u.role === 'admin' ? 'badge-info' : 'badge-success'">
                {{ u.role === 'admin' ? 'Administrateur' : 'Employé' }}
              </span>
            </td>
            <td class="text-right">
              <div class="flex items-center justify-end gap-2">
                <button type="button" class="btn-secondary !px-3 !py-1.5 !text-[10px] uppercase tracking-widest" @click="startEdit(u)">
                  Modifier
                </button>
                <button type="button" class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 transition-colors" @click="remove(u)">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                  </svg>
                </button>
              </div>
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

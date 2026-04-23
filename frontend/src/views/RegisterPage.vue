<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useToastStore } from '@/stores/toast'
import api from '@/api/client'

const router = useRouter()
const toast = useToastStore()
const loading = ref(false)
const step = ref(1)
const error = ref('')

const form = reactive({
  company_nom: '',
  company_email: '',
  company_telephone: '',
  company_adresse: '',
  nom: '',
  email: '',
  password: '',
  password_confirmation: '',
})

function nextStep() {
  error.value = ''
  if (step.value === 1) {
    if (!form.company_nom || !form.company_email) {
      error.value = 'Veuillez remplir le nom et l\'email de l\'entreprise.'
      return
    }
  }
  step.value = 2
}

function prevStep() {
  step.value = 1
}

async function submit() {
  error.value = ''
  if (!form.nom || !form.email || !form.password) {
    error.value = 'Veuillez remplir tous les champs.'
    return
  }
  if (form.password !== form.password_confirmation) {
    error.value = 'Les mots de passe ne correspondent pas.'
    return
  }

  loading.value = true
  try {
    const { data } = await api.post('/register', {
      company_nom: form.company_nom,
      company_email: form.company_email,
      company_telephone: form.company_telephone || undefined,
      company_adresse: form.company_adresse || undefined,
      nom: form.nom,
      email: form.email,
      password: form.password,
      password_confirmation: form.password_confirmation,
    })

    if (data.success) {
      toast.show(data.message, 'success')
      router.push('/login')
    }
  } catch (e) {
    error.value = e.response?.data?.message || 'Une erreur est survenue.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen bg-[#fcfcfd] flex items-center justify-center p-6 font-sans selection:bg-brand-500/10 selection:text-brand-600 overflow-hidden relative">
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
      <div class="absolute top-[-20%] left-[-10%] h-[70%] w-[70%] rounded-full bg-brand-500/[0.03] blur-[140px]"></div>
      <div class="absolute bottom-[-20%] right-[-10%] h-[70%] w-[70%] rounded-full bg-indigo-500/[0.03] blur-[140px]"></div>
      <div class="absolute inset-0 opacity-[0.015]" style="background-image: radial-gradient(#6366f1 0.5px, transparent 0.5px); background-size: 24px 24px;"></div>
    </div>

    <div class="w-full max-w-[520px] z-10">
      <div class="text-center mb-8 space-y-4">
        <RouterLink to="/" class="relative inline-flex group cursor-pointer">
          <div class="absolute inset-0 bg-brand-600 blur-2xl opacity-20 group-hover:opacity-30 transition-opacity duration-500"></div>
          <div class="relative flex h-16 w-16 items-center justify-center rounded-[1.5rem] bg-white border border-slate-100 text-brand-600 shadow-premium transition-all duration-700 hover:scale-105">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-8 w-8">
              <path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>
            </svg>
          </div>
        </RouterLink>
        <RouterLink to="/" class="block cursor-pointer">
          <h1 class="text-3xl font-black tracking-tighter"><span class="text-slate-900">STOCK</span><span class="text-brand-600">MA</span></h1>
        </RouterLink>
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.4em]">Créer votre espace</p>
      </div>

      <div class="bg-white border border-slate-100 rounded-[2rem] p-8 shadow-strong relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-transparent via-brand-500/20 to-transparent"></div>

        <div class="flex items-center justify-center gap-3 mb-8">
          <div class="flex items-center gap-2">
            <div
              class="flex h-8 w-8 items-center justify-center rounded-full text-xs font-black transition-all"
              :class="step >= 1 ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-400'"
            >
              <svg v-if="step > 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" /></svg>
              <span v-else>1</span>
            </div>
            <span class="text-[10px] font-black uppercase tracking-widest" :class="step >= 1 ? 'text-brand-600' : 'text-slate-400'">Entreprise</span>
          </div>
          <div class="h-px w-8 bg-slate-100"></div>
          <div class="flex items-center gap-2">
            <div
              class="flex h-8 w-8 items-center justify-center rounded-full text-xs font-black transition-all"
              :class="step >= 2 ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-400'"
            >
              <svg v-if="step > 2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" /></svg>
              <span v-else>2</span>
            </div>
            <span class="text-[10px] font-black uppercase tracking-widest" :class="step >= 2 ? 'text-brand-600' : 'text-slate-400'">Compte</span>
          </div>
        </div>

        <form @submit.prevent="step === 1 ? nextStep() : submit()">
          <div v-if="step === 1" class="space-y-5">
            <div class="space-y-2">
              <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 ml-1">Nom de l'entreprise *</label>
              <input v-model="form.company_nom" type="text" required placeholder="Ma Pharmacie SARL" class="input-field !py-3" />
            </div>
            <div class="space-y-2">
              <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 ml-1">Email de contact *</label>
              <input v-model="form.company_email" type="email" required placeholder="contact@pharmacie.com" class="input-field !py-3" />
            </div>
            <div class="space-y-2">
              <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 ml-1">Téléphone</label>
              <input v-model="form.company_telephone" type="text" placeholder="06 12 34 56 78" class="input-field !py-3" />
            </div>
            <div class="space-y-2">
              <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 ml-1">Adresse</label>
              <input v-model="form.company_adresse" type="text" placeholder="123 Rue de la Santé, 75001 Paris" class="input-field !py-3" />
            </div>
            <button type="submit" class="btn-primary w-full !py-4 !text-base shadow-xl shadow-brand-600/10">
              Suivant : Créer le compte →
            </button>
          </div>

          <div v-else class="space-y-5">
            <button type="button" class="flex items-center gap-2 text-[10px] font-bold text-slate-400 hover:text-brand-600 transition-colors" @click="prevStep">
              ← Retour
            </button>
            <div class="space-y-2">
              <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 ml-1">Nom complet de l'administrateur *</label>
              <input v-model="form.nom" type="text" required placeholder="Jean Dupont" class="input-field !py-3" />
            </div>
            <div class="space-y-2">
              <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 ml-1">Email *</label>
              <input v-model="form.email" type="email" required placeholder="jean.dupont@exemple.com" class="input-field !py-3" />
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div class="space-y-2">
                <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 ml-1">Mot de passe *</label>
                <input v-model="form.password" type="password" required placeholder="••••••••" class="input-field !py-3" />
              </div>
              <div class="space-y-2">
                <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 ml-1">Confirmation *</label>
                <input v-model="form.password_confirmation" type="password" required placeholder="••••••••" class="input-field !py-3" />
              </div>
            </div>

            <div v-if="error" class="flex items-center gap-3 rounded-2xl border border-rose-100 bg-rose-50 px-5 py-4 text-xs font-bold text-rose-600">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5 shrink-0"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" /></svg>
              {{ error }}
            </div>

            <button type="submit" class="btn-primary w-full !py-4 !text-base shadow-xl shadow-brand-600/10" :disabled="loading">
              <template v-if="loading">
                <div class="h-5 w-5 animate-spin rounded-full border-2 border-white/20 border-t-white"></div>
                Création...
              </template>
              <template v-else>Créer mon espace</template>
            </button>
          </div>
        </form>

        <div class="mt-6 pt-6 border-t border-slate-50 text-center">
          <p class="text-[11px] font-bold text-slate-400">
            Déjà un compte ?
            <RouterLink to="/login" class="text-brand-600 hover:text-brand-700 underline underline-offset-4 ml-1">Se connecter</RouterLink>
          </p>
        </div>
      </div>

      <p class="mt-8 text-center text-[10px] font-black uppercase tracking-[0.3em] text-slate-300">
        &copy; {{ new Date().getFullYear() }} STOCKMA. Propulsé par l'excellence.
      </p>
    </div>
  </div>
</template>

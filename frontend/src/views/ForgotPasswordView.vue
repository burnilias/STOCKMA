<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useToastStore } from '@/stores/toast'
import api from '@/api/client'

const router = useRouter()
const toast = useToastStore()
const email = ref('')
const loading = ref(false)
const submitted = ref(false)
const error = ref('')

async function submit() {
  error.value = ''
  if (!email.value) {
    error.value = 'Veuillez saisir votre adresse email.'
    return
  }

  loading.value = true
  try {
    // We assume there's a forgot-password endpoint or simulate it
    await api.post('/forgot-password', { email: email.value })
    submitted.value = true
    toast.show('Lien de réinitialisation envoyé par email.')
  } catch (e) {
    error.value = e.response?.data?.message || 'Email non reconnu ou erreur serveur.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen bg-[#fcfcfd] flex items-center justify-center p-6 font-sans selection:bg-brand-500/10 selection:text-brand-600 overflow-hidden relative">
    <!-- Sophisticated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
      <div class="absolute top-[-20%] left-[-10%] h-[70%] w-[70%] rounded-full bg-brand-500/[0.03] blur-[140px]"></div>
      <div class="absolute bottom-[-20%] right-[-10%] h-[70%] w-[70%] rounded-full bg-indigo-500/[0.03] blur-[140px]"></div>
      <div class="absolute inset-0 opacity-[0.015]" style="background-image: radial-gradient(#6366f1 0.5px, transparent 0.5px); background-size: 24px 24px;"></div>
    </div>

    <div class="w-full max-w-[460px] z-10">
      <!-- Header -->
      <div class="text-center mb-10 space-y-4">
        <div class="relative inline-flex group">
          <div class="absolute inset-0 bg-brand-600 blur-2xl opacity-20 group-hover:opacity-30 transition-opacity duration-500"></div>
          <div class="relative flex h-20 w-20 items-center justify-center rounded-[2rem] bg-white border border-slate-100 text-brand-600 shadow-premium transition-all duration-700 hover:scale-105">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-10 w-10">
              <path d="M12.378 1.602a.75.75 0 00-.756 0L3 6.632l9 5.25 9-5.25-8.622-5.03zM21.75 7.93l-9 5.25v9l8.628-5.032a.75.75 0 00.372-.648V7.93zM11.25 22.18v-9l-9-5.25v8.59a.75.75 0 00.372.648l8.628 5.033z" />
            </svg>
          </div>
        </div>
        <div class="space-y-1">
          <h1 class="text-4xl font-black tracking-tighter text-slate-900 uppercase">Stock<span class="text-brand-600">ora</span></h1>
          <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.4em]">Mot de passe oublié</p>
        </div>
      </div>

      <!-- Main Card -->
      <div class="bg-white border border-slate-100 rounded-[2.5rem] p-10 shadow-strong relative overflow-hidden group">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-transparent via-brand-500/20 to-transparent"></div>

        <div v-if="submitted" class="text-center space-y-6 py-4 animate-fade">
          <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-50 text-emerald-600 border border-emerald-100">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-8 w-8">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div class="space-y-2">
            <h2 class="text-xl font-bold text-slate-900">Email envoyé !</h2>
            <p class="text-sm text-slate-500 leading-relaxed">
              Veuillez vérifier votre boîte de réception <b>{{ email }}</b> pour réinitialiser votre mot de passe.
            </p>
          </div>
          <button @click="router.push('/login')" class="btn-primary w-full !py-4 shadow-xl shadow-brand-600/10 active:scale-95 transition-all">
            Retour à la connexion
          </button>
        </div>

        <form v-else class="space-y-8 relative z-10" @submit.prevent="submit">
          <p class="text-sm text-slate-500 leading-relaxed text-center px-4">
            Saisissez votre adresse email et nous vous enverrons un lien pour réinitialiser votre mot de passe.
          </p>

          <div class="space-y-3">
            <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 ml-1">Adresse Email</label>
            <input v-model="email" type="email" required placeholder="nom@entreprise.com" class="input-field !py-4" />
          </div>

          <div v-if="error" class="flex items-center gap-3 rounded-2xl border border-rose-100 bg-rose-50 px-5 py-4 text-xs font-bold text-rose-600 animate-fade">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5 shrink-0">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
            </svg>
            {{ error }}
          </div>

          <button type="submit" class="btn-primary w-full !py-4 !text-base shadow-xl shadow-brand-600/10 active:scale-95 transition-all" :disabled="loading">
            <template v-if="loading">
              <div class="h-5 w-5 animate-spin rounded-full border-2 border-white/20 border-t-white"></div>
              Envoi...
            </template>
            <template v-else>Envoyer le lien</template>
          </button>

          <div class="pt-2 text-center">
            <RouterLink to="/login" class="text-[11px] font-black uppercase tracking-widest text-slate-400 hover:text-brand-600 transition-colors">Retour à la connexion</RouterLink>
          </div>
        </form>
      </div>
      
      <p class="mt-12 text-center text-[10px] font-black uppercase tracking-[0.3em] text-slate-300">
        &copy; {{ new Date().getFullYear() }} STOCKMA Cloud. Système de Gestion Sécurisé.
      </p>
    </div>
  </div>
</template>

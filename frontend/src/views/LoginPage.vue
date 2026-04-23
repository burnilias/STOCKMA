<script setup>
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const email = ref('admin@stock.com')
const password = ref('password')
const loading = ref(false)
const error = ref('')

const auth = useAuthStore()
const router = useRouter()
const route = useRoute()

async function submit() {
  error.value = ''
  loading.value = true
  try {
    await auth.login(email.value, password.value)
    const redirect = route.query.redirect || '/dashboard'
    router.push(redirect)
  } catch (e) {
    const msg =
      e.response?.data?.message ||
      e.response?.data?.errors?.email?.[0] ||
      'Email ou mot de passe incorrect'
    error.value = msg
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
      
      <!-- Subtle Grid Pattern -->
      <div class="absolute inset-0 opacity-[0.015]" style="background-image: radial-gradient(#6366f1 0.5px, transparent 0.5px); background-size: 24px 24px;"></div>
    </div>

    <div class="w-full max-w-[420px] z-10">
      <!-- Header with refined branding -->
      <div class="text-center mb-10 space-y-6">
        <RouterLink to="/" class="relative inline-flex group cursor-pointer">
          <div class="absolute inset-0 bg-brand-600 blur-2xl opacity-20 group-hover:opacity-30 transition-opacity duration-500"></div>
          <div class="relative flex h-20 w-20 items-center justify-center rounded-[2rem] bg-white border border-slate-100 text-brand-600 shadow-premium transition-all duration-700 hover:scale-105">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-10 w-10">
              <path d="M12.378 1.602a.75.75 0 00-.756 0L3 6.632l9 5.25 9-5.25-8.622-5.03zM21.75 7.93l-9 5.25v9l8.628-5.032a.75.75 0 00.372-.648V7.93zM11.25 22.18v-9l-9-5.25v8.59a.75.75 0 00.372.648l8.628 5.033z" />
            </svg>
          </div>
        </RouterLink>

        <div class="space-y-2">
          <RouterLink to="/" class="block">
            <h1 class="text-4xl font-black tracking-tighter italic uppercase"><span class="text-slate-900 not-italic">STOCK</span><span class="text-brand-600 not-italic">MA</span></h1>
          </RouterLink>
          <div class="flex items-center justify-center gap-3">
            <span class="h-px w-6 bg-slate-200"></span>
            <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.4em]">Inventory Intelligence</p>
            <span class="h-px w-6 bg-slate-200"></span>
          </div>
        </div>
      </div>

      <!-- Main Login Card -->
      <div class="bg-white border border-slate-100 rounded-[2rem] p-10 shadow-strong relative overflow-hidden group">
        <!-- Top accent line -->
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-transparent via-brand-500/20 to-transparent"></div>

        <form class="space-y-6 relative z-10" @submit.prevent="submit">
          <div class="space-y-2">
            <label class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-500 ml-1">Identifiant d'accès</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-slate-400 group-focus-within:text-brand-500 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                </svg>
              </div>
              <input
                v-model="email"
                type="email"
                required
                placeholder="nom@entreprise.com"
                class="input-field !pl-10 !py-3 !text-sm"
                autocomplete="username"
              />
            </div>
          </div>

          <div class="space-y-2">
            <div class="flex items-center justify-between ml-1">
              <label class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-500">Clé de sécurité</label>
              <RouterLink to="/forgot-password" class="text-[9px] font-black uppercase tracking-widest text-brand-600 hover:text-brand-700 transition-colors underline-offset-4 hover:underline">Accès perdu ?</RouterLink>
            </div>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-slate-400 group-focus-within:text-brand-500 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                </svg>
              </div>
              <input
                v-model="password"
                type="password"
                required
                placeholder="••••••••••••"
                class="input-field !pl-10 !py-3 !text-sm"
                autocomplete="current-password"
              />
            </div>
          </div>

          <div v-if="error" class="flex items-center gap-3 rounded-xl border border-rose-100 bg-rose-50 px-4 py-3 text-[11px] font-bold text-rose-600 animate-fade">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 shrink-0">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
            </svg>
            {{ error }}
          </div>

          <button type="submit" class="btn-primary w-full !py-4 !text-sm shadow-xl shadow-brand-600/10 active:scale-95 transition-all" :disabled="loading">
            <template v-if="loading">
              <div class="h-4 w-4 animate-spin rounded-full border-2 border-white/20 border-t-white"></div>
              Authentification...
            </template>
            <template v-else>Se connecter</template>
          </button>
        </form>

        <!-- Subtle footer inside card -->
        <div class="mt-8 pt-6 border-t border-slate-50 flex items-center justify-center gap-6">
          <div class="flex items-center gap-2">
            <div class="h-1.5 w-1.5 rounded-full bg-emerald-500"></div>
            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Serveur Actif</span>
          </div>
          <div class="flex items-center gap-2">
            <div class="h-1.5 w-1.5 rounded-full bg-brand-500"></div>
            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">v2.4.0-pro</span>
          </div>
        </div>

        <div class="mt-6 pt-4 border-t border-slate-100 text-center">
          <p class="text-[11px] font-bold text-slate-400">
            Pas de compte ?
            <RouterLink to="/register" class="text-brand-600 hover:text-brand-700 underline underline-offset-4 ml-1 font-black">Créer un espace</RouterLink>
          </p>
        </div>
      </div>
      
      <!-- External Footer -->
      <p class="mt-8 text-center text-[9px] font-black uppercase tracking-[0.3em] text-slate-300">
        &copy; {{ new Date().getFullYear() }} STOCKMA Cloud. Système de Gestion Sécurisé.
      </p>
    </div>
  </div>
</template>

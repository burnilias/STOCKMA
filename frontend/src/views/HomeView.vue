<script setup>
import { RouterLink } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
</script>

<template>
  <div class="min-h-screen bg-bg font-sans text-ink">
    <!-- Header -->
    <header class="sticky top-0 z-50 border-b border-slate-100 bg-white/70 backdrop-blur-xl">
      <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-2">
          <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-600 text-white shadow-lg shadow-brand-600/20">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2.5"
              stroke-linecap="round"
              stroke-linejoin="round"
              class="h-6 w-6"
            >
              <path d="m7.5 4.27 9 5.15" />
              <path
                d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"
              />
              <path d="m3.3 7 8.7 5 8.7-5" />
              <path d="M12 22V12" />
            </svg>
          </div>
          <span class="text-xl font-black tracking-tight uppercase"><span class="text-slate-900">STOCK</span><span class="text-brand-600">MA</span></span>
        </div>
        <nav class="flex items-center gap-6">
          <template v-if="auth.isAuthenticated">
            <RouterLink
              to="/dashboard"
              class="btn-primary !px-5 !py-2.5 !text-xs uppercase tracking-widest"
            >
              Tableau de bord
            </RouterLink>
          </template>
          <template v-else>
            <RouterLink
              to="/login"
              class="text-xs font-black uppercase tracking-widest text-slate-500 hover:text-brand-600 transition-colors"
            >
              Connexion
            </RouterLink>
            <RouterLink
              to="/register"
              class="btn-primary !px-6 !py-2.5 !text-xs uppercase tracking-widest"
            >
              S'inscrire
            </RouterLink>
          </template>
        </nav>
      </div>
    </header>

    <!-- Hero Section -->
    <main>
      <section class="relative overflow-hidden pt-24 pb-20 sm:pt-32 sm:pb-32">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <div class="lg:grid lg:grid-cols-12 lg:gap-16 items-center">
            <div class="sm:text-center md:mx-auto md:max-w-2xl lg:col-span-6 lg:text-left">
              <div class="inline-flex items-center gap-2 rounded-full bg-brand-50 px-3 py-1 text-[10px] font-black uppercase tracking-widest text-brand-600 mb-6 border border-brand-100">
                <span class="relative flex h-2 w-2">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-2 w-2 bg-brand-500"></span>
                </span>
                Version 2.0 disponible
              </div>
              <h1 class="text-5xl font-black tracking-tight text-slate-900 sm:text-6xl md:text-7xl leading-[1.1]">
                Gérez votre stock avec <span class="text-brand-600">précision.</span>
              </h1>
              <p class="mt-8 text-lg text-slate-500 font-medium leading-relaxed sm:text-xl">
                Suivez vos produits, gérez vos entrées et sorties, et évitez les ruptures grâce à une interface moderne, fluide et intelligente.
              </p>
              <div class="mt-10 flex flex-col gap-4 sm:flex-row sm:justify-center lg:justify-start">
                <RouterLink
                  to="/register"
                  class="btn-primary !px-10 !py-5 !text-base"
                >
                  Créer mon espace
                </RouterLink>
                <RouterLink
                  to="/login"
                  class="btn-secondary !px-10 !py-5 !text-base"
                >
                  Se connecter
                </RouterLink>
              </div>
            </div>
            <div class="relative mt-16 sm:mx-auto sm:max-w-xl lg:col-span-6 lg:mx-0 lg:mt-0">
              <div class="relative mx-auto w-full rounded-[2.5rem] border border-slate-100 bg-white p-3 shadow-strong">
                <div class="overflow-hidden rounded-[2rem] bg-slate-50 border border-slate-100">
                  <div class="h-6 w-full bg-white border-b border-slate-100 flex items-center px-4 gap-1.5">
                    <div class="h-2 w-2 rounded-full bg-slate-200"></div>
                    <div class="h-2 w-2 rounded-full bg-slate-200"></div>
                    <div class="h-2 w-2 rounded-full bg-slate-200"></div>
                  </div>
                  <img
                    src="https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?auto=format&fit=crop&q=80&w=2000"
                    alt="Gestion d'entrepôt"
                    class="w-full h-auto object-cover opacity-90"
                  />
                </div>
              </div>
              <!-- Decorative elements -->
              <div class="absolute -top-6 -right-6 h-32 w-32 rounded-full bg-brand-500/10 blur-3xl"></div>
              <div class="absolute -bottom-10 -left-10 h-40 w-40 rounded-full bg-brand-600/5 blur-3xl"></div>
            </div>
          </div>
        </div>
      </section>

      <!-- Preview / Stats Section -->
      <section class="py-24 bg-slate-50/50 border-y border-slate-100">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
            <div class="space-y-8">
              <h2 class="text-4xl font-black tracking-tight text-slate-900 leading-tight">
                Une interface conçue pour la <span class="text-brand-600">performance.</span>
              </h2>
              <p class="text-lg text-slate-500 font-medium leading-relaxed">
                Visualisez vos stocks en un coup d'œil. Notre système de badges intelligents et nos tables haute-densité vous permettent de prendre des décisions plus rapidement.
              </p>
              <div class="flex flex-col gap-4">
                <div class="flex items-center gap-4 p-4 rounded-2xl bg-white border border-slate-100 shadow-sm">
                  <div class="h-10 w-10 rounded-xl bg-brand-50 flex items-center justify-center text-brand-600">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-6 w-6">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                    </svg>
                  </div>
                  <div>
                    <p class="text-sm font-bold text-slate-900">Rapidité d'exécution</p>
                    <p class="text-xs text-slate-500 font-medium">Temps de réponse inférieur à 100ms</p>
                  </div>
                </div>
                <div class="flex items-center gap-4 p-4 rounded-2xl bg-white border border-slate-100 shadow-sm">
                  <div class="h-10 w-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-6 w-6">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </div>
                  <div>
                    <p class="text-sm font-bold text-slate-900">Précision Inventaire</p>
                    <p class="text-xs text-slate-500 font-medium">Synchronisation temps réel garantie</p>
                  </div>
                </div>
              </div>
            </div>

            <div class="table-shell !shadow-strong rotate-1 hover:rotate-0 transition-transform duration-700">
              <div class="p-4 border-b border-slate-100 bg-white flex items-center justify-between">
                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Aperçu du Stock</span>
                <span class="badge badge-success">Système OK</span>
              </div>
              <table class="w-full">
                <thead class="table-head">
                  <tr>
                    <th>Produit</th>
                    <th class="text-right">Stock</th>
                    <th>Statut</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                  <tr class="table-row">
                    <td>
                      <p class="table-cell-main">MacBook Pro M3</p>
                      <p class="table-cell-sub">Apple Inc.</p>
                    </td>
                    <td class="text-right">
                      <span class="table-cell-highlight">12</span>
                    </td>
                    <td>
                      <span class="badge badge-success">En Stock</span>
                    </td>
                  </tr>
                  <tr class="table-row">
                    <td>
                      <p class="table-cell-main">iPhone 15 Pro</p>
                      <p class="table-cell-sub">Apple Inc.</p>
                    </td>
                    <td class="text-right">
                      <span class="table-cell-highlight text-rose-600">2</span>
                    </td>
                    <td>
                      <span class="badge badge-error">Critique</span>
                    </td>
                  </tr>
                  <tr class="table-row">
                    <td>
                      <p class="table-cell-main">Magic Mouse</p>
                      <p class="table-cell-sub">Accessoires</p>
                    </td>
                    <td class="text-right">
                      <span class="table-cell-highlight text-amber-600">5</span>
                    </td>
                    <td>
                      <span class="badge badge-warning">Faible</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </section>

      <!-- Features Section -->
      <section id="features" class="bg-white py-24 sm:py-32">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <div class="mx-auto max-w-2xl text-center">
            <h2 class="text-xs font-black uppercase tracking-[0.2em] text-brand-600 mb-4">Fonctionnalités</h2>
            <p class="text-4xl font-black tracking-tight text-slate-900 sm:text-5xl leading-tight">
              Tout ce dont vous avez besoin pour votre inventaire.
            </p>
          </div>
          <div class="mx-auto mt-16 max-w-2xl sm:mt-20 lg:mt-24 lg:max-w-none">
            <dl class="grid max-w-xl grid-cols-1 gap-x-12 gap-y-16 lg:max-w-none lg:grid-cols-3">
              <div class="flex flex-col group">
                <dt class="flex items-center gap-x-4 text-xl font-bold leading-7 text-slate-900">
                  <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-50 text-brand-600 border border-slate-100 group-hover:bg-brand-600 group-hover:text-white transition-all duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                  </div>
                  Gestion des Produits
                </dt>
                <dd class="mt-4 flex flex-auto flex-col text-base leading-7 text-slate-500 font-medium">
                  <p class="flex-auto">Organisez vos produits par catégories, unités et suivez les niveaux de stock en temps réel avec une précision chirurgicale.</p>
                </dd>
              </div>
              <div class="flex flex-col group">
                <dt class="flex items-center gap-x-4 text-xl font-bold leading-7 text-slate-900">
                  <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-50 text-brand-600 border border-slate-100 group-hover:bg-brand-600 group-hover:text-white transition-all duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 14 4-4"/><path d="m3.34 19 3.07-5.35a2 2 0 0 1 1.72-1h3.74a2 2 0 0 0 1.72-1L16.66 6.3a2 2 0 0 1 1.72-1h2.74"/><path d="m8 2 1.8 1.8a2 2 0 0 0 2.8 0L14.4 2"/><path d="M21 15a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2h2"/><path d="M14 17v5"/><path d="M9 22h6"/></svg>
                  </div>
                  Mouvements de Stock
                </dt>
                <dd class="mt-4 flex flex-auto flex-col text-base leading-7 text-slate-500 font-medium">
                  <p class="flex-auto">Enregistrez facilement vos entrées et sorties de marchandises avec un historique complet et traçable à tout moment.</p>
                </dd>
              </div>
              <div class="flex flex-col group">
                <dt class="flex items-center gap-x-4 text-xl font-bold leading-7 text-slate-900">
                  <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-50 text-brand-600 border border-slate-100 group-hover:bg-brand-600 group-hover:text-white transition-all duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20v-6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2Z"/><path d="M22 20v-6a2 2 0 0 0-2-2h-6a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2Z"/><path d="M12 10V4a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2Z"/></svg>
                  </div>
                  Alertes de Stock Bas
                </dt>
                <dd class="mt-4 flex flex-auto flex-col text-base leading-7 text-slate-500 font-medium">
                  <p class="flex-auto">Soyez alerté instantanément dès qu'un produit atteint son seuil critique pour ne plus jamais rater une vente.</p>
                </dd>
              </div>
            </dl>
          </div>
        </div>
      </section>
    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-100 bg-bg py-16">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col items-center justify-between gap-8 sm:flex-row">
          <div class="flex items-center gap-2">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-600 text-white">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
            </div>
            <span class="text-base font-black tracking-tight uppercase"><span class="text-slate-900">STOCK</span><span class="text-brand-600">MA</span></span>
          </div>
          <p class="text-xs font-black uppercase tracking-widest text-slate-400">
            &copy; {{ new Date().getFullYear() }} STOCKMA. Propulsé par l'excellence.
          </p>
        </div>
      </div>
    </footer>
  </div>
</template>

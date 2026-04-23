<script setup>
import { RouterLink } from 'vue-router'
</script>

<template>
  <div class="flex min-h-[60vh] flex-col items-center justify-center px-4 text-center">
    <div class="mb-8 flex h-20 w-20 items-center justify-center rounded-3xl bg-rose-50 text-rose-500 shadow-sm border border-rose-100">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-10 w-10">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0-10.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.248-8.25-3.286zm0 13.036h.008v.008H12v-.008z" />
      </svg>
    </div>
    <p class="font-mono text-6xl font-black tabular-nums text-slate-200">403</p>
    <h1 class="mt-4 text-2xl font-bold text-slate-900 tracking-tight uppercase">Accès restreint</h1>
    <p class="mt-2 max-w-md text-sm leading-relaxed text-slate-500 font-medium">
      Vous n’avez pas les droits nécessaires pour consulter cette page. Contactez un administrateur pour obtenir les accès.
    </p>
    <div class="mt-10 flex gap-4">
      <RouterLink to="/dashboard" class="btn-primary"> 
        Aller au tableau de bord 
      </RouterLink>
      <button @click="$router.back()" class="btn-secondary"> 
        Retourner
      </button>
    </div>
  </div>
</template>

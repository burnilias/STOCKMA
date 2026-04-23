<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue'
import { RouterLink, RouterView, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useToastStore } from '@/stores/toast'
import NavIcon from '@/components/NavIcon.vue'

const auth = useAuthStore()
const toast = useToastStore()
const route = useRoute()

const sidebarOpen = ref(false)

const pageTitles = {
  dashboard: 'Tableau de bord',
  'stock-status': 'État des stocks',
  products: 'Produits',
  'product-new': 'Nouveau produit',
  'product-edit': 'Modifier le produit',
  'stock-entry': 'Entrée de stock',
  'stock-exit': 'Sortie de stock',
  categories: 'Catégories',
  alertes: 'Alertes de stock',
  movements: 'Historique des mouvements',
  'low-stock': 'Stocks bas',
  users: 'Utilisateurs',
}

const pageTitle = computed(() => pageTitles[route.name] ?? 'Application')

const nav = computed(() => {
  const items = [
    { to: '/dashboard', label: 'Tableau de bord', icon: 'dashboard' },
    { to: '/stock-status', label: 'État des stocks', icon: 'chart' },
    { to: '/products', label: 'Produits', icon: 'cube' },
    { to: '/stock-entry', label: 'Entrée stock', icon: 'arrow-down-tray' },
    { to: '/stock-exit', label: 'Sortie stock', icon: 'arrow-up-tray' },
    { to: '/movements', label: 'Historique', icon: 'list' },
    { to: '/low-stock', label: 'Stocks bas', icon: 'warning' },
  ]
  if (auth.isAdmin) {
    items.push(
      { to: '/categories', label: 'Catégories', icon: 'tag' },
      { to: '/alertes', label: 'Alertes', icon: 'bell' },
      { to: '/users', label: 'Utilisateurs', icon: 'users' },
    )
  }
  if (auth.isSuperAdmin) {
    items.push(
      { to: '/super-admin', label: 'Super Admin', icon: 'shield' },
    )
  }
  return items
})

function navActive(path) {
  return route.path === path || route.path.startsWith(`${path}/`)
}

async function handleLogout() {
  await auth.logout()
  window.location.href = '/login'
}

// Close sidebar on route change (mobile)
const closeSidebar = () => {
  sidebarOpen.value = false
}

// Handle window resize
const handleResize = () => {
  if (window.innerWidth >= 1024) {
    sidebarOpen.value = false
  }
}

onMounted(() => {
  window.addEventListener('resize', handleResize)
})

onUnmounted(() => {
  window.removeEventListener('resize', handleResize)
})
</script>

<template>
  <div class="min-h-screen flex bg-bg font-sans selection:bg-brand-500/10 selection:text-brand-600 overflow-hidden">
    <!-- Mobile Sidebar Overlay -->
    <Transition name="fade">
      <div 
        v-if="sidebarOpen" 
        class="fixed inset-0 z-40 bg-slate-900/40 backdrop-blur-sm lg:hidden"
        @click="sidebarOpen = false"
      ></div>
    </Transition>

    <!-- Sidebar Light Indigo -->
    <aside
      class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col border-r border-slate-200 bg-white shadow-strong transition-transform duration-500 ease-out lg:static lg:w-64 lg:translate-x-0 lg:shadow-none"
      :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    >
      <div class="px-6 py-8">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-600 text-white shadow-lg shadow-brand-600/20">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6">
                <path d="M12.378 1.602a.75.75 0 00-.756 0L3 6.632l9 5.25 9-5.25-8.622-5.03zM21.75 7.93l-9 5.25v9l8.628-5.032a.75.75 0 00.372-.648V7.93zM11.25 22.18v-9l-9-5.25v8.59a.75.75 0 00.372.648l8.628 5.033z" />
              </svg>
            </div>
            <div>
              <span class="block text-base font-black tracking-tight"><span class="text-slate-900">STOCK</span><span class="text-brand-600">MA</span></span>
              <span class="text-[10px] font-bold uppercase tracking-widest text-brand-600">Inventory</span>
              <p v-if="auth.isSuperAdmin" class="text-[9px] font-black text-slate-400 mt-0.5">Vue Globale</p>
              <p v-else-if="auth.company" class="text-[9px] font-bold text-slate-400 mt-0.5 truncate max-w-[140px]">{{ auth.company.nom }}</p>
            </div>
          </div>
          <button @click="sidebarOpen = false" class="lg:hidden p-2 text-slate-400 hover:text-slate-600">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="h-6 w-6">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>

      <nav class="flex-1 space-y-1 px-4 overflow-y-auto custom-scrollbar">
        <RouterLink
          v-for="item in nav"
          :key="item.to"
          :to="item.to"
          class="nav-item group"
          @click="closeSidebar"
          :class="navActive(item.to) ? 'nav-item-active' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900'"
        >
          <NavIcon :name="item.icon" class="h-5 w-5 transition-transform group-hover:scale-110" />
          <span>{{ item.label }}</span>
        </RouterLink>
      </nav>

      <div class="p-4 mt-auto border-t border-slate-100 bg-slate-50/50">
        <div class="mb-6 rounded-2xl bg-white p-4 border border-slate-100 shadow-sm">
          <div class="flex items-center gap-3">
            <div class="h-9 w-9 rounded-xl bg-brand-50 flex items-center justify-center text-xs font-black text-brand-600 border border-brand-100">
              {{ auth.user?.nom?.substring(0, 2).toUpperCase() }}
            </div>
            <div class="min-w-0 flex-1">
              <p class="truncate text-xs font-black text-slate-900">{{ auth.user?.nom }}</p>
              <p class="truncate text-[10px] text-brand-600 font-black uppercase tracking-wider">{{ auth.user?.role }}</p>
            </div>
          </div>
        </div>
        <button
          type="button"
          class="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-xs font-black uppercase tracking-widest text-slate-500 transition-all hover:bg-rose-50 hover:text-rose-600"
          @click="handleLogout"
        >
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="h-5 w-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
          </svg>
          Déconnexion
        </button>
      </div>
    </aside>

    <!-- Main Area -->
    <div class="flex min-w-0 flex-1 flex-col overflow-hidden">
      <header
        class="sticky top-0 z-30 flex h-20 shrink-0 items-center justify-between bg-bg/80 px-4 sm:px-6 lg:px-10 backdrop-blur-xl"
      >
        <div class="flex items-center gap-4">
          <button 
            @click="sidebarOpen = true" 
            class="lg:hidden p-2 rounded-xl bg-white border border-slate-200 text-slate-500 hover:text-brand-600 shadow-sm transition-all"
          >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="h-6 w-6">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
          </button>
          <h1 class="text-[10px] sm:text-xs font-black uppercase tracking-[0.2em] text-slate-400 truncate max-w-[150px] sm:max-w-none">
            {{ pageTitle }}
          </h1>
        </div>
        <div class="flex items-center gap-3 sm:gap-6">
          <div class="hidden md:flex items-center gap-2 rounded-full bg-white border border-slate-200 px-4 py-2 shadow-sm">
            <span class="relative flex h-2 w-2">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
            </span>
            <span class="text-[10px] font-black uppercase tracking-widest text-emerald-600">Online</span>
          </div>
          <button class="relative rounded-xl bg-white p-2 sm:p-2.5 text-slate-400 hover:text-brand-600 transition-all border border-slate-200 shadow-sm">
            <NavIcon name="bell" class="h-5 w-5" />
            <span class="absolute top-2 right-2 flex h-2 w-2 rounded-full bg-rose-500 ring-2 ring-white"></span>
          </button>
        </div>
      </header>

      <main class="flex-1 overflow-y-auto px-4 sm:px-6 lg:px-10 py-6 sm:py-8 custom-scrollbar">
        <div class="mx-auto max-w-7xl w-full">
          <RouterView v-slot="{ Component }">
            <Transition name="page" mode="out-in">
              <component :is="Component" />
            </Transition>
          </RouterView>
        </div>
      </main>
    </div>

    <!-- Premium Toast Light -->
    <Transition name="toast">
      <div
        v-if="toast.visible"
        class="fixed bottom-6 right-6 sm:bottom-10 sm:right-10 z-[60] flex items-center gap-4 rounded-2xl border border-slate-100 bg-white p-4 sm:p-5 shadow-strong backdrop-blur-xl max-w-[calc(100vw-3rem)]"
      >
        <div 
          class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl shadow-sm"
          :class="{
            'bg-emerald-50 text-emerald-600': toast.type === 'success',
            'bg-rose-50 text-rose-600': toast.type === 'error',
            'bg-brand-50 text-brand-600': toast.type === 'info',
          }"
        >
          <svg v-if="toast.type === 'success'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-6 w-6">
            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
          </svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-6 w-6">
            <path d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" />
          </svg>
        </div>
        <span class="text-xs sm:text-sm font-bold text-slate-900">{{ toast.message }}</span>
      </div>
    </Transition>
  </div>
</template>

<style scoped>
.page-enter-active,
.page-leave-active {
  transition: opacity 0.3s ease, transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
.page-enter-from {
  opacity: 0;
  transform: translateY(8px);
}
.page-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}

.toast-enter-active,
.toast-leave-active {
  transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}
.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateX(24px) scale(0.95);
}

.fade-enter-active, .fade-leave-active {
  transition: opacity 0.4s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #cbd5e1;
}
</style>

<style scoped>
.page-enter-active,
.page-leave-active {
  transition: opacity 0.3s ease, transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
.page-enter-from {
  opacity: 0;
  transform: translateY(8px);
}
.page-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}

.toast-enter-active,
.toast-leave-active {
  transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}
.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateX(24px) scale(0.95);
}
</style>

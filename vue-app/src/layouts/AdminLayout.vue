<!-- eslint-disable @typescript-eslint/no-unused-vars -->
<script setup lang="ts">
import AdminNavbarView from '@/views/admin/components/AdminNavbarView.vue'
import AdminSideBarView from '@/views/admin/components/AdminSideBarView.vue'
import { RouterLink, RouterView } from 'vue-router'
</script>

<template>
  <div class="w-full h-screen overflow-hidden flex relative font-afacad bg-slate-100">
    <aside>
      <div class="w-70 max-w-full bg-white border-r border-slate-300 h-screen z-40">
        <AdminSideBarView class="w-full" />
      </div>
    </aside>
    <div class="w-full overflow-y-auto relative">
      <header
        class="sticky z-40 top-0 w-full bg-linear-65 from-orange-400 to-orange-600 shadow flex justify-center items-center"
      >
        <AdminNavbarView />
      </header>
      <RouterView />
    </div>
  </div>
</template>

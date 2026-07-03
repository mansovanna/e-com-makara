<script setup lang="ts">
import IconNotification from '@/components/icons/IconNotification.vue'
import IconSearch from '@/components/icons/IconSearch.vue'
import IconShopping from '@/components/icons/IconShopping.vue'
import IconSupport from '@/components/icons/IconSupport.vue'
import IconUser from '@/components/icons/IconUser.vue'
import { useAuthStore } from '@/stores/auth_store'
import { LoadingIcon } from '@/stores/icon'
import { IconLogout } from '@tabler/icons-vue'
const authStore = useAuthStore()
</script>

<template>
  <!-- bg-linear-to-r from-indigo-500 from-10% via-sky-500 via-30% to-emerald-500 to-90% -->
  <div class="w-full p-3 flex justify-end items-center">
    <!-- image -->

    <!-- End Image logo -->
    <nav>
      <div class="flex justify-end items-center gap-4">
        <!-- Block Search -->
        <div v-if="false" class="w-100 relative">
          <div class="absolute top-0 bottom-0 p-2 flex justify-center items-center text-white">
            <component :is="IconSearch" class="size-5" />
          </div>
          <input
            type="text"
            placeholder="Search now!"
            class="w-full bg-white/30 p-2 pl-8 px-6 text-white text-md rounded-full focus:outline-orange-500 focus:ring-2 focus:ring-white/60"
          />
        </div>
        <!-- Block Search -->

        <!-- Block notification -->
        <div class="relative group">
          <div
            class="size-10 bg-white/40 rounded-lg animate-pulse group-hover:scale-105 duration-500 ease-in-out"
          ></div>
          <button
            class="absolute top-0 cursor-pointer right-0 bottom-0 left-0 text-white flex justify-center items-center"
          >
            <component class="size-5" :is="IconNotification" />
          </button>
          <!-- spn not -->
          <span
            class="bg-radial bg-red-500 border border-white font-semibold text-white px-1 text-xs font-open-sans rounded-full absolute -top-1 left-5"
            >0</span
          >
        </div>
        <!-- Block notification -->

        <!-- Block Card Shoppong -->
        <div class="relative group">
          <div
            class="size-10 bg-white/40 rounded-lg animate-pulse group-hover:scale-105 duration-500 ease-in-out"
          ></div>
          <button
            class="absolute top-0 cursor-pointer right-0 bottom-0 left-0 text-white flex justify-center items-center"
          >
            <component class="size-5" :is="IconShopping" />
          </button>
          <!-- spn not -->
          <span
            class="bg-radial bg-red-500 border border-white font-semibold text-white px-1 text-xs font-open-sans rounded-full absolute -top-1 left-5"
            >0</span
          >
        </div>
        <!-- Block Card Shoppong -->

        <!-- Block Favorit -->
        <div class="relative group">
          <div
            class="size-10 bg-white/40 rounded-lg animate-pulse group-hover:scale-105 duration-500 ease-in-out"
          ></div>
          <button
            class="absolute top-0 cursor-pointer right-0 bottom-0 left-0 text-white flex justify-center items-center"
          >
            <component class="size-5" :is="IconSupport" />
          </button>
          <!-- spn not -->
          <span
            class="bg-radial bg-red-500 border border-white font-semibold text-white px-1 text-xs font-open-sans rounded-full absolute -top-1 left-5"
            >0</span
          >
        </div>
        <!-- Block Card Favorit -->

        <!-- Block user -->
        <div class="relative group">
          <div
            class="size-10 bg-white/40 rounded-lg animate-pulse group-hover:scale-105 duration-500 ease-in-out"
          ></div>
          <button
            class="absolute top-0 cursor-pointer right-0 bottom-0 left-0 text-white flex justify-center items-center"
          >
            <component class="size-7" :is="IconUser" />
          </button>
          <!-- spn not -->
        </div>
        <!-- End Block user -->

        <!-- Block user -->
        <div class="relative group">
          <div
            class="size-10 bg-white/40 rounded-lg animate-pulse group-hover:scale-105 duration-500 ease-in-out"
          ></div>
          <button
            @click="authStore.logout"
            :disabled="authStore.isLoading"
            class="absolute top-0 cursor-pointer right-0 bottom-0 left-0 text-white flex justify-center items-center"
          >
            <component v-if="authStore.isLoading" class="size-7" :is="LoadingIcon" />
            <component v-else class="size-7" :is="IconLogout" />
          </button>
          <!-- spn not -->
        </div>
        <!-- End Block user -->
      </div>
    </nav>
  </div>
</template>

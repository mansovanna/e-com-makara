<!-- eslint-disable vue/multi-word-component-names -->
<script setup lang="ts">
import Card_products_info from '@/components/utils/card_products_info.vue'
import { BackIcon } from '@/stores/icon'
import { useShoppStore } from '@/stores/shopp_store'

const card = useShoppStore()
</script>

<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <main class="w-full flex flex-col items-center justify-start">
    <div class="w-2/3 py-6 flex flex-col justify-center items-start gap-6">
      <!-- Header -->
      <div class="w-full flex justify-between items-center">
        <h1 class="text-xl font-semibold text-gray-800 font-poppins">Favorite</h1>
        <button
          @click="$router.push({ name: 'home' })"
          class="px-4 py-2 flex justify-center items-center gap-2 bg-orange-500 text-white rounded-md hover:bg-orange-600 cursor-pointer transition"
        >
          <component :is="BackIcon" />
          <span>Back</span>
        </button>
      </div>

      <!-- End Start menu Catagories -->
      <div class="w-full flex flex-col gap-4">
        <div v-if="card.favoriteProducts" class="grid grid-cols-3 gap-4">
          <div v-for="(item, index) in card.favoriteProducts" :key="index">
            <Card_products_info :data="item" />
          </div>
        </div>

        <!-- data is empty -->
        <div
          class="w-full flex justify-center items-center p-4 "
          v-if="!card.favoriteProducts.length"
        >
          <p>No foods available.</p>
        </div>
        <!-- End data is empty -->
      </div>
      <div class="h-20"></div>
    </div>
  </main>
</template>

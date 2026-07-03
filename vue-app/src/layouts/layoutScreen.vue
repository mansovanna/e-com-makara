<!-- eslint-disable @typescript-eslint/no-unused-vars -->
<script setup lang="ts">
import IconNotification from '@/components/icons/IconNotification.vue'
import IconSearch from '@/components/icons/IconSearch.vue'
import IconShopping from '@/components/icons/IconShopping.vue'
import IconSupport from '@/components/icons/IconSupport.vue'
import IconUser from '@/components/icons/IconUser.vue'
import { useShoppStore } from '@/stores/shopp_store'
import { computed } from 'vue'
import { RouterLink, RouterView } from 'vue-router'

const shopStore = useShoppStore()

// count product in cart
const computedCartCount = computed(() => {
  return shopStore.products.reduce((total, product) => total + product.quantity, 0)
})

const computedFavoriteCount = computed(() => {
  return shopStore.favoriteProducts.length
})

const computedNotification = computed(() => {
  return shopStore.recepts.length
})
</script>

<template>
  <div class="w-full h-screen overflow-y-auto relative font-afacad bg-slate-100 flex flex-col">
    <header
      class="sticky z-50 top-0 w-full bg-linear-65 from-orange-400 to-orange-600 shadow flex justify-center items-center"
    >
      <!-- bg-linear-to-r from-indigo-500 from-10% via-sky-500 via-30% to-emerald-500 to-90% -->
      <div class="w-2/3 max-lg:w-full p-3 flex justify-between items-center">
        <!-- image -->
        <div class="flex justify-start items-center gap-2">
          <div class="size-10 overflow-clip border border-white rounded-full bg-orange-500">
            <img
              class="rounded-full"
              src="https://img.pikbest.com/png-images/20241111/-22creative-food-logo-collection-for-culinary-brands-22_11079861.png!sw800"
              alt=""
            />
          </div>
          <div class="flex justify-start items-center gap-1">
            <h1 class="font-black text-3xl text-white drop-shadow">Good Food</h1>
          </div>
        </div>
        <!-- End Image logo -->
        <nav>
          <div class="flex justify-end items-center gap-4">
            <!-- Block Search -->
            <div hidden class="w-100 relative">
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
                @click="$router.push({ name: 'notification' })"
                class="absolute top-0 cursor-pointer right-0 bottom-0 left-0 text-white flex justify-center items-center"
              >
                <component class="size-5" :is="IconNotification" />
              </button>
              <!-- spn not -->
              <span
                class="bg-radial bg-red-500 border border-white font-semibold text-white px-1 text-xs font-open-sans rounded-full absolute -top-1 left-5"
                >{{ computedNotification ?? 0 }}</span
              >
            </div>
            <!-- Block notification -->

            <!-- Block Card Shoppong -->
            <div class="relative group">
              <div
                class="size-10 bg-white/40 rounded-lg animate-pulse group-hover:scale-105 duration-500 ease-in-out"
              ></div>
              <button
                @click="$router.push({ name: 'card' })"
                class="absolute top-0 cursor-pointer right-0 bottom-0 left-0 text-white flex justify-center items-center"
              >
                <component class="size-5" :is="IconShopping" />
              </button>
              <!-- spn not -->
              <span
                class="bg-radial bg-red-500 border border-white font-semibold text-white px-1 text-xs font-open-sans rounded-full absolute -top-1 left-5"
                >{{ computedCartCount ?? 0 }}</span
              >
            </div>
            <!-- Block Card Shoppong -->

            <!-- Block Favorit -->
            <div class="relative group">
              <div
                class="size-10 bg-white/40 rounded-lg animate-pulse group-hover:scale-105 duration-500 ease-in-out"
              ></div>
              <button
                @click="$router.push({ name: 'favorite' })"
                class="absolute top-0 cursor-pointer right-0 bottom-0 left-0 text-white flex justify-center items-center"
              >
                <component class="size-5" :is="IconSupport" />
              </button>
              <!-- spn not -->
              <span
                class="bg-radial bg-red-500 border border-white font-semibold text-white px-1 text-xs font-open-sans rounded-full absolute -top-1 left-5"
                >{{ computedFavoriteCount ?? 0 }}</span
              >
            </div>
            <!-- Block Card Favorit -->

            <!-- Block user -->
            <div v-if="false" class="relative group">
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
          </div>
        </nav>
      </div>
    </header>

    <div class="w-full flex-1">
      <RouterView />
    </div>

    <footer class="bg-orange-600 text-white mt-10">
      <div
        class="max-w-7xl mx-auto px-4 py-10 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-8"
      >
        <!-- Brand -->
        <div>
          <h2 class="text-2xl font-bold mb-3">Good Food</h2>
          <p class="text-sm text-white/80 leading-relaxed">
            Delicious food delivered fast and fresh. Enjoy your favorite meals anytime anywhere.
          </p>
        </div>

        <!-- Quick Links -->
        <div>
          <h3 class="text-lg font-semibold mb-3">Quick Links</h3>
          <ul class="space-y-2 text-sm text-white/80">
            <li><RouterLink to="/" class="hover:text-white transition">Home</RouterLink></li>
            <li><RouterLink to="/menu" class="hover:text-white transition">Menu</RouterLink></li>
            <li>
              <RouterLink to="/orders" class="hover:text-white transition">Orders</RouterLink>
            </li>
            <li>
              <RouterLink to="/contact" class="hover:text-white transition">Contact</RouterLink>
            </li>
          </ul>
        </div>

        <!-- Categories -->
        <div>
          <h3 class="text-lg font-semibold mb-3">Categories</h3>
          <ul class="space-y-2 text-sm text-white/80">
            <li class="hover:text-white transition cursor-pointer">Food</li>
            <li class="hover:text-white transition cursor-pointer">Rice</li>
            <li class="hover:text-white transition cursor-pointer">Drinks</li>
            <li class="hover:text-white transition cursor-pointer">Coffee</li>
          </ul>
        </div>

        <!-- Contact -->
        <div>
          <h3 class="text-lg font-semibold mb-3">Contact</h3>
          <ul class="space-y-2 text-sm text-white/80">
            <li class="hover:text-white transition">📧 support@goodfood.com</li>
            <li class="hover:text-white transition">📞 +855 123 456 789</li>
            <li class="hover:text-white transition">📍 Phnom Penh, Cambodia</li>
          </ul>
        </div>
      </div>

      <!-- Bottom bar -->
      <div class="border-t border-white/20">
        <div
          class="max-w-7xl mx-auto px-4 py-4 flex flex-col md:flex-row justify-between items-center text-sm text-white/70"
        >
          <p class="mb-2 md:mb-0">
            © 2026 <span class="font-semibold text-white">Good Food</span>. All rights reserved.
          </p>

          <div class="flex gap-5">
            <a href="#" class="hover:text-white transition">Privacy</a>
            <a href="#" class="hover:text-white transition">Terms</a>
            <a href="#" class="hover:text-white transition">Support</a>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

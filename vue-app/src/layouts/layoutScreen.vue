<!-- eslint-disable @typescript-eslint/no-unused-vars -->
<script setup lang="ts">
import IconNotification from '@/components/icons/IconNotification.vue'
import IconSearch from '@/components/icons/IconSearch.vue'
import IconShopping from '@/components/icons/IconShopping.vue'
import IconSupport from '@/components/icons/IconSupport.vue'
import IconUser from '@/components/icons/IconUser.vue'
import { HomeIcon, InvoiceIcon, LogoApp } from '@/stores/icon'
import { useMyOrders } from '@/stores/my_order'
import { useShoppStore } from '@/stores/shopp_store'
import { computed } from 'vue'
import { RouterLink, RouterView } from 'vue-router'

const myOrder = useMyOrders()

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
          <div
            class="size-10 overflow-clip object-center object-cover border border-white rounded-full bg-orange-500"
          >
            <img
              class="rounded-full object-center object-cover w-full h-full"
              :src="LogoApp"
              alt=""
            />
          </div>
          <div class="flex justify-start items-center gap-1">
            <h1 class="font-black text-3xl text-white drop-shadow">Makara Food Store</h1>
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

            <!-- Block Home -->
            <div class="relative group">
              <div
                class="size-10 bg-white/40 rounded-lg animate-pulse group-hover:scale-105 duration-500 ease-in-out"
              ></div>
              <button
                @click="$router.push({ name: 'home' })"
                class="absolute top-0 cursor-pointer right-0 bottom-0 left-0 text-white flex justify-center items-center"
              >
                <component class="size-5" :is="HomeIcon" />
              </button>
              <!-- spn not -->
            </div>
            <!-- Block Home -->

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

            <!-- Block Invoice -->
            <div class="relative group">
              <div
                class="size-10 bg-white/40 rounded-lg animate-pulse group-hover:scale-105 duration-500 ease-in-out"
              ></div>
              <button
                @click="$router.push({ name: 'orders' })"
                class="absolute top-0 cursor-pointer right-0 bottom-0 left-0 text-white flex justify-center items-center"
              >
                <component class="size-5" :is="InvoiceIcon" />
              </button>
              <!-- spn not -->
              <span
                class="bg-radial bg-red-500 border border-white font-semibold text-white px-1 text-xs font-open-sans rounded-full absolute -top-1 left-5"
                >{{ myOrder.data?.data.length ?? 0 }}</span
              >
            </div>
            <!-- Block Card Invoice -->

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
      <div class="max-w-7xl mx-auto px-6 py-12 grid grid-cols-1 sm:grid-cols-2 gap-10">
        <!-- Brand -->
        <div>
          <div class="flex items-center gap-3 mb-4">
            <img
              class="w-12 h-12 rounded-full object-cover border-2 border-white/30"
              :src="LogoApp"
              alt="Makra Food Store logo"
            />
            <h2 class="text-xl font-bold">Makra Food Store</h2>
          </div>
          <p class="text-sm text-white/80 leading-relaxed">
            ម្ហូបឆ្ងាញ់ដឹកជូនលឿន ស្រស់ថ្មីជានិច្ច។ រីករាយនឹងម្ហូបដែលអ្នកចូលចិត្ត គ្រប់ពេលវេលា
            គ្រប់ទីកន្លែង។
          </p>
        </div>

        <!-- Contact -->
        <div>
          <h3 class="text-lg font-semibold mb-4">Contact</h3>
          <ul class="space-y-3 text-sm text-white/80">
            <li class="flex items-center gap-2 hover:text-white transition">
              <span
                ><svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="1em"
                  height="1em"
                  viewBox="0 0 24 24"
                >
                  <path d="M0 0h24v24H0z" fill="none" />
                  <path
                    fill="currentColor"
                    d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2m-.4 4.25l-7.07 4.42c-.32.2-.74.2-1.06 0L4.4 8.25a.85.85 0 1 1 .9-1.44L12 11l6.7-4.19a.85.85 0 1 1 .9 1.44"
                  />
                </svg>
              </span>
              support@goodfood.com
            </li>
            <li class="flex items-center gap-2 hover:text-white transition">
              <span
                ><svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="1em"
                  height="1em"
                  viewBox="0 0 24 24"
                >
                  <path d="M0 0h24v24H0z" fill="none" />
                  <path
                    fill="currentColor"
                    d="M13 8a3 3 0 0 1 3 3a1 1 0 0 0 2 0a5 5 0 0 0-5-5a1 1 0 0 0 0 2"
                  />
                  <path
                    fill="currentColor"
                    d="M13 4a7 7 0 0 1 7 7a1 1 0 0 0 2 0a9 9 0 0 0-9-9a1 1 0 0 0 0 2m8.75 11.91a1 1 0 0 0-.72-.65l-6-1.37a1 1 0 0 0-.92.26c-.14.13-.15.14-.8 1.38a9.9 9.9 0 0 1-4.87-4.89C9.71 10 9.72 10 9.85 9.85a1 1 0 0 0 .26-.92L8.74 3a1 1 0 0 0-.65-.72a4 4 0 0 0-.72-.18A4 4 0 0 0 6.6 2A4.6 4.6 0 0 0 2 6.6A15.42 15.42 0 0 0 17.4 22a4.6 4.6 0 0 0 4.6-4.6a5 5 0 0 0-.06-.76a4.3 4.3 0 0 0-.19-.73"
                  />
                </svg>
              </span>
              +855 123 456 789
            </li>
            <li class="flex items-center gap-2 hover:text-white transition">
              <span
                ><svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="1em"
                  height="1em"
                  viewBox="0 0 32 32"
                >
                  <path d="M0 0h32v32H0z" fill="none" />
                  <path
                    fill="currentColor"
                    d="M16 2A11.013 11.013 0 0 0 5 13a10.9 10.9 0 0 0 2.216 6.6s.3.395.349.452L16 30l8.439-9.953c.044-.053.345-.447.345-.447l.001-.003A10.9 10.9 0 0 0 27 13A11.013 11.013 0 0 0 16 2m0 15a4 4 0 1 1 4-4a4.005 4.005 0 0 1-4 4"
                  />
                  <circle cx="16" cy="13" r="4" fill="none" />
                </svg>
              </span>
              Phnom Penh, Cambodia
            </li>
          </ul>
        </div>
      </div>

      <!-- Bottom bar -->
      <div class="border-t border-white/20">
        <div
          class="max-w-7xl mx-auto px-6 py-5 flex flex-col md:flex-row justify-between items-center gap-3 text-sm text-white/70"
        >
          <p>
            © 2026 <span class="font-semibold text-white">Makara Food Store</span>. All rights
            reserved.
          </p>

          <div class="flex gap-6">
            <a href="#" class="hover:text-white transition">Privacy</a>
            <a href="#" class="hover:text-white transition">Terms</a>
            <a href="#" class="hover:text-white transition">Support</a>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

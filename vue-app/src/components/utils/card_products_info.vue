<script setup lang="ts">
import { computed } from 'vue'
import IconAdd from '../icons/IconAdd.vue'
import IconSubtract from '../icons/IconSubtract.vue'
import IconShopping from '../icons/IconShopping.vue'
import IconSupport from '../icons/IconSupport.vue'
import type { FoodModel } from '@/models/food_model.ts'
import { useShoppStore } from '@/stores/shopp_store.ts'

const shopStore = useShoppStore()

const prop = defineProps<{
  data: FoodModel | null
}>()

// check if product is already in cart -> disables "add to cart" button
const computedIsInCart = computed(() => {
  if (!prop.data) return false
  return shopStore.products.some((item) => item.id === prop.data?.id)
})

const computedProductQuantity = computed(() => {
  if (!prop.data) return 1
  const productInCart = shopStore.products.find((item) => item.id === prop.data?.id)
  return productInCart ? productInCart.quantity : 1
})

const handleAddToCart = () => {
  if (!prop.data) return
  shopStore.addToCart(prop.data, 1)
}

// check favorite -----------
const favoriteSelected = computed(() => {
  if (!prop.data) return false
  return shopStore.favoriteProducts.some((item) => item.id === prop.data?.id)
})
</script>

<template>
  <div class="border border-white bg-white rounded-2xl overflow-clip">
    <div class="relative">
      <img
        class="w-full h-70 object-cover object-center"
        :src="
          prop.data?.image_url ??
          'https://res.cloudinary.com/dglkfckne/image/upload/v1758529164/i_Gaming_Promotion_Engine_6779be0f6b.png'
        "
        :alt="prop.data?.name ?? 'Food image'"
      />
      <button
        @click="shopStore.toggleFavorite(prop.data)"
        class="-bottom-8 right-2 rounded-full absolute cursor-pointer"
        :class="favoriteSelected ? 'text-red-500' : 'text-slate-400'"
      >
        <IconSupport />
      </button>
    </div>
    <div class="p-4">
      <h1 class="font-hanuman text-xl text-orange-500">{{ prop.data?.name }}</h1>
      <div class="flex justify-between items-center">
        <p class="text-3xl font-bold text-red-500">${{ prop.data?.price?.toFixed(2) }}</p>
      </div>
      <p class="text-slate-400 line-clamp-2">{{ prop.data?.description }}</p>
      <div class="flex justify-between items-center gap-4 mt-2">
        <button
          @click="handleAddToCart"
          :disabled="computedIsInCart"
          class="disabled:cursor-not-allowed disabled:bg-slate-500 p-2 bg-linear-65 from-orange-500 to-pink-500 rounded-md text-white px-6 cursor-pointer hover:bg-orange-400"
        >
          <IconShopping />
        </button>
        <div class="bg-slate-200 rounded-md flex">
          <button
            type="button"
            :disabled="computedProductQuantity <= 1"
            @click="shopStore.decrementProductQuantity(Number(prop.data?.id))"
            class="disabled:bg-gray-300 disabled:text-gray-500 disabled:opacity-50 disabled:cursor-not-allowed disabled:border-gray-300 size-8 flex justify-center items-center font-bold bg-orange-500 cursor-pointer active:bg-orange-400 text-white font-hanuman rounded-l-md"
          >
            <IconSubtract />
          </button>
          <input
            type="number"
            :value="computedProductQuantity"
            readonly
            class="outline-0 w-10 text-center font-poppins flex justify-center items-center"
          />
          <button
            type="button"
            @click="shopStore.incrementProductQuantity(Number(prop.data?.id))"
            class="size-8 flex justify-center items-center font-bold bg-orange-500 cursor-pointer active:bg-orange-400 text-white font-hanuman rounded-r-md"
          >
            <IconAdd />
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

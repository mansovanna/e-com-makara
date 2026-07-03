<script setup lang="ts">
import { ref } from 'vue'
import IconAdd from '../icons/IconAdd.vue'
import IconSubtract from '../icons/IconSubtract.vue'
import IconShopping from '../icons/IconShopping.vue'
const orders = ref(1)

const increment = () => {
  orders.value++
}

const decrement = () => {
  if (orders.value <= 1) {
    orders.value = 1
  } else {
    orders.value--
  }
}
</script>

<template>
  <div class="w-90 border border-white bg-white rounded-2xl overflow-clip">
    <div>
      <img
        class="w-full h-60 object-cover object-center"
        src="https://img.magnific.com/free-photo/mini-burgers-french-fries-spices-cocktails-side-view_141793-1814.jpg?semt=ais_hybrid&w=740&q=80"
        alt=""
      />
    </div>
    <!-- Block Info -->
    <div class="p-4">
      <h1 class="font-hanuman text-xl text-orange-500">ប្រម៉ូសិនពិសេស</h1>
      <div class="flex justify-between items-center">
        <p class="text-3xl font-bold text-red-500">$13.5</p>
      </div>
      <div class="flex justify-between items-center gap-4 mt-2">
        <button
          class="p-2 bg-linear-65 from-orange-500 to-pink-500 rounded-md text-white px-6 cursor-pointer hover:bg-orange-400"
        >
          <component :is="IconShopping" />
        </button>
        <form>
          <div class="bg-slate-200 rounded-md flex">
            <button
              type="button"
              :disabled="orders <= 1"
              @click="decrement"
              class="disabled:bg-gray-300 disabled:text-gray-500 disabled:opacity-50 disabled:cursor-not-allowed disabled:border-gray-300 size-8 flex justify-center items-center font-bold bg-orange-500 cursor-pointer active:bg-orange-400 text-white font-hanuman rounded-l-md"
            >
              <component :is="IconSubtract" />
            </button>
            <input
              type="number"
              v-model="orders"
              class="outline-0 w-10 text-center font-poppins flex justify-center items-center"
            />
            <button
              type="button"
              @click="increment"
              class="size-8 flex justify-center items-center font-bold bg-orange-500 cursor-pointer active:bg-orange-400 text-white font-hanuman rounded-r-md"
            >
              <component :is="IconAdd" />
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

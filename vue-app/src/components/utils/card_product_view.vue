<!-- eslint-disable @typescript-eslint/no-unused-vars -->
<script setup lang="ts">
import { ref } from 'vue'
import IconAdd from '../icons/IconAdd.vue'
import IconSubtract from '../icons/IconSubtract.vue'
import IconShopping from '../icons/IconShopping.vue'
import IconSupport from '../icons/IconSupport.vue'
import IconView from '../icons/IconView.vue'
import IconStatus from '../icons/IconStatus.vue'
</script>

<template>
  <div
    class="border border-white bg-white rounded-2xl overflow-clip hover:scale-105 duration-300 shadow"
  >
    <div class="relative">
      <img
        class="w-full h-70 object-cover object-center"
        src="https://img.magnific.com/free-photo/mini-burgers-french-fries-spices-cocktails-side-view_141793-1814.jpg?semt=ais_hybrid&w=740&q=80"
        alt=""
      />
    </div>
    <!-- Block Info -->
    <div class="p-4">
      <p class="text-3xl font-bold text-red-500">$13.5</p>
      <h1 class="font-hanuman text-xl text-orange-500">ប្រម៉ូសិនពិសេស</h1>
      <h1 class="font-hanuman text-slate-400 line-clamp-2">
        ប្រម៉ូសិនពិសេស ប្រម៉ូសិនពិសេស ប្រម៉ូសិនពិសេស ប្រម៉ូសិនពិសេស ប្រម៉ូសិនពិសេស
      </h1>
      <div class="flex justify-between items-center gap-4 mt-2">
        <div class="flex justify-start items-center gap-2">
          <button
            class="p-2 px-3 bg-linear-65 from-orange-400 to-orange-600 rounded-md text-white cursor-pointer hover:bg-orange-400"
          >
            <component :is="IconView" />
          </button>
          <button
            class="p-2 px-3 bg-linear-65 from-blue-500 to-blue-700 rounded-md text-white cursor-pointer hover:bg-orange-400"
          >
            <component :is="IconStatus" />
          </button>
          <button
            class="p-2 px-3 bg-linear-65 from-red-500 to-red-700 rounded-md text-white cursor-pointer hover:bg-orange-400"
          >
            <component :is="IconStatus" />
          </button>
        </div>

        <!-- status -->
        <span class="px-6 py-1 bg-green-600 text-white capitalize rounded-full">public</span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import Card_product from '@/components/utils/card_product.vue'
import Card_products_info from '@/components/utils/card_products_info.vue'
import { useCustomerStore } from '@/stores/customer_store'
import { LoadingIcon } from '@/stores/icon'
import { onMounted, ref } from 'vue'
const isSelectIndex = ref(0)

const setSelection = (id: number) => {
  isSelectIndex.value = id || 0
  appStore.getFoodsByCategory(isSelectIndex.value)
}

const appStore = useCustomerStore()
onMounted(async () => {
  // console.log('HomeView mounted')
  await appStore.getCategories()
  await appStore.getFoodsByCategory(isSelectIndex.value)
})
</script>

<template>
  <main class="w-full flex flex-col items-center justify-start">
    <div class="w-2/3 max-lg:w-full px-4 py-6 flex flex-col justify-center items-start gap-6">
      <!--  -->
      <!--  -->
      <img
        class="w-full rounded-2xl h-100 max-lg:h-70 object-center object-cover"
        src="https://static.vecteezy.com/system/resources/thumbnails/002/876/939/small/bubble-milk-tea-design-collection-pearl-milk-tea-boba-milk-tea-yummy-drinks-coffees-with-doodle-style-banner-illustration-vector.jpg"
        alt=""
      />
      <!-- Block main -->
      <div
        v-if="false"
        class="w-full rounded-xl bg-linear-to-r from-orange-500 to-orange-400 relative"
      >
        <div class="p-6 pl-20 flex flex-col justify-start items-start">
          <img
            class="w-40 -left-16 -top-5 absolute"
            src="https://static.vecteezy.com/system/resources/thumbnails/024/039/409/small/red-label-tag-sale-discount-with-transparent-background-png.png"
            alt=""
          />

          <img
            class="w-40 -right-16 -top-5 absolute"
            src="https://res.cloudinary.com/dglkfckne/image/upload/v1758529164/i_Gaming_Promotion_Engine_6779be0f6b.png"
            alt=""
          />
          <div class=" ">
            <div class="flex gap-3 items-end">
              <h1 class="font-hanuman text-3xl font-semibold text-white">ប្រម៉ូសិនពិសេស</h1>
              <h1 class="text-5xl font-bold text-white text-right">20%</h1>
            </div>
            <p class="font-hanuman text-white font-normal">
              ការបញ្ចុះតម្លៃអតិបរមា $ 2.40 លើការបញ្ជាទិញអប្បបរមា $ 3.40
            </p>
          </div>
        </div>
        <!-- Block Card Pridcut -->
        <div class="p-6 pt-0">
          <div class="w-full flex gap-4 min-w-full overflow-auto hide-scrollbar scroll-0">
            <div v-for="index in 10" :key="index">
              <Card_product />
            </div>
          </div>
        </div>
      </div>

      <!-- End -->

      <!-- Start menu Catagories -->
      <div class="w-full flex flex-col gap-4">
        <h1 class="text-left">All Order</h1>
        <!-- list menu -->
        <div class="pt-0" v-if="appStore.categories?.data && appStore.categories.data.length > 0">
          <div class="w-2/3 flex gap-4 min-w-full overflow-auto hide-scrollbar scroll-0">
            <button
              @click="setSelection(0)"
              class="px-8 rounded-full cursor-pointer hover:bg-orange-600 hover:text-white duration-500 ease-in-out"
              :class="
                isSelectIndex == 0
                  ? 'bg-linear-65 from-orange-500 to-pink-500 text-white'
                  : 'bg-white border border-orange-500 text-orange-500'
              "
            >
              All
            </button>

            <div v-for="item in appStore.categories?.data || []" :key="item.id">
              <button
                @click="setSelection(item.id)"
                class="pl-1 pr-5 py-1 rounded-full flex justify-center items-center gap-2 text-nowrap cursor-pointer hover:bg-orange-600 hover:text-white duration-500 ease-in-out"
                :class="
                  isSelectIndex === item.id
                    ? 'bg-linear-65 from-orange-500 to-pink-500 text-white'
                    : 'bg-white border border-orange-500 text-orange-500'
                "
              >
                <div class="w-10 h-10">
                  <img
                    v-if="item.image"
                    :src="item.image_url"
                    class="w-10 h-10 object-cover object-center rounded-full"
                  />
                </div>
                <span>{{ item.name }}</span>
              </button>
            </div>
          </div>
        </div>
        <!-- data is empty -->
        <div
          class="w-full flex justify-center items-center p-4"
          v-if="!appStore.categories?.data && !appStore.isLoading"
        >
          <p>No categories available.</p>
        </div>
        <!-- End data is empty -->
        <!-- data loading -->
        <div v-if="appStore.isLoading">
          <div class="flex items-center justify-center p-2">
            <LoadingIcon class="w-10 h-10 animate-spin text-orange-500" />
          </div>
        </div>
      </div>

      <!-- End Start menu Catagories -->
      <div class="w-full flex flex-col gap-4">
        <div
          v-if="appStore.data?.data"
          class="grid grid-cols-3 gap-4 max-md:grid-cols-1 max-lg:grid-cols-2"
        >
          <div v-for="(item, index) in appStore.data?.data" :key="index">
            <Card_products_info :data="item" />
          </div>
        </div>

        <!-- data is empty -->
        <div
          class="w-full flex justify-center items-center p-4"
          v-if="!appStore.data?.data.length && !appStore.isLoadingFoods"
        >
          <p>No foods available.</p>
        </div>
        <!-- End data is empty -->

        <!-- data loading -->
        <div v-if="appStore.isLoadingFoods" class="w-full flex justify-center items-center p-10">
          <div class="flex items-center justify-center p-2">
            <LoadingIcon class="w-10 h-10 animate-spin text-orange-500" />
          </div>
        </div>
      </div>
      <div class="h-20"></div>
    </div>
  </main>
</template>

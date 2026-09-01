<script setup lang="ts">
import { onMounted } from 'vue'
import { useMyOrders } from './stores/my_order'

const myOrders = useMyOrders()
onMounted(() => {
  myOrders.getOrders()
})
</script>

<template>
  <RouterView />
</template>

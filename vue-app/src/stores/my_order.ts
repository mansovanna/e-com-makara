/* eslint-disable @typescript-eslint/no-explicit-any */
import type { MyOrdersResponse } from '@/models/order'
import myOrder_provider from '@/providers/myOrder_provider'
import { defineStore } from 'pinia'

export const useMyOrders = defineStore('my-order', {
  state: () => ({
    data: null as MyOrdersResponse | null,
    isLoading: false,

  }),

  actions: {
    async getOrders() {
      this.isLoading = true
      //
      const idList = localStorage.getItem('my_orders') ?? null
      try {
        // oxlint-disable-next-line no-unused-vars
        const res = await myOrder_provider.getMyOrders(idList)

        this.data = res.data
      } catch (error: any) {
        console.log(error)
      } finally {
        this.isLoading = false
      }
    },
  },
})

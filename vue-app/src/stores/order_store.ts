/* eslint-disable @typescript-eslint/no-explicit-any */
import order_provider from '@/providers/order_provider'
import { defineStore } from 'pinia'

export const useOrderStore = defineStore('order', {
  state: () => ({
    data: null as any | null,
    isLoading: false,
    sumary: null as any | null,
  }),
  actions: {
    // --------------
    async getList() {
      this.isLoading = true

      try {
        const res = await order_provider.getList()
        this.data = res.data
      } catch (error: any) {
        console.log(error)
      } finally {
        this.isLoading = false
      }
    },

    async updateStatus(id: number, status: string) {
      return order_provider.updateStatus(id, status)
    },
    async getSumary() {
      this.isLoading = true

      try {
        const res = await order_provider.getDash()
        this.sumary = res.data
      } catch (error) {
        console.log(error)
      } finally {
        this.isLoading = false
      }
    },
  },
})

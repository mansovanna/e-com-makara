import type { CategoryModelResponeAPI } from '@/models/categories_model'
import type { FoodModelResponseAPI } from '@/models/food_model'
import foodsProvider from '@/providers/foods_provider'
import { defineStore } from 'pinia'

export const useCustomerStore = defineStore('customer', {
  state: () => ({
    data: null as FoodModelResponseAPI | null,
    isLoading: false,
    isLoadingFoods: false,
    categories: null as CategoryModelResponeAPI | null,
  }),
  actions: {
    // logic functions for customer store can be added here
    async getCategories() {
      this.isLoading = true
      try {
        const res = await foodsProvider.getCategoriesList()
        if (res.status == 200) {
          this.categories = res.data
        }
      } catch (error) {
        console.error('Error fetching categories:', error)
      } finally {
        this.isLoading = false
      }
    },

    async getFoodsByCategory(category_id: number = 0) {
      this.isLoadingFoods = true
      try {
        const res = await foodsProvider.getFoodCategoriesList(category_id)
        if (res.status == 200) {
          this.data = res.data
        }
      } catch (error) {
        console.error('Error fetching foods by category:', error)
      } finally {
        this.isLoadingFoods = false
      }
    },
  },
})

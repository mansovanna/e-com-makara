/* eslint-disable @typescript-eslint/no-explicit-any */
// import { defineStore } from 'pinia'
// import { ref, reactive } from 'vue'

interface Product {
  id: number | null
  category_id: number | null
  name: string
  image: File | string | null
  price: number | null
  description: string
  status: 'active' | 'inactive' | ''
}

import type { CategoryModelResponeAPI } from '@/models/categories_model'
import type { FoodModelResponseAPI } from '@/models/food_model'
import FoodProvider from '@/providers/foods_provider'
import { defineStore } from 'pinia'

export const useFoodStore = defineStore('food', {
  state: () => ({
    products: null as FoodModelResponseAPI | null,
    categories: null as CategoryModelResponeAPI | null,
    showModal: false,
    showModalUpdate: false,
    search: '',
    selectedCategoryName: '',
    formData: {
      id: null,
      category_id: null,
      name: '',
      image: null as File | null,
      price: null,
      description: '',
      status: '',
    } as Product,
    errors: {
      name: '',
      category_id: '',
      image: '',
      price: '',
      description: '',
      status: '',
    },
    isLoading: false,
    isLoadingCategories: false,
    isLoadingAny: {
      id: null as number | null,
      loading: false,
    },
  }),
  actions: {
    setModule() {
      this.showModal = !this.showModal
      // console.log('openCreateModal called, showModal:', this.showModal)
    },
    setModuleUpdate() {
      this.showModalUpdate = !this.showModalUpdate
      // console.log('openUpdateModal called, showModalUpdate:', this.showModalUpdate)
    },
    // openProductModal(product?: Product) {
    //   if (product) {
    //     // Edit mode
    //     this.formData = { ...product }
    //   } else {
    //     // Create mode
    //     this.resetForm()
    //   }
    //   this.showModal = true
    // },
    closeProductModal() {
      this.showModal = false
      this.showModalUpdate = false
      this.resetForm()
    },
    resetForm() {
      this.formData = {
        id: null,
        category_id: null,
        name: '',
        image: null,
        price: null,
        description: '',
        status: '',
      }
      this.selectedCategoryName = ''
      this.search = ''
      this.clearErrors()
    },
    clearErrors() {
      this.errors = {
        name: '',
        category_id: '',
        image: '',
        price: '',
        description: '',
        status: '',
      }
    },
    validateForm(): boolean {
      this.clearErrors()
      let isValid = true

      if (!this.formData.name.trim()) {
        this.errors.name = 'Product name is required'
        isValid = false
      }

      if (!this.formData.category_id) {
        this.errors.category_id = 'Category is required'
        isValid = false
      }

      if (!this.formData.image) {
        this.errors.image = 'Product image is required'
        isValid = false
      }

      if (this.formData.price === null || this.formData.price <= 0) {
        this.errors.price = 'Valid price is required'
        isValid = false
      }

      if (!this.formData.description.trim()) {
        this.errors.description = 'Description is required'
        isValid = false
      }

      if (!this.formData.status) {
        this.errors.status = 'Status is required'
        isValid = false
      }

      return isValid
    },

    validateFormUpdate(): boolean {
      this.clearErrors()
      let isValid = true

      if (!this.formData.name.trim()) {
        this.errors.name = 'Product name is required'
        isValid = false
      }

      if (!this.formData.category_id) {
        this.errors.category_id = 'Category is required'
        isValid = false
      }

      if (this.formData.price === null || this.formData.price <= 0) {
        this.errors.price = 'Valid price is required'
        isValid = false
      }

      if (!this.formData.description.trim()) {
        this.errors.description = 'Description is required'
        isValid = false
      }

      if (!this.formData.status) {
        this.errors.status = 'Status is required'
        isValid = false
      }

      return isValid
    },
    async getCategoriesList(search: string = '') {
      try {
        const response = await FoodProvider.getCategoriesList(search)
        this.categories = response.data
      } catch (error) {
        console.error('Error fetching categories:', error)
      }
    },

    // ---------------
    async getFoodsList() {
      this.isLoading = true
      try {
        const response = await FoodProvider.getFoodsList()
        this.products = response.data
      } catch (error) {
        console.error('Error fetching products:', error)
      } finally {
        this.isLoading = false
      }
    },

    async createProduct(data: any) {
      try {
        const response = await FoodProvider.createFood(data)
        if (response.data.status === false) {
          this.errors = response.data.errors
          return
        }
        this.products?.data.unshift(response.data.data)
        this.closeProductModal()
      } catch (error) {
        console.error('Error creating product:', error)
      }
    },

    async updateProduct(id: number, data: any) {
      this.isLoading = true
      try {
        const response = await FoodProvider.updateFood(id, data)
        if (response.data.status === false) {
          this.errors = response.data.errors
          return
        }
        const index = this.products?.data.findIndex((product) => product.id === id)
        if (index !== undefined && index !== -1) {
          this.products!.data[index] = response.data.data
        }
        this.closeProductModal()
      } catch (error) {
        console.error('Error updating product:', error)
      } finally {
        this.isLoading = false
      }
    },
    async deleteProduct(id: number) {
      this.isLoadingAny.id = id
      this.isLoadingAny.loading = true
      try {
        await FoodProvider.deleteFood(id)
        this.products!.data = this.products!.data.filter((product) => product.id !== id)
      } catch (error) {
        console.error('Error deleting product:', error)
      } finally {
        this.isLoadingAny.id = null
        this.isLoadingAny.loading = false
      }
    },
  },
})

/* eslint-disable @typescript-eslint/no-explicit-any */
import type { CategoryModelResponeAPI } from '@/models/categories_model'
import category_provider from '@/providers/category_provider'
import { defineStore } from 'pinia'

export const uesCategories = defineStore('cate', {
  state() {
    return {
      //
      formData: {
        name: '',
        image: null as File | null,
        status: '',
      },
      data: null as CategoryModelResponeAPI | null,
      //
      error: {
        name: '',
        image: '',
        status: '',
      },
      isLoading: false,
      isLoadingIndex: false,
      search: '',
      per_page: 1,
      page: 10,
      // ------------------
      isUpdateForm: false,
      isLoadingAny: {
        id: 0,
        isLoading: false,
      },
    }
  },
  actions: {
    handleFileUpload(e: Event) {
      const target = e.target as HTMLInputElement | null
      this.formData.image = target?.files?.[0] ?? null
    },
    handleUpdate() {
      this.isUpdateForm = !this.isUpdateForm
    },
    // reset or clear
    resetClear() {
      this.formData.name = ''
      this.formData.image = null
      this.formData.status = ''
      this.error.image = ''
      this.error.name = ''
      this.error.status = ''
    },
    // post api create - name is required

    // oxlint-disable-next-line no-unused-vars
    async create(data: FormData) {
      // clear previous error
      this.error.name = ''
      this.isLoading = true
      try {
        const res = await category_provider.create(data)
        if (res.status === 200 || res.status === 201) {
          if (this.data?.data) {
            this.data.data.unshift(res.data.data)
          }

          this.resetClear()
        }
      } catch (error: any) {
        console.log(error)
      } finally {
        this.isLoading = false
      }
    },

    async update(data: FormData, id: number) {
      this.isLoading = true
      try {
        const res = await category_provider.update(id, data)
        if (res.status === 200 || res.status === 201) {
          if (this.data?.data) {
            const index = this.data.data.findIndex((item) => item.id === id)
            if (index !== -1) {
              this.data.data[index] = res.data.data
            }
          }
          this.handleUpdate()
          this.resetClear()
        }
      } catch (error: any) {
        console.log(error)
      } finally {
        this.isLoading = false
      }
    },

    async delete(id: number) {
      this.isLoadingAny.id = id
      this.isLoadingAny.isLoading = true
      try {
        const res = await category_provider.delete(id)
        if (res.status === 200 || res.status === 201) {
          if (this.data?.data) {
            const index = this.data.data.findIndex((item) => item.id === id)
            if (index !== -1) {
              this.data.data.splice(index, 1)
            }
          }
        }
      } catch (error: any) {
        console.log(error)
      } finally {
        this.isLoadingAny.id = 0
        this.isLoadingAny.isLoading = false
      }
    },

    // -------------------
    async getData(search: string, per_page: number = 1, page: number = 10) {
      this.isLoadingIndex = true

      try {
        const res = await category_provider.getAllData(search, per_page, page)
        if (res.status === 200) {
          this.data = res.data
          // console.log(this.data)
        }
      } catch (error: any) {
        console.log(error)
      } finally {
        this.isLoadingIndex = false
      }
    },
  },
})

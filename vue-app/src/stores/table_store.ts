/* eslint-disable @typescript-eslint/no-explicit-any */
import table_provider from '@/providers/table_provider'
import { defineStore } from 'pinia'

interface TableItem {
  id: number
  table_number: string
  qr_code: string | null
  status: string
}

interface FormData {
  id: number | null
  number_table: string
}

export const useTableStore = defineStore('table', {
  state: () => ({
    tables: [] as TableItem[],
    formData: {
      id: null as number | null,
      number_table: null as string | null,
    } as FormData,
    errors: {} as Record<string, string>,
    loading: false,
  }),

  actions: {
    async fetchTables() {
      this.loading = true
      try {
        const res = await table_provider.getTableList()
        this.tables = res.data.data
      } finally {
        this.loading = false
      }
    },

    resetForm() {
      this.formData = { id: null, number_table: '' }
      this.errors = {}
    },

    async saveTable() {
      this.errors = {}
      try {
        const payload = { table_number: this.formData.number_table }

        if (this.formData.id) {
          await table_provider.updateTable(this.formData.id, payload)
        } else {
          await table_provider.createTable(payload)
        }

        this.resetForm()
        await this.fetchTables()
      } catch (err: any) {
        if (err.response?.status === 422) {
          const backendErrors = err.response.data.errors
          this.errors = {
            number_table: backendErrors.table_number?.[0] ?? '',
          }
        }
      }
    },

    editTable(item: TableItem) {
      this.formData = {
        id: item.id,
        number_table: item.table_number,
      }
    },

    async deleteTable(id: number) {
      await table_provider.deleteTable(id)
      await this.fetchTables()
    },
  },
})

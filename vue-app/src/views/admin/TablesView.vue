<!-- eslint-disable @typescript-eslint/no-explicit-any -->
<script setup lang="ts">
import { onMounted } from 'vue'
import { CategorisIcon, DeleteIcon, EditIcon } from '@/stores/icon'
import { useTableStore } from '@/stores/table_store'

const appStore = useTableStore()

onMounted(() => {
  appStore.fetchTables()
})

const handleSubmit = () => {
  if (!appStore.formData.number_table.trim()) {
    appStore.errors.number_table = 'Table number is required'
    return
  }
  appStore.saveTable()
}

const handleEdit = (item: any) => {
  appStore.editTable(item)
}

const handleDelete = (id: number) => {
  if (confirm('Are you sure you want to delete this table?')) {
    appStore.deleteTable(id)
  }
}
</script>

<template>
  <div class="w-full p-4">
    <!-- Header -->
    <div class="w-full">
      <div class="flex justify-between items-center mb-8">
        <div>
          <h1 class="text-4xl font-bold text-orange-600 mb-2">Table Management</h1>
          <p class="text-slate-600">Manage your restaurant tables and menu items</p>
        </div>

        <div class="flex justify-end items-center gap-4">
          <div>
            <!-- <label class="block text-orange-500 font-medium mb-1">
              Number Table <span class="text-red-600">*</span>
            </label> -->
            <div class="relative">
              <input
                type="text"
                v-model="appStore.formData.number_table"
                placeholder="Table Number"
                @keyup.enter="handleSubmit"
                class="w-full pl-12 pr-4 py-2.5 text-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500 rounded-lg font-sans text-base transition"
                :class="
                  appStore.errors.number_table
                    ? 'border-2 border-red-500 bg-red-50'
                    : 'bg-slate-100 border border-slate-200'
                "
              />
              <div
                class="absolute top-0 left-0 bottom-0 rounded-l-lg px-3 flex justify-center items-center bg-gradient-to-r from-orange-600 to-orange-400"
              >
                <component class="text-white size-5" :is="CategorisIcon" />
              </div>
            </div>
            <span v-if="appStore.errors.number_table" class="text-xs text-red-500 mt-1 block">{{
              appStore.errors.number_table
            }}</span>
          </div>
          <button
            @click="handleSubmit"
            class="px-6 py-3 bg-gradient-to-r from-orange-600 to-orange-400 text-white rounded-lg font-semibold hover:from-orange-700 hover:to-orange-500 transition shadow-lg"
          >
            {{ appStore.formData.id ? 'Update Table' : '+ Add New' }}
          </button>
          <button
            v-if="appStore.formData.id"
            @click="appStore.resetForm"
            class="px-4 py-3 bg-slate-200 text-slate-600 rounded-lg font-semibold hover:bg-slate-300 transition"
          >
            Cancel
          </button>
        </div>
      </div>
    </div>

    <!-- Loading state -->
    <div v-if="appStore.loading" class="text-center text-slate-400 py-10">Loading tables...</div>

    <!-- Empty state -->
    <div v-else-if="appStore.tables.length === 0" class="text-center text-slate-400 py-10">
      No tables yet. Add your first table above.
    </div>

    <!-- Table grid -->
    <div v-else class="w-full grid grid-cols-4 gap-4">
      <div v-for="item in appStore.tables" :key="item.id">
        <div class="p-4 bg-white rounded-md border border-orange-500">
          <div
            class="w-full h-20 bg-slate-200 rounded-md flex justify-center items-center p-4 font-afacad text-3xl font-bold text-orange-600"
          >
            <p>{{ item.table_number }}</p>
          </div>

          <div class="mt-4 flex justify-end items-center gap-4">
            <button
              @click="handleEdit(item)"
              class="p-2 rounded-md bg-orange-600 text-white hover:bg-orange-400 cursor-pointer"
            >
              <component :is="EditIcon" />
            </button>

            <button
              @click="handleDelete(item.id)"
              class="p-2 rounded-md bg-red-600 text-white hover:bg-red-400 cursor-pointer"
            >
              <component :is="DeleteIcon" />
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<!-- eslint-disable @typescript-eslint/no-unused-vars -->
<script setup lang="ts">
import IconLock from '@/components/icons/IconLock.vue'
import IconUser from '@/components/icons/IconUser.vue'
import IconView from '@/components/icons/IconView.vue'
import IconViewOff from '@/components/icons/IconViewOff.vue'
import { useAuthStore } from '@/stores/auth_store'
import { LoadingIcon } from '@/stores/icon'
import { ref } from 'vue'

const authStore = useAuthStore()

const isShowPass = ref(false)

const togglePassword = () => {
  isShowPass.value = !isShowPass.value
}

const validateEmail = (email: string) => {
  if (!email) {
    authStore.error.email = 'Email is required!'
    return authStore.error.email
  }

  if (!authStore.validateEmail(email)) {
    authStore.error.email = 'Email format is invalid!'
    return authStore.error.email
  }

  authStore.error.email = ''
  return authStore.error.email
}

const validatePassword = (password: string) => {
  if (!password) {
    authStore.error.password = 'Password is required!'
    return authStore.error.password
  }

  if (!authStore.validatePassword(password)) {
    authStore.error.password =
      'Password must contain uppercase, lowercase, number and special character.'
    return authStore.error.password
  }

  authStore.error.password = ''
  return authStore.error.password
}

const validateForm = () => {
  const isValidateEmail = validateEmail(authStore.formData.email) === ''

  // const isValidatePassword =
  //   validatePassword(authStore.formData.password) === ''

  return isValidateEmail
}

const submitForm = () => {
  if (!validateForm()) return

  authStore.login(authStore.formData.email, authStore.formData.password)
}
</script>

<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <main class="w-full h-screen flex justify-center items-center p-4 bg-slate-200">
    <div class="w-2/4 h-100 bg-white shadow rounded-3xl overflow-clip flex">
      <!-- Block LOGO -->
      <div
        class="w-1/2 p-6 bg-linear-to-r from-orange-600 to-orange-400 flex flex-col justify-center items-center gap-4"
      >
        <div>
          <div class="size-40 overflow-clip border border-white rounded-full bg-orange-500">
            <img
              class="rounded-full"
              src="https://img.pikbest.com/png-images/20241111/-22creative-food-logo-collection-for-culinary-brands-22_11079861.png!sw800"
              alt=""
            />
          </div>
        </div>
        <div class="flex flex-col justify-center items-center">
          <h1 class="font-black text-3xl text-white drop-shadow">Good Food</h1>
          <h1 class="text-xl text-white drop-shadow">Welcome to System Good Food Management</h1>
        </div>
      </div>
      <!-- END Block LOGO -->

      <!-- Auth -->
      <div class="w-1/2 p-6 flex flex-col justify-center items-start">
        <h1 class="font-bold font-poppins text-3xl text-orange-400">Login Account</h1>
        <h1 class="font-poppins text-sm text-orange-400">
          Please check and complete all required fields
        </h1>

        <div class="mt-2 w-full font-open-sans">
          <!-- Form -->
          <form class="space-y-2" @submit.prevent="submitForm">
            <!-- Email -->
            <div class="w-full">
              <label class="text-orange-500">Email <span class="text-red-600">*</span></label>
              <div class="relative mt-1">
                <input
                  type="text"
                  v-model="authStore.formData.email"
                  placeholder="example.com"
                  class="w-full pl-14 p-2.5 text-orange-500 focus:outline-orange-600/50 focus:ring-4 focus:ring-orange-500 rounded-md font-open-sans text-lg"
                  :class="
                    authStore.error.email
                      ? 'border border-red-500 bg-red-100 text-red-500'
                      : 'bg-slate-100'
                  "
                />

                <div
                  class="absolute top-0 left-0 bottom-0 rounded-l-md px-3 flex justify-center items-center bg-linear-to-r from-orange-600 to-orange-400"
                >
                  <component class="text-white size-6" :is="IconUser" />
                </div>
              </div>
              <span class="font-poppins text-xs text-red-500">{{ authStore.error.email }}</span>
            </div>
            <!-- End Email -->

            <!-- Password -->
            <div class="w-full">
              <label class="text-orange-500">Password <span class="text-red-600">*</span></label>
              <div class="relative mt-1">
                <input
                  :type="isShowPass ? 'text' : 'password'"
                  placeholder="*******"
                  v-model="authStore.formData.password"
                  class="w-full pl-14 pr-12 p-2.5 text-orange-500 focus:outline-orange-600/50 focus:ring-4 focus:ring-orange-500 rounded-md font-open-sans text-lg"
                  :class="
                    authStore.error.password
                      ? 'border border-red-500 bg-red-100 text-red-500'
                      : 'bg-slate-100'
                  "
                />

                <div
                  class="absolute top-0 left-0 bottom-0 rounded-l-md px-3 flex justify-center items-center bg-linear-to-r from-orange-600 to-orange-400"
                >
                  <component class="text-white size-6" :is="IconLock" />
                </div>

                <!-- Lock show -->
                <button
                  @click="togglePassword"
                  type="button"
                  class="overflow-clip absolute top-0 cursor-pointer right-0 bottom-0 rounded-r-md px-3 flex justify-center items-center"
                >
                  <component v-if="!isShowPass" class="text-orange-500 size-6" :is="IconView" />
                  <component v-else class="text-orange-500 size-6" :is="IconViewOff" />
                </button>
              </div>
              <span class="font-poppins text-xs text-red-500">{{ authStore.error.password }}</span>
            </div>
            <!-- End Password -->

            <!-- Button -->
            <button
              type="submit"
              :disabled="authStore.isLoading"
              class="mt-3 font-open-sans flex justify-center items-center bg-linear-to-r from-orange-600 to-orange-400 text-white w-full p-2.5 rounded-md cursor-pointer hover:scale-105 duration-500 ease-in-out"
            >
              <component v-if="authStore.isLoading" :is="LoadingIcon" />
              <span v-else> Login Now</span>
            </button>
          </form>
        </div>

        <div class="w-full mt-4 flex justify-between items-center gap-4">
          <hr class="text-orange-500 w-full" />
          <div class="rotate-45">
            <div class="w-4 h-4 bg-red-500"></div>
          </div>
          <hr class="text-orange-500 w-full" />
        </div>
      </div>
      <!-- End Auth -->
    </div>
  </main>
</template>

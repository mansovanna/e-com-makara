<!-- eslint-disable @typescript-eslint/no-unused-vars -->
<script setup lang="ts">
import IconLock from '@/components/icons/IconLock.vue'
import IconUser from '@/components/icons/IconUser.vue'
import IconView from '@/components/icons/IconView.vue'
import IconViewOff from '@/components/icons/IconViewOff.vue'
import { useAuthStore } from '@/stores/auth_store'
import { LoadingIcon, LogoApp } from '@/stores/icon'
import { ref } from 'vue'

const authStore = useAuthStore()

const isShowPass = ref(false)

const togglePassword = () => {
  isShowPass.value = !isShowPass.value
}

const validateEmail = (email: string) => {
  if (!email) {
    authStore.error.email = 'សូមបញ្ចូលអ៊ីមែល!'
    return authStore.error.email
  }

  if (!authStore.validateEmail(email)) {
    authStore.error.email = 'ទម្រង់អ៊ីមែលមិនត្រឹមត្រូវ!'
    return authStore.error.email
  }

  authStore.error.email = ''
  return authStore.error.email
}

const validatePassword = (password: string) => {
  if (!password) {
    authStore.error.password = 'សូមបញ្ចូលពាក្យសម្ងាត់!'
    return authStore.error.password
  }

  if (!authStore.validatePassword(password)) {
    authStore.error.password =
      'ពាក្យសម្ងាត់ត្រូវមានអក្សរធំ អក្សរតូច លេខ និងសញ្ញាពិសេស។'
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
  <main
    class="w-full min-h-screen flex justify-center items-center p-4 bg-slate-200 font-kantumruy-pro"
  >
    <div
      class="w-full max-w-4xl bg-white shadow rounded-3xl overflow-clip flex flex-col md:flex-row"
    >
      <!-- Block LOGO -->
      <div
        class="w-full md:w-1/2 p-6 py-8 md:py-6 bg-linear-to-r from-orange-600 to-orange-400 flex flex-col justify-center items-center gap-4"
      >
        <div>
          <div
            class="size-24 sm:size-32 md:size-40 overflow-clip border border-white rounded-full bg-orange-500"
          >
            <img
              class="rounded-full size-24 sm:size-32 md:size-40 object-center object-cover"
              :src="LogoApp"
              alt="Makra Food Store"
            />
          </div>
        </div>
        <div class="flex flex-col justify-center items-center text-center px-2">
          <h1 class="font-black text-2xl sm:text-3xl text-white drop-shadow">
            Makra Food Store
          </h1>
          <h1 class="text-base sm:text-xl text-white drop-shadow">
            សូមស្វាគមន៍មកកាន់ប្រព័ន្ធគ្រប់គ្រង
          </h1>
        </div>
      </div>
      <!-- END Block LOGO -->

      <!-- Auth -->
      <div class="w-full md:w-1/2 p-6 flex flex-col justify-center items-start">
        <h1 class="font-bold  font-kantumruy-pro text-2xl sm:text-3xl text-orange-400">
          ចូលប្រើគណនី
        </h1>
        <h1 class=" font-kantumruy-pro text-sm text-orange-400 mt-1">
          សូមពិនិត្យ និងបំពេញព័ត៌មានឱ្យបានត្រឹមត្រូវ
        </h1>

        <p
          v-if="authStore.isMessageError"
          class="text-red-500 text-xs p-1 bg-red-500/10 rounded-lg px-4 mt-2"
        >
          {{ authStore.isMessageError }}
        </p>

        <div class="mt-2 w-full font-kantumruy-pro">
          <!-- Form -->
          <form class="space-y-2" @submit.prevent="submitForm">
            <!-- Email -->
            <div class="w-full">
              <label class="text-orange-500 font-kantumruy-pro">អ៊ីមែល <span class="text-red-600">*</span></label>
              <div class="relative mt-1">
                <input
                  type="text"
                  v-model="authStore.formData.email"
                  placeholder="example.com"
                  class="w-full pl-14 p-2.5  text-orange-500 focus:outline-orange-600/50 focus:ring-4 focus:ring-orange-500 rounded-md font-kantumruy-pro text-base sm:text-lg"
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
              <span class=" text-xs text-red-500 font-kantumruy-pro">{{ authStore.error.email }}</span>
            </div>
            <!-- End Email -->

            <!-- Password -->
            <div class="w-full">
              <label class="text-orange-500 font-kantumruy-pro">ពាក្យសម្ងាត់ <span class="text-red-600">*</span></label>
              <div class="relative mt-1">
                <input
                  :type="isShowPass ? 'text' : 'password'"
                  placeholder="*******"
                  v-model="authStore.formData.password"
                  class="w-full pl-14 pr-12 p-2.5 text-orange-500 focus:outline-orange-600/50 focus:ring-4 focus:ring-orange-500 rounded-md font-kantumruy-pro text-base sm:text-lg"
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
                  class="overflow-clip absolute top-0 font-kantumruy-pro cursor-pointer right-0 bottom-0 rounded-r-md px-3 flex justify-center items-center"
                >
                  <component v-if="!isShowPass" class="text-orange-500 size-6 " :is="IconView" />
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
              class="mt-3  flex font-kantumruy-pro justify-center items-center bg-linear-to-r from-orange-600 to-orange-400 text-white w-full p-2.5 rounded-md cursor-pointer hover:scale-105 duration-500 ease-in-out"
            >
              <component v-if="authStore.isLoading" :is="LoadingIcon" />
              <span v-else>ចូលប្រើឥឡូវនេះ</span>
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

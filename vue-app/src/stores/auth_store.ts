import auth_provider from '@/providers/auth_provider'
import router from '@/router'
import { defineStore } from 'pinia'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    formData: {
      email: '',
      // email: 'admin@1234gmail.com',
      password: '',
      // password: 'password',
    },
    error: {
      email: '',
      password: '',
    },
    isLoading: false,
  }),
  actions: {
    // Email validation helper
    validateEmail(email: string) {
      return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)
    },
    validatePassword(password: string) {
      const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/
      return regex.test(password)
    },
    async login(email: string, password: string) {
      this.isLoading = true
      try {
        const res = await auth_provider.login(email, password)

        console.log(res.data)
        localStorage.setItem('token', res.data.token)
        localStorage.setItem('user', JSON.stringify(res.data.user))
        router.push('/admin')
      } catch (error) {
        console.log(error)
      } finally {
        this.isLoading = false
      }
    },
    //
    async logout() {
      this.isLoading = true

      try {
        await auth_provider.logout()
        localStorage.removeItem('token')
        router.push({ name: 'login' })
      } catch (e) {
        console.log(e)
      } finally {
        this.isLoading = false
      }
    },
  },
})

import api from '@/config/api'

class AuthProvider {
  login(email: string, password: string) {
    return api.post('/login', {
      email: email,
      password: password,
    })
  }
  logout() {
    return api.delete('/logout')
  }
}

export default new AuthProvider()

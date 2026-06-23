import axios from 'axios'

const api = axios.create({
  baseURL: 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
  timeout: 15000,
})

// JWT token placeholder — connect when PHP Slim backend is ready
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('eventilize_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => Promise.reject(error)
)

api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response && error.response.status === 401) {
      localStorage.removeItem('eventilize_user')
      localStorage.removeItem('eventilize_token')
      
      const currentPath = window.location.pathname
      if (currentPath !== '/' && currentPath !== '/login' && currentPath !== '/register') {
        window.location.href = '/login?expired=true'
      }
    }
    return Promise.reject(error)
  }
)

export default api

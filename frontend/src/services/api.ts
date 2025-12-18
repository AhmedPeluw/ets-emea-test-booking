import axios, { AxiosInstance } from 'axios';
import { User, Session, Booking, ApiResponse } from '@/types';

const API_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api';

class ApiService {
  private client: AxiosInstance;

  constructor() {
    this.client = axios.create({
      baseURL: API_URL,
      headers: {
        'Content-Type': 'application/json',
      },
    });

    // Intercepteur pour ajouter le token JWT
    this.client.interceptors.request.use((config) => {
      const token = this.getToken();
      if (token) {
        config.headers.Authorization = `Bearer ${token}`;
      }
      return config;
    });

    // Intercepteur pour gérer les erreurs (token expiré)
    this.client.interceptors.response.use(
      (response) => response,
      (error) => {
        // Si token expiré ou invalide, rediriger vers login
        if (error.response?.status === 401 && error.response?.data?.message?.includes('JWT')) {
          this.removeToken();
          if (typeof window !== 'undefined') {
            window.location.href = '/login';
          }
        }
        return Promise.reject(error);
      }
    );
  }

  private getToken(): string | null {
    return typeof window !== 'undefined' ? localStorage.getItem('token') : null;
  }

  setToken(token: string): void {
    if (typeof window !== 'undefined') {
      localStorage.setItem('token', token);
    }
  }

  removeToken(): void {
    if (typeof window !== 'undefined') {
      localStorage.removeItem('token');
    }
  }

  // Auth
  async register(data: { name: string; email: string; password: string; confirmPassword: string }) {
    const response = await this.client.post('/auth/register', data);
    return {
      success: response.data.success,
      data: { user: response.data.user },
      message: response.data.message
    };
  }

  async login(email: string, password: string) {
    const response = await this.client.post('/auth/login', { email, password });
    if (response.data.success && response.data.token) {
      this.setToken(response.data.token);
    }
    return {
      success: response.data.success,
      data: {
        token: response.data.token,
        user: response.data.user
      },
      message: response.data.message
    };
  }

  logout(): void {
    this.removeToken();
  }

  // Users
  async getProfile() {
    const response = await this.client.get('/users/me');
    
    // Backend retourne DIRECTEMENT {success: true, user: {...}}
    // On adapte au format attendé {success: true, data: {user: {...}}}
    return {
      success: response.data.success,
      data: {
        user: response.data.user
      }
    };
  }

  async updateProfile(data: Partial<User>) {
    const response = await this.client.put('/users/me', data);
    
    // Backend retourne DIRECTEMENT {success: true, user: {...}, message: "..."}
    // PAS {success: true, data: {user: {...}}}
    return {
      success: response.data.success,
      data: {
        user: response.data.user  // Backend a "user" directement
      },
      message: response.data.message
    };
  }

  // Sessions
  async getSessions(page = 1, itemsPerPage = 10, filters?: { language?: string; level?: string }) {
    const response = await this.client.get<ApiResponse<Session[]>>('/sessions', {
      params: { page, itemsPerPage, ...filters },
    });
    return response.data;
  }

  async getSession(id: string) {
    const response = await this.client.get<ApiResponse<Session>>(`/sessions/${id}`);
    return response.data;
  }

  // Bookings
  async getBookings(page = 1, itemsPerPage = 10) {
    const response = await this.client.get<ApiResponse<Booking[]>>('/bookings', {
      params: { page, itemsPerPage },
    });
    return response.data;
  }

  async createBooking(sessionId: string) {
    const response = await this.client.post<ApiResponse<Booking>>('/bookings', { sessionId });
    return response.data;
  }

  async cancelBooking(id: string, reason?: string) {
    const response = await this.client.delete<ApiResponse<Booking>>(`/bookings/${id}`, {
      data: { reason },
    });
    return response.data;
  }
}

export const apiService = new ApiService();

/**
 * Authentication Context
 * Manages user authentication state and provides auth methods
 * Handles JWT token validation and automatic login redirect
 */
'use client';

import React, { createContext, useContext, useState, useEffect } from 'react';
import { User } from '@/types';
import { apiService } from '@/services/api';

interface AuthContextType {
  user: User | null;
  loading: boolean;
  login: (email: string, password: string) => Promise<void>;
  register: (name: string, email: string, password: string, confirmPassword: string) => Promise<void>;
  logout: () => void;
  updateUser: (data: Partial<User>) => Promise<void>;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const AuthProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    checkAuth();
  }, []);

  const checkAuth = async () => {
    try {
      // Only check auth if token exists
      const token = localStorage.getItem('token');
      if (!token) {
        setLoading(false);
        return;
      }
      

      // Retry logic pour attendre que le backend soit prêt
      let retries = 3;
      let lastError: any;

      while (retries > 0) {
        try {
          const response = await apiService.getProfile();
          
          if (response.success && response.data?.user) {
            setUser(response.data.user);
            setLoading(false);
            return;
          }
          break;
        } catch (error: any) {
          lastError = error;
          
          // Si erreur de connexion (backend pas prêt), retry
          if (error.code === 'ERR_NETWORK' || error.code === 'ECONNRESET') {
            retries--;
            if (retries > 0) {
              console.log(`⏳ checkAuth: Backend not ready, retrying... (${retries} left)`);
              await new Promise(resolve => setTimeout(resolve, 2000)); // Attendre 2s
              continue;
            }
          }
          
          // Si erreur 401, token invalide/expiré
          if (error.response?.status === 401) {
            localStorage.removeItem('token');
          }
          
          break;
        }
      }
      
    } catch (error) {
    } finally {
      setLoading(false);
    }
  };

  const login = async (email: string, password: string) => {
    const response = await apiService.login(email, password);
    if (response.success && response.data?.user) {
      setUser(response.data.user);
    } else {
      throw new Error(response.message || 'Login failed');
    }
  };

  const register = async (name: string, email: string, password: string, confirmPassword: string) => {
    const response = await apiService.register({ name, email, password, confirmPassword });
    if (!response.success) {
      throw new Error(response.message || 'Registration failed');
    }
    // Auto-login after registration
    await login(email, password);
  };

  const logout = () => {
    apiService.logout();
    setUser(null);
  };

  const updateUser = async (data: Partial<User>) => {
    try {
      const response = await apiService.updateProfile(data);
      
      if (response.success && response.data?.user) {
        setUser(response.data.user);
        return; // Success - pas d'erreur
      }
      
      // Si pas success, lancer erreur
      throw new Error(response.message || 'Mise à jour échouée');
    } catch (error: any) {
      throw error; // Propager l'erreur
    }
  };

  return (
    <AuthContext.Provider value={{ user, loading, login, register, logout, updateUser }}>
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (context === undefined) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
};

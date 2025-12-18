import { apiService } from '@/services/api';
import axios from 'axios';

jest.mock('axios');
const mockedAxios = axios as jest.Mocked<typeof axios>;

describe('ApiService', () => {
  beforeEach(() => {
    jest.clearAllMocks();
    localStorage.clear();
  });

  describe('Authentication', () => {
    it('should register a new user successfully', async () => {
      const mockResponse = {
        data: {
          success: true,
          user: {
            id: '123',
            name: 'Test User',
            email: 'test@example.com',
          },
          message: 'Registration successful',
        },
      };

      mockedAxios.create.mockReturnThis();
      mockedAxios.post.mockResolvedValue(mockResponse);

      const userData = {
        name: 'Test User',
        email: 'test@example.com',
        password: 'Password123!',
        confirmPassword: 'Password123!',
      };

      const result = await apiService.register(userData);

      expect(result.success).toBe(true);
      expect(result.data.user.name).toBe('Test User');
    });

    it('should login and store token', async () => {
      const mockResponse = {
        data: {
          success: true,
          token: 'test-jwt-token',
          user: {
            id: '123',
            name: 'Test User',
            email: 'test@example.com',
          },
        },
      };

      mockedAxios.create.mockReturnThis();
      mockedAxios.post.mockResolvedValue(mockResponse);

      const result = await apiService.login('test@example.com', 'Password123!');

      expect(result.success).toBe(true);
      expect(result.data.token).toBe('test-jwt-token');
      expect(localStorage.setItem).toHaveBeenCalledWith('token', 'test-jwt-token');
    });

    it('should remove token on logout', () => {
      apiService.logout();
      expect(localStorage.removeItem).toHaveBeenCalledWith('token');
    });
  });

  describe('User Profile', () => {
    it('should get user profile', async () => {
      const mockResponse = {
        data: {
          success: true,
          user: {
            id: '123',
            name: 'Test User',
            email: 'test@example.com',
            roles: ['ROLE_USER'],
          },
        },
      };

      mockedAxios.create.mockReturnThis();
      mockedAxios.get.mockResolvedValue(mockResponse);

      const result = await apiService.getProfile();

      expect(result.success).toBe(true);
      expect(result.data.user.name).toBe('Test User');
    });

    it('should update user profile', async () => {
      const mockResponse = {
        data: {
          success: true,
          user: {
            id: '123',
            name: 'Updated Name',
            email: 'test@example.com',
          },
          message: 'Profile updated',
        },
      };

      mockedAxios.create.mockReturnThis();
      mockedAxios.put.mockResolvedValue(mockResponse);

      const result = await apiService.updateProfile({ name: 'Updated Name' });

      expect(result.success).toBe(true);
      expect(result.data.user.name).toBe('Updated Name');
    });
  });

  describe('Error Handling', () => {
    it('should handle 401 errors and redirect', async () => {
      const mockError = {
        response: {
          status: 401,
          data: {
            message: 'JWT Token not found',
          },
        },
      };

      mockedAxios.create.mockReturnThis();
      mockedAxios.get.mockRejectedValue(mockError);

      // Mock window.location
      delete window.location;
      window.location = { href: '' } as any;

      try {
        await apiService.getProfile();
      } catch (error) {
        expect(localStorage.removeItem).toHaveBeenCalledWith('token');
      }
    });
  });
});

import React from 'react';
import { render, screen, waitFor } from '@testing-library/react';
import { AuthProvider, useAuth } from '@/contexts/AuthContext';
import { apiService } from '@/services/api';

jest.mock('@/services/api');

const TestComponent = () => {
  const { user, loading } = useAuth();
  
  if (loading) return <div>Loading...</div>;
  if (user) return <div>Welcome {user.name}</div>;
  return <div>Not authenticated</div>;
};

describe('AuthContext', () => {
  beforeEach(() => {
    jest.clearAllMocks();
    localStorage.clear();
  });

  it('should show loading initially', () => {
    (apiService.getProfile as jest.Mock).mockImplementation(() => 
      new Promise(() => {}) // Never resolves
    );

    render(
      <AuthProvider>
        <TestComponent />
      </AuthProvider>
    );

    expect(screen.getByText('Loading...')).toBeInTheDocument();
  });

  it('should show not authenticated when no token', async () => {
    localStorage.getItem = jest.fn(() => null);

    render(
      <AuthProvider>
        <TestComponent />
      </AuthProvider>
    );

    await waitFor(() => {
      expect(screen.getByText('Not authenticated')).toBeInTheDocument();
    });
  });

  it('should authenticate user with valid token', async () => {
    localStorage.getItem = jest.fn(() => 'valid-token');
    
    (apiService.getProfile as jest.Mock).mockResolvedValue({
      success: true,
      data: {
        user: {
          id: '123',
          name: 'Test User',
          email: 'test@example.com',
        },
      },
    });

    render(
      <AuthProvider>
        <TestComponent />
      </AuthProvider>
    );

    await waitFor(() => {
      expect(screen.getByText('Welcome Test User')).toBeInTheDocument();
    });
  });

  it('should handle authentication error', async () => {
    localStorage.getItem = jest.fn(() => 'invalid-token');
    
    (apiService.getProfile as jest.Mock).mockRejectedValue({
      response: { status: 401 },
    });

    render(
      <AuthProvider>
        <TestComponent />
      </AuthProvider>
    );

    await waitFor(() => {
      expect(screen.getByText('Not authenticated')).toBeInTheDocument();
    });
  });
});

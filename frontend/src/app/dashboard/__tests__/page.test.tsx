import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import { useRouter } from 'next/navigation';
import DashboardPage from '@/app/dashboard/page';
import { useAuth } from '@/contexts/AuthContext';
import { apiService } from '@/services/api';

jest.mock('next/navigation', () => ({
  useRouter: jest.fn(),
}));

jest.mock('@/contexts/AuthContext', () => ({
  useAuth: jest.fn(),
}));

jest.mock('@/services/api');

describe('Dashboard Page', () => {
  const mockPush = jest.fn();
  const mockUser = {
    id: '123',
    name: 'Test User',
    email: 'test@example.com',
    roles: ['ROLE_USER'],
  };

  const mockSessions = {
    success: true,
    data: {
      items: [
        {
          id: '1',
          language: 'English',
          level: 'B2',
          date: '2024-12-20',
          time: '10:00',
          duration: 120,
          location: 'Paris',
          availableSeats: 10,
          maxSeats: 20,
          price: 150,
          isActive: true,
        },
        {
          id: '2',
          language: 'French',
          level: 'C1',
          date: '2024-12-21',
          time: '14:00',
          duration: 120,
          location: 'Lyon',
          availableSeats: 5,
          maxSeats: 15,
          price: 180,
          isActive: true,
        },
      ],
      total: 2,
      pages: 1,
      currentPage: 1,
      itemsPerPage: 10,
    },
  };

  beforeEach(() => {
    jest.clearAllMocks();
    (useRouter as jest.Mock).mockReturnValue({ push: mockPush });
    (useAuth as jest.Mock).mockReturnValue({
      user: mockUser,
      loading: false,
    });
    (apiService.getSessions as jest.Mock).mockResolvedValue(mockSessions);
  });

  it('should render dashboard with sessions', async () => {
    render(<DashboardPage />);

    await waitFor(() => {
      expect(screen.getByText(/sessions disponibles/i)).toBeInTheDocument();
      expect(screen.getByText(/English/)).toBeInTheDocument();
      expect(screen.getByText(/French/)).toBeInTheDocument();
    });
  });

  it('should display user welcome message', async () => {
    render(<DashboardPage />);

    await waitFor(() => {
      expect(screen.getByText(/bienvenue/i)).toBeInTheDocument();
      expect(screen.getByText(/Test User/)).toBeInTheDocument();
    });
  });

  it('should show loading state initially', () => {
    (apiService.getSessions as jest.Mock).mockImplementation(
      () => new Promise(() => {})
    );

    render(<DashboardPage />);

    expect(screen.getByText(/chargement/i)).toBeInTheDocument();
  });

  it('should filter sessions by language', async () => {
    render(<DashboardPage />);

    await waitFor(() => {
      expect(screen.getByText(/English/)).toBeInTheDocument();
    });

    const languageFilter = screen.getByRole('combobox', { name: /langue/i });
    fireEvent.change(languageFilter, { target: { value: 'English' } });

    expect(apiService.getSessions).toHaveBeenCalledWith(
      1,
      10,
      expect.objectContaining({ language: 'English' })
    );
  });

  it('should filter sessions by level', async () => {
    render(<DashboardPage />);

    await waitFor(() => {
      expect(screen.getByText(/B2/)).toBeInTheDocument();
    });

    const levelFilter = screen.getByRole('combobox', { name: /niveau/i });
    fireEvent.change(levelFilter, { target: { value: 'B2' } });

    expect(apiService.getSessions).toHaveBeenCalledWith(
      1,
      10,
      expect.objectContaining({ level: 'B2' })
    );
  });

  it('should display session details correctly', async () => {
    render(<DashboardPage />);

    await waitFor(() => {
      // Check first session
      expect(screen.getByText(/English/)).toBeInTheDocument();
      expect(screen.getByText(/B2/)).toBeInTheDocument();
      expect(screen.getByText(/Paris/)).toBeInTheDocument();
      expect(screen.getByText(/10 places/i)).toBeInTheDocument();
      expect(screen.getByText(/150/)).toBeInTheDocument();
    });
  });

  it('should show book button for available sessions', async () => {
    render(<DashboardPage />);

    await waitFor(() => {
      const bookButtons = screen.getAllByRole('button', { name: /réserver/i });
      expect(bookButtons).toHaveLength(2);
    });
  });

  it('should handle booking a session', async () => {
    (apiService.createBooking as jest.Mock).mockResolvedValue({
      success: true,
      data: { id: 'booking-1', status: 'confirmed' },
    });

    render(<DashboardPage />);

    await waitFor(() => {
      expect(screen.getAllByRole('button', { name: /réserver/i })).toHaveLength(2);
    });

    const firstBookButton = screen.getAllByRole('button', { name: /réserver/i })[0];
    fireEvent.click(firstBookButton);

    await waitFor(() => {
      expect(apiService.createBooking).toHaveBeenCalledWith('1');
    });
  });

  it('should show success message after booking', async () => {
    (apiService.createBooking as jest.Mock).mockResolvedValue({
      success: true,
      message: 'Réservation réussie',
    });

    render(<DashboardPage />);

    await waitFor(() => {
      const bookButton = screen.getAllByRole('button', { name: /réserver/i })[0];
      fireEvent.click(bookButton);
    });

    await waitFor(() => {
      expect(screen.getByText(/réservation réussie/i)).toBeInTheDocument();
    });
  });

  it('should show error message on booking failure', async () => {
    (apiService.createBooking as jest.Mock).mockRejectedValue({
      message: 'Booking failed',
    });

    render(<DashboardPage />);

    await waitFor(() => {
      const bookButton = screen.getAllByRole('button', { name: /réserver/i })[0];
      fireEvent.click(bookButton);
    });

    await waitFor(() => {
      expect(screen.getByText(/booking failed/i)).toBeInTheDocument();
    });
  });

  it('should paginate sessions', async () => {
    const paginatedMockSessions = {
      ...mockSessions,
      data: { ...mockSessions.data, pages: 3, currentPage: 1 },
    };
    (apiService.getSessions as jest.Mock).mockResolvedValue(paginatedMockSessions);

    render(<DashboardPage />);

    await waitFor(() => {
      expect(screen.getByText(/page 1/i)).toBeInTheDocument();
    });

    const nextButton = screen.getByRole('button', { name: /suivant/i });
    fireEvent.click(nextButton);

    expect(apiService.getSessions).toHaveBeenCalledWith(2, 10, {});
  });

  it('should redirect to login if not authenticated', async () => {
    (useAuth as jest.Mock).mockReturnValue({
      user: null,
      loading: false,
    });

    render(<DashboardPage />);

    await waitFor(() => {
      expect(mockPush).toHaveBeenCalledWith('/login');
    });
  });

  it('should show empty state when no sessions available', async () => {
    (apiService.getSessions as jest.Mock).mockResolvedValue({
      success: true,
      data: { items: [], total: 0, pages: 0 },
    });

    render(<DashboardPage />);

    await waitFor(() => {
      expect(screen.getByText(/aucune session/i)).toBeInTheDocument();
    });
  });
});

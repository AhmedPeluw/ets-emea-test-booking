import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import { useRouter } from 'next/navigation';
import BookingsPage from '@/app/bookings/page';
import { useAuth } from '@/contexts/AuthContext';
import { apiService } from '@/services/api';

jest.mock('next/navigation', () => ({
  useRouter: jest.fn(),
}));

jest.mock('@/contexts/AuthContext', () => ({
  useAuth: jest.fn(),
}));

jest.mock('@/services/api');

describe('Bookings Page', () => {
  const mockPush = jest.fn();
  const mockUser = {
    id: '123',
    name: 'Test User',
    email: 'test@example.com',
  };

  const mockBookings = {
    success: true,
    data: {
      items: [
        {
          id: 'booking-1',
          userId: '123',
          sessionId: 'session-1',
          status: 'confirmed',
          createdAt: '2024-12-16 10:00:00',
          session: {
            id: 'session-1',
            language: 'English',
            level: 'B2',
            date: '2024-12-20',
            time: '10:00',
            location: 'Paris',
          },
        },
        {
          id: 'booking-2',
          userId: '123',
          sessionId: 'session-2',
          status: 'cancelled',
          createdAt: '2024-12-15 14:00:00',
          session: {
            id: 'session-2',
            language: 'French',
            level: 'C1',
            date: '2024-12-21',
            time: '14:00',
            location: 'Lyon',
          },
        },
      ],
      total: 2,
      pages: 1,
    },
  };

  beforeEach(() => {
    jest.clearAllMocks();
    (useRouter as jest.Mock).mockReturnValue({ push: mockPush });
    (useAuth as jest.Mock).mockReturnValue({
      user: mockUser,
      loading: false,
    });
    (apiService.getBookings as jest.Mock).mockResolvedValue(mockBookings);
  });

  it('should render bookings list', async () => {
    render(<BookingsPage />);

    await waitFor(() => {
      expect(screen.getByText(/mes réservations/i)).toBeInTheDocument();
      expect(screen.getByText(/English/)).toBeInTheDocument();
      expect(screen.getByText(/French/)).toBeInTheDocument();
    });
  });

  it('should display booking details correctly', async () => {
    render(<BookingsPage />);

    await waitFor(() => {
      expect(screen.getByText(/English/)).toBeInTheDocument();
      expect(screen.getByText(/B2/)).toBeInTheDocument();
      expect(screen.getByText(/Paris/)).toBeInTheDocument();
      expect(screen.getByText(/20 déc/i)).toBeInTheDocument();
      expect(screen.getByText(/10:00/)).toBeInTheDocument();
    });
  });

  it('should show confirmed status badge', async () => {
    render(<BookingsPage />);

    await waitFor(() => {
      expect(screen.getByText(/confirmée/i)).toBeInTheDocument();
    });
  });

  it('should show cancelled status badge', async () => {
    render(<BookingsPage />);

    await waitFor(() => {
      expect(screen.getByText(/annulée/i)).toBeInTheDocument();
    });
  });

  it('should show cancel button for confirmed bookings only', async () => {
    render(<BookingsPage />);

    await waitFor(() => {
      const cancelButtons = screen.getAllByRole('button', { name: /annuler/i });
      // Only one cancel button for the confirmed booking
      expect(cancelButtons).toHaveLength(1);
    });
  });

  it('should not show cancel button for cancelled bookings', async () => {
    render(<BookingsPage />);

    await waitFor(() => {
      const bookingCards = screen.getAllByTestId('booking-card');
      const cancelledCard = bookingCards[1]; // Second booking is cancelled
      
      expect(cancelledCard).not.toHaveTextContent(/annuler réservation/i);
    });
  });

  it('should handle booking cancellation', async () => {
    (apiService.cancelBooking as jest.Mock).mockResolvedValue({
      success: true,
      message: 'Booking cancelled',
    });

    render(<BookingsPage />);

    await waitFor(() => {
      const cancelButton = screen.getByRole('button', { name: /annuler/i });
      fireEvent.click(cancelButton);
    });

    // Confirm cancellation
    await waitFor(() => {
      const confirmButton = screen.getByRole('button', { name: /confirmer/i });
      fireEvent.click(confirmButton);
    });

    await waitFor(() => {
      expect(apiService.cancelBooking).toHaveBeenCalledWith('booking-1');
    });
  });

  it('should show confirmation dialog before cancelling', async () => {
    render(<BookingsPage />);

    await waitFor(() => {
      const cancelButton = screen.getByRole('button', { name: /annuler/i });
      fireEvent.click(cancelButton);
    });

    await waitFor(() => {
      expect(screen.getByText(/êtes-vous sûr/i)).toBeInTheDocument();
      expect(screen.getByRole('button', { name: /confirmer/i })).toBeInTheDocument();
      expect(screen.getByRole('button', { name: /annuler/i })).toBeInTheDocument();
    });
  });

  it('should refresh bookings after cancellation', async () => {
    (apiService.cancelBooking as jest.Mock).mockResolvedValue({
      success: true,
    });

    render(<BookingsPage />);

    await waitFor(() => {
      const cancelButton = screen.getByRole('button', { name: /annuler/i });
      fireEvent.click(cancelButton);
    });

    const confirmButton = await screen.findByRole('button', { name: /confirmer/i });
    fireEvent.click(confirmButton);

    await waitFor(() => {
      // getBookings should be called again (initial + after cancel)
      expect(apiService.getBookings).toHaveBeenCalledTimes(2);
    });
  });

  it('should show error message on cancellation failure', async () => {
    (apiService.cancelBooking as jest.Mock).mockRejectedValue({
      message: 'Cancellation failed',
    });

    render(<BookingsPage />);

    await waitFor(() => {
      const cancelButton = screen.getByRole('button', { name: /annuler/i });
      fireEvent.click(cancelButton);
    });

    const confirmButton = await screen.findByRole('button', { name: /confirmer/i });
    fireEvent.click(confirmButton);

    await waitFor(() => {
      expect(screen.getByText(/cancellation failed/i)).toBeInTheDocument();
    });
  });

  it('should show empty state when no bookings', async () => {
    (apiService.getBookings as jest.Mock).mockResolvedValue({
      success: true,
      data: { items: [], total: 0, pages: 0 },
    });

    render(<BookingsPage />);

    await waitFor(() => {
      expect(screen.getByText(/aucune réservation/i)).toBeInTheDocument();
    });
  });

  it('should show loading state initially', () => {
    (apiService.getBookings as jest.Mock).mockImplementation(
      () => new Promise(() => {})
    );

    render(<BookingsPage />);

    expect(screen.getByText(/chargement/i)).toBeInTheDocument();
  });

  it('should redirect to login if not authenticated', async () => {
    (useAuth as jest.Mock).mockReturnValue({
      user: null,
      loading: false,
    });

    render(<BookingsPage />);

    await waitFor(() => {
      expect(mockPush).toHaveBeenCalledWith('/login');
    });
  });

  it('should paginate bookings', async () => {
    const paginatedMockBookings = {
      ...mockBookings,
      data: { ...mockBookings.data, pages: 3, currentPage: 1 },
    };
    (apiService.getBookings as jest.Mock).mockResolvedValue(paginatedMockBookings);

    render(<BookingsPage />);

    await waitFor(() => {
      expect(screen.getByText(/page 1/i)).toBeInTheDocument();
    });

    const nextButton = screen.getByRole('button', { name: /suivant/i });
    fireEvent.click(nextButton);

    expect(apiService.getBookings).toHaveBeenCalledWith(2, 20);
  });

  it('should display booking creation date', async () => {
    render(<BookingsPage />);

    await waitFor(() => {
      expect(screen.getByText(/16 déc/i)).toBeInTheDocument();
      expect(screen.getByText(/15 déc/i)).toBeInTheDocument();
    });
  });
});

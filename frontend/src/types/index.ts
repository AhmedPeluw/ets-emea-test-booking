export interface User {
  id: string;
  name: string;
  email: string;
  roles?: string[];
}

export interface Session {
  id: string;
  language: string;
  date: string;
  time: string;
  location: string;
  totalSeats: number;
  availableSeats: number;
  description?: string;
  level?: string;
  durationMinutes?: number;
  price?: number;
  isActive: boolean;
}

export interface Booking {
  id: string;
  sessionId: string;
  status: string;
  createdAt: string;
  updatedAt: string;
  session?: Session;
}

export interface PaginationData {
  total: number;
  pages: number;
  currentPage: number;
  itemsPerPage: number;
}

export interface ApiResponse<T> {
  success: boolean;
  data?: T;
  message?: string;
  errors?: Record<string, string>;
  pagination?: PaginationData;
}

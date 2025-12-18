'use client';

import { useEffect, useState } from 'react';
import { useAuth } from '@/contexts/AuthContext';
import { useRouter } from 'next/navigation';
import Link from 'next/link';
import { Booking } from '@/types';
import { apiService } from '@/services/api';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Calendar, Clock, MapPin, ArrowLeft, Loader2, XCircle, CheckCircle2, BookOpen } from 'lucide-react';

export default function BookingsPage() {
  const { user, loading: authLoading } = useAuth();
  const router = useRouter();
  const [bookings, setBookings] = useState<Booking[]>([]);
  const [loading, setLoading] = useState(true);
  const [cancellingId, setCancellingId] = useState<string | null>(null);

  useEffect(() => {
    if (!authLoading && !user) {
      router.push('/login');
    }
  }, [user, authLoading, router]);

  useEffect(() => {
    if (user) {
      loadBookings();
    }
  }, [user]);

  const loadBookings = async () => {
    try {
      const response = await apiService.getBookings(1, 20);
      if (response.success && response.data) {
        setBookings(response.data);
      }
    } catch (error) {
      console.error('Erreur chargement réservations:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleCancelBooking = async (bookingId: string) => {
    if (!confirm('Êtes-vous sûr de vouloir annuler cette réservation ?')) {
      return;
    }

    setCancellingId(bookingId);
    try {
      await apiService.cancelBooking(bookingId);
      await loadBookings();
    } catch (error: any) {
      alert(error.response?.data?.message || 'Erreur lors de l\'annulation');
    } finally {
      setCancellingId(null);
    }
  };

  const getStatusBadge = (status: string) => {
    const variants: Record<string, { variant: 'default' | 'secondary' | 'destructive' | 'outline'; label: string }> = {
      confirmed: { variant: 'default', label: 'Confirmée' },
      pending: { variant: 'secondary', label: 'En attente' },
      cancelled: { variant: 'destructive', label: 'Annulée' },
      completed: { variant: 'outline', label: 'Complétée' },
    };

    const config = variants[status] || { variant: 'outline' as const, label: status };

    return <Badge variant={config.variant}>{config.label}</Badge>;
  };

  if (authLoading || loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <Loader2 className="h-8 w-8 animate-spin text-primary" />
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50">
      {/* Header */}
      <header className="border-b bg-white/80 backdrop-blur-md sticky top-0 z-50 shadow-sm">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8 py-4">
          <div className="flex items-center justify-between">
            <Button variant="ghost" asChild>
              <Link href="/dashboard">
                <ArrowLeft className="mr-2 h-4 w-4" />
                Retour au tableau de bord
              </Link>
            </Button>
            <div className="flex items-center space-x-3">
              <div className="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-lg">
                <BookOpen className="w-6 h-6 text-white" />
              </div>
              <h1 className="text-xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
                ETS EMEA
              </h1>
            </div>
          </div>
        </div>
      </header>

      {/* Main Content */}
      <main className="container mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div className="mb-8">
          <h2 className="text-3xl font-bold mb-2">Mes réservations</h2>
          <p className="text-muted-foreground">
            Gérez toutes vos réservations de sessions de test
          </p>
        </div>

        {bookings.length === 0 ? (
          <Card className="text-center py-16">
            <CardContent>
              <Calendar className="h-16 w-16 mx-auto text-muted-foreground mb-4" />
              <h3 className="text-xl font-semibold mb-2">Aucune réservation</h3>
              <p className="text-muted-foreground mb-6">
                Vous n'avez pas encore réservé de session de test
              </p>
              <Button asChild>
                <Link href="/dashboard">
                  <BookOpen className="mr-2 h-4 w-4" />
                  Découvrir les sessions
                </Link>
              </Button>
            </CardContent>
          </Card>
        ) : (
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {bookings.map((booking) => (
              <Card 
                key={booking.id} 
                className="hover:shadow-lg transition-all duration-300 overflow-hidden"
              >
                <div className={`h-2 ${
                  booking.status === 'confirmed' ? 'bg-green-500' :
                  booking.status === 'cancelled' ? 'bg-red-500' :
                  'bg-gray-500'
                }`} />
                <CardHeader>
                  <div className="flex items-start justify-between">
                    <div className="flex-1">
                      <CardTitle className="text-xl flex items-center gap-2 mb-1">
                        {booking.session?.language || 'Session'}
                      </CardTitle>
                      <div className="flex items-center gap-2 mt-2">
                        {getStatusBadge(booking.status)}
                        {booking.session?.level && (
                          <Badge variant="outline">{booking.session.level}</Badge>
                        )}
                      </div>
                    </div>
                  </div>
                </CardHeader>
                <CardContent className="space-y-4">
                  {booking.session && (
                    <div className="space-y-3">
                      <div className="flex items-center text-sm">
                        <Calendar className="h-4 w-4 mr-3 text-primary" />
                        <span className="font-medium">{booking.session.date}</span>
                      </div>
                      <div className="flex items-center text-sm">
                        <Clock className="h-4 w-4 mr-3 text-primary" />
                        <span>{booking.session.time}</span>
                      </div>
                      <div className="flex items-center text-sm">
                        <MapPin className="h-4 w-4 mr-3 text-primary" />
                        <span>{booking.session.location}</span>
                      </div>
                    </div>
                  )}

                  <div className="pt-4 border-t flex items-center justify-between">
                    <div className="text-xs text-muted-foreground">
                      Réservé le {new Date(booking.createdAt).toLocaleDateString('fr-FR')}
                    </div>

                    {booking.status === 'confirmed' && (
                      <Button
                        variant="destructive"
                        size="sm"
                        onClick={() => handleCancelBooking(booking.id)}
                        disabled={cancellingId === booking.id}
                      >
                        {cancellingId === booking.id ? (
                          <>
                            <Loader2 className="mr-2 h-3 w-3 animate-spin" />
                            Annulation...
                          </>
                        ) : (
                          <>
                            <XCircle className="mr-2 h-3 w-3" />
                            Annuler
                          </>
                        )}
                      </Button>
                    )}

                    {booking.status === 'cancelled' && (
                      <div className="flex items-center text-sm text-destructive">
                        <XCircle className="h-4 w-4 mr-1" />
                        Annulée
                      </div>
                    )}

                    {booking.status === 'completed' && (
                      <div className="flex items-center text-sm text-green-600">
                        <CheckCircle2 className="h-4 w-4 mr-1" />
                        Complétée
                      </div>
                    )}
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        )}
      </main>
    </div>
  );
}

'use client';

import { useEffect, useState } from 'react';
import { useAuth } from '@/contexts/AuthContext';
import { useRouter } from 'next/navigation';
import { Session } from '@/types';
import { apiService } from '@/services/api';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { 
  BookOpen, Calendar, Clock, MapPin, Users, LogOut, User, 
  GraduationCap, Loader2, ArrowRight, CheckCircle2 
} from 'lucide-react';

export default function DashboardPage() {
  const { user, loading, logout } = useAuth();
  const router = useRouter();
  const [sessions, setSessions] = useState<Session[]>([]);
  const [loadingSessions, setLoadingSessions] = useState(true);
  const [bookingSession, setBookingSession] = useState<string | null>(null);

  useEffect(() => {
    if (!loading && !user) {
      router.push('/login');
    }
  }, [user, loading, router]);

  useEffect(() => {
    if (user) {
      loadSessions();
    }
  }, [user]);

  const loadSessions = async () => {
    try {
      const response = await apiService.getSessions(1, 6);
      if (response.success && response.data) {
        setSessions(response.data);
      }
    } catch (error) {
      console.error('Erreur chargement sessions:', error);
    } finally {
      setLoadingSessions(false);
    }
  };

  const handleBookSession = async (sessionId: string) => {
    setBookingSession(sessionId);
    try {
      await apiService.createBooking(sessionId);
      router.push('/bookings');
    } catch (error: any) {
      alert(error.response?.data?.message || 'Erreur lors de la réservation');
      setBookingSession(null);
    }
  };

  const getLanguageFlag = (language: string) => {
    const flags: Record<string, string> = {
      'Anglais': '🇬🇧',
      'Français': '🇫🇷',
      'Espagnol': '🇪🇸',
      'Allemand': '🇩🇪',
      'Italien': '🇮🇹',
      'Portugais': '🇵🇹',
      'Chinois': '🇨🇳',
      'Japonais': '🇯🇵',
      'Arabe': '🇸🇦'
    };
    return flags[language] || '🌍';
  };

  if (loading || !user) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <Loader2 className="h-8 w-8 animate-spin text-primary" />
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50">
      {/* Header/Navbar */}
      <header className="border-b bg-white/80 backdrop-blur-md sticky top-0 z-50 shadow-sm">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between items-center h-16">
            <div className="flex items-center space-x-3">
              <div className="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-lg">
                <BookOpen className="w-6 h-6 text-white" />
              </div>
              <div>
                <h1 className="text-xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
                  ETS EMEA
                </h1>
                <p className="text-xs text-muted-foreground">Test Booking</p>
              </div>
            </div>

            <div className="flex items-center space-x-4">
              <Button variant="ghost" size="sm" onClick={() => router.push('/bookings')}>
                <Calendar className="h-4 w-4 mr-2" />
                Mes réservations
              </Button>
              
              <Button variant="ghost" size="sm" onClick={() => router.push('/profile')}>
                <User className="h-4 w-4 mr-2" />
                Profil
              </Button>

              <div className="flex items-center space-x-3 pl-4 border-l">
                <Avatar className="h-9 w-9">
                  <AvatarFallback className="bg-gradient-to-br from-blue-600 to-indigo-600 text-white font-semibold">
                    {user.name.substring(0, 2).toUpperCase()}
                  </AvatarFallback>
                </Avatar>
                <div className="hidden md:block">
                  <p className="text-sm font-medium">{user.name}</p>
                  <p className="text-xs text-muted-foreground">{user.email}</p>
                </div>
                <Button variant="ghost" size="icon" onClick={logout} title="Déconnexion">
                  <LogOut className="h-4 w-4" />
                </Button>
              </div>
            </div>
          </div>
        </div>
      </header>

      {/* Hero Section */}
      <div className="bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-16">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
          <div className="max-w-3xl">
            <h2 className="text-4xl font-bold mb-4">
              Bienvenue, {user.name} ! 👋
            </h2>
            <p className="text-xl text-blue-100 mb-6">
              Réservez votre session de test de langue en quelques clics
            </p>
            <div className="flex flex-wrap gap-4">
              <Button 
                variant="secondary" 
                size="lg"
                className="shadow-lg hover:shadow-xl transition-all"
                onClick={() => document.getElementById('sessions')?.scrollIntoView({ behavior: 'smooth' })}
              >
                <BookOpen className="mr-2 h-5 w-5" />
                Voir les sessions
              </Button>
              <Button 
                variant="outline" 
                size="lg"
                className="bg-white/10 border-white/20 text-white hover:bg-white/20"
                onClick={() => router.push('/bookings')}
              >
                <Calendar className="mr-2 h-5 w-5" />
                Mes réservations
              </Button>
            </div>
          </div>
        </div>
      </div>

      {/* Main Content */}
      <main className="container mx-auto px-4 sm:px-6 lg:px-8 py-12" id="sessions">
        <div className="flex items-center justify-between mb-8">
          <div>
            <h3 className="text-3xl font-bold mb-2">Sessions disponibles</h3>
            <p className="text-muted-foreground">
              Choisissez parmi les prochaines sessions de test
            </p>
          </div>
          <Button variant="outline" onClick={() => router.push('/sessions')}>
            Voir toutes les sessions
            <ArrowRight className="ml-2 h-4 w-4" />
          </Button>
        </div>

        {loadingSessions ? (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {[1, 2, 3].map((i) => (
              <Card key={i} className="animate-pulse">
                <CardHeader>
                  <div className="h-6 bg-muted rounded w-3/4" />
                  <div className="h-4 bg-muted rounded w-1/2 mt-2" />
                </CardHeader>
                <CardContent>
                  <div className="space-y-3">
                    <div className="h-4 bg-muted rounded" />
                    <div className="h-4 bg-muted rounded" />
                    <div className="h-4 bg-muted rounded w-2/3" />
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        ) : sessions.length === 0 ? (
          <Card className="text-center py-16">
            <CardContent>
              <BookOpen className="h-16 w-16 mx-auto text-muted-foreground mb-4" />
              <h4 className="text-xl font-semibold mb-2">Aucune session disponible</h4>
              <p className="text-muted-foreground">
                Revenez plus tard pour voir les nouvelles sessions
              </p>
            </CardContent>
          </Card>
        ) : (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {sessions.map((session) => (
              <Card 
                key={session.id} 
                className="hover:shadow-xl transition-all duration-300 border-0 shadow-lg overflow-hidden group"
              >
                <div className="h-2 bg-gradient-to-r from-blue-600 to-indigo-600" />
                <CardHeader className="pb-4">
                  <div className="flex items-start justify-between">
                    <div className="flex-1">
                      <CardTitle className="text-xl flex items-center gap-2">
                        <span className="text-2xl">{getLanguageFlag(session.language)}</span>
                        {session.language}
                      </CardTitle>
                      <CardDescription className="mt-1">
                        {session.description || 'Test de langue'}
                      </CardDescription>
                    </div>
                    {session.level && (
                      <Badge variant="secondary" className="ml-2">
                        <GraduationCap className="w-3 h-3 mr-1" />
                        {session.level}
                      </Badge>
                    )}
                  </div>
                </CardHeader>
                <CardContent className="space-y-3 pb-4">
                  <div className="flex items-center text-sm text-muted-foreground">
                    <Calendar className="h-4 w-4 mr-2 text-primary" />
                    <span className="font-medium">{session.date}</span>
                  </div>
                  <div className="flex items-center text-sm text-muted-foreground">
                    <Clock className="h-4 w-4 mr-2 text-primary" />
                    <span>{session.time}</span>
                    {session.durationMinutes && (
                      <span className="ml-2">({session.durationMinutes} min)</span>
                    )}
                  </div>
                  <div className="flex items-center text-sm text-muted-foreground">
                    <MapPin className="h-4 w-4 mr-2 text-primary" />
                    <span>{session.location}</span>
                  </div>
                  <div className="flex items-center justify-between pt-2 border-t">
                    <div className="flex items-center text-sm">
                      <Users className="h-4 w-4 mr-2 text-primary" />
                      <span className="font-semibold text-foreground">
                        {session.availableSeats}/{session.totalSeats}
                      </span>
                      <span className="text-muted-foreground ml-1">places</span>
                    </div>
                    {session.price !== undefined && session.price > 0 && (
                      <Badge variant="outline" className="font-semibold">
                        {session.price}€
                      </Badge>
                    )}
                  </div>
                </CardContent>
                <CardFooter>
                  <Button
                    className="w-full shadow-md group-hover:shadow-lg transition-all"
                    onClick={() => handleBookSession(session.id)}
                    disabled={session.availableSeats === 0 || bookingSession === session.id}
                  >
                    {bookingSession === session.id ? (
                      <>
                        <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                        Réservation...
                      </>
                    ) : session.availableSeats === 0 ? (
                      'Complet'
                    ) : (
                      <>
                        <CheckCircle2 className="mr-2 h-4 w-4" />
                        Réserver maintenant
                      </>
                    )}
                  </Button>
                </CardFooter>
              </Card>
            ))}
          </div>
        )}
      </main>

      {/* Footer */}
      <footer className="border-t bg-white/80 backdrop-blur-md mt-20 py-8">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8 text-center text-sm text-muted-foreground">
          <p>© 2024 ETS EMEA. Tous droits réservés.</p>
        </div>
      </footer>
    </div>
  );
}

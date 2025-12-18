'use client';

import { useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/contexts/AuthContext';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { BookOpen, Calendar, CheckCircle2, Globe, Users, ArrowRight } from 'lucide-react';

export default function Home() {
  const { user, loading } = useAuth();
  const router = useRouter();

  useEffect(() => {
    // Redirect to dashboard if already logged in
    if (!loading && user) {
      router.push('/dashboard');
    }
  }, [user, loading, router]);

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 via-white to-indigo-50">
        <div className="text-center">
          <div className="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-2xl mb-4 shadow-lg animate-pulse">
            <BookOpen className="w-8 h-8 text-white" />
          </div>
          <p className="text-gray-600">Chargement...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50">
      {/* Decorative elements */}
      <div className="absolute inset-0 overflow-hidden pointer-events-none">
        <div className="absolute -top-40 -right-40 w-80 h-80 bg-blue-400/20 rounded-full blur-3xl" />
        <div className="absolute -bottom-40 -left-40 w-80 h-80 bg-indigo-400/20 rounded-full blur-3xl" />
      </div>

      {/* Header */}
      <header className="relative z-10 border-b bg-white/80 backdrop-blur-md">
        <div className="container mx-auto px-6 py-4">
          <div className="flex items-center justify-between">
            <div className="flex items-center space-x-3">
              <div className="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-lg">
                <BookOpen className="w-6 h-6 text-white" />
              </div>
              <div>
                <h1 className="text-xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
                  ETS EMEA
                </h1>
                <p className="text-xs text-gray-600">Test Booking Platform</p>
              </div>
            </div>
            <div className="flex gap-3">
              <Button variant="ghost" onClick={() => router.push('/login')}>
                Se connecter
              </Button>
              <Button onClick={() => router.push('/register')} className="shadow-lg">
                S'inscrire
                <ArrowRight className="ml-2 h-4 w-4" />
              </Button>
            </div>
          </div>
        </div>
      </header>

      {/* Hero Section */}
      <main className="relative z-10">
        <section className="container mx-auto px-6 py-20">
          <div className="text-center max-w-4xl mx-auto mb-16">
            <h2 className="text-5xl font-bold mb-6 bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
              Réservez vos sessions de test de langue
            </h2>
            <p className="text-xl text-gray-600 mb-8">
              Plateforme moderne et intuitive pour gérer vos réservations de tests de langues
            </p>
            <div className="flex gap-4 justify-center">
              <Button size="lg" onClick={() => router.push('/register')} className="shadow-xl">
                <CheckCircle2 className="mr-2 h-5 w-5" />
                Commencer gratuitement
              </Button>
              <Button size="lg" variant="outline" onClick={() => router.push('/login')}>
                Se connecter
              </Button>
            </div>
          </div>

          {/* Features Grid */}
          <div className="grid md:grid-cols-3 gap-8 mb-20">
            <Card className="border-0 shadow-lg hover:shadow-xl transition-all">
              <CardContent className="p-8 text-center">
                <div className="inline-flex items-center justify-center w-16 h-16 bg-blue-100 rounded-2xl mb-4">
                  <Calendar className="w-8 h-8 text-blue-600" />
                </div>
                <h3 className="text-xl font-semibold mb-2">Réservation simple</h3>
                <p className="text-gray-600">
                  Réservez votre session en quelques clics avec notre interface intuitive
                </p>
              </CardContent>
            </Card>

            <Card className="border-0 shadow-lg hover:shadow-xl transition-all">
              <CardContent className="p-8 text-center">
                <div className="inline-flex items-center justify-center w-16 h-16 bg-indigo-100 rounded-2xl mb-4">
                  <Globe className="w-8 h-8 text-indigo-600" />
                </div>
                <h3 className="text-xl font-semibold mb-2">Plusieurs langues</h3>
                <p className="text-gray-600">
                  Tests disponibles pour l'anglais, français, espagnol, allemand et plus
                </p>
              </CardContent>
            </Card>

            <Card className="border-0 shadow-lg hover:shadow-xl transition-all">
              <CardContent className="p-8 text-center">
                <div className="inline-flex items-center justify-center w-16 h-16 bg-purple-100 rounded-2xl mb-4">
                  <Users className="w-8 h-8 text-purple-600" />
                </div>
                <h3 className="text-xl font-semibold mb-2">Centres certifiés</h3>
                <p className="text-gray-600">
                  Réseau de centres de test ETS EMEA dans toute l'Europe
                </p>
              </CardContent>
            </Card>
          </div>

          {/* Stats */}
          <div className="bg-white rounded-2xl shadow-xl p-12">
            <div className="grid md:grid-cols-3 gap-8 text-center">
              <div>
                <div className="text-4xl font-bold text-blue-600 mb-2">1000+</div>
                <div className="text-gray-600">Sessions disponibles</div>
              </div>
              <div>
                <div className="text-4xl font-bold text-indigo-600 mb-2">50+</div>
                <div className="text-gray-600">Centres de test</div>
              </div>
              <div>
                <div className="text-4xl font-bold text-purple-600 mb-2">10+</div>
                <div className="text-gray-600">Langues testées</div>
              </div>
            </div>
          </div>

          {/* CTA Section */}
          <div className="text-center mt-20">
            <h3 className="text-3xl font-bold mb-4">Prêt à commencer ?</h3>
            <p className="text-xl text-gray-600 mb-8">
              Créez votre compte en moins d'une minute
            </p>
            <Button size="lg" onClick={() => router.push('/register')} className="shadow-xl">
              <CheckCircle2 className="mr-2 h-5 w-5" />
              Créer mon compte gratuitement
            </Button>
          </div>
        </section>
      </main>

      {/* Footer */}
      <footer className="relative z-10 border-t bg-white/80 backdrop-blur-md mt-20 py-8">
        <div className="container mx-auto px-6 text-center text-sm text-gray-600">
          <p>© 2024 ETS EMEA. Tous droits réservés.</p>
          <div className="mt-2 space-x-4">
            <a href="#" className="hover:text-blue-600">Conditions d'utilisation</a>
            <span>•</span>
            <a href="#" className="hover:text-blue-600">Politique de confidentialité</a>
            <span>•</span>
            <a href="#" className="hover:text-blue-600">Contact</a>
          </div>
        </div>
      </footer>
    </div>
  );
}

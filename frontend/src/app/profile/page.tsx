'use client';

import { useEffect, useState } from 'react';
import { useAuth } from '@/contexts/AuthContext';
import { useRouter } from 'next/navigation';
import Link from 'next/link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { ArrowLeft, Loader2, Save, BookOpen } from 'lucide-react';

export default function ProfilePage() {
  const { user, loading: authLoading, updateUser } = useAuth();
  const router = useRouter();
  const [formData, setFormData] = useState({
    name: '',
    email: '',
  });
  const [loading, setLoading] = useState(false);
  const [message, setMessage] = useState('');
  const [error, setError] = useState('');

  useEffect(() => {
    if (!authLoading && !user) {
      router.push('/login');
    } else if (user) {
      setFormData({
        name: user.name,
        email: user.email,
      });
    }
  }, [user, authLoading, router]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setMessage('');
    setError('');
    setLoading(true);

    try {
      await updateUser(formData);
      setMessage('Profil mis à jour avec succès !');
      setTimeout(() => setMessage(''), 3000); // Effacer après 3s
    } catch (err: any) {
      // Gérer le cas du token expiré
      if (err.response?.status === 401) {
        setError('Session expirée. Vous allez être redirigé...');
        setTimeout(() => router.push('/login'), 2000);
      } else {
        setError(err.message || 'Erreur lors de la mise à jour');
      }
    } finally {
      setLoading(false);
    }
  };

  // Afficher un loader pendant le chargement initial
  if (authLoading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 via-white to-indigo-50">
        <div className="text-center">
          <Loader2 className="h-12 w-12 animate-spin text-primary mx-auto mb-4" />
          <p className="text-muted-foreground">Chargement de votre profil...</p>
        </div>
      </div>
    );
  }

  if (!user) {
    return null; // Le redirect se fera via useEffect
  }

  return (
    <div className="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50">
      {/* Header */}
      <header className="border-b bg-white/80 backdrop-blur-md sticky top-0 z-50 shadow-sm">
        <div className="container mx-auto px-6 py-4">
          <div className="flex items-center justify-between">
            <Button variant="ghost" asChild>
              <Link href="/dashboard">
                <ArrowLeft className="mr-2 h-4 w-4" />
                Retour
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
      <main className="container mx-auto px-6 py-12 max-w-2xl">
        <div className="mb-8 text-center animate-fade-in">
          <Avatar className="h-24 w-24 mx-auto mb-4">
            <AvatarFallback className="bg-gradient-to-br from-blue-600 to-indigo-600 text-white text-2xl font-bold">
              {user.name.substring(0, 2).toUpperCase()}
            </AvatarFallback>
          </Avatar>
          <h2 className="text-3xl font-bold mb-2">{user.name}</h2>
          <p className="text-muted-foreground">{user.email}</p>
        </div>

        <Card className="shadow-xl border-0 animate-fade-in">
          <CardHeader>
            <CardTitle>Modifier mon profil</CardTitle>
            <CardDescription>
              Mettez à jour vos informations personnelles
            </CardDescription>
          </CardHeader>
          <CardContent>
            {message && (
              <div className="mb-6 p-4 rounded-lg bg-green-100 border border-green-200 text-green-700 animate-fade-in">
                ✅ {message}
              </div>
            )}

            {error && (
              <div className="mb-6 p-4 rounded-lg bg-destructive/10 border border-destructive/20 text-destructive animate-fade-in">
                ❌ {error}
              </div>
            )}

            <form onSubmit={handleSubmit} className="space-y-6">
              <div className="space-y-2">
                <Label htmlFor="name">Nom complet</Label>
                <Input
                  id="name"
                  type="text"
                  value={formData.name}
                  onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                  required
                  disabled={loading}
                  minLength={2}
                  placeholder="Votre nom complet"
                />
              </div>

              <div className="space-y-2">
                <Label htmlFor="email">Email</Label>
                <Input
                  id="email"
                  type="email"
                  value={formData.email}
                  onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                  required
                  disabled={loading}
                  placeholder="votre@email.com"
                />
                <p className="text-xs text-muted-foreground">
                  L'email est utilisé pour la connexion
                </p>
              </div>

              <Button 
                type="submit" 
                className="w-full h-11 text-base"
                disabled={loading}
              >
                {loading ? (
                  <>
                    <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                    Enregistrement en cours...
                  </>
                ) : (
                  <>
                    <Save className="mr-2 h-4 w-4" />
                    Enregistrer les modifications
                  </>
                )}
              </Button>
            </form>
          </CardContent>
        </Card>

        {/* Info supplémentaires */}
        <div className="mt-6 text-center text-sm text-muted-foreground animate-fade-in">
          <p>💡 Vos données sont sécurisées et ne seront jamais partagées</p>
        </div>
      </main>
    </div>
  );
}

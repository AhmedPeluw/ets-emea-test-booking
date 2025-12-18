# Frontend ETS EMEA - Design Moderne avec shadcn/ui

## 🎨 Design System

Ce frontend utilise **shadcn/ui** - une collection de composants réutilisables, accessibles et modernes construits avec Radix UI et Tailwind CSS.

### Caractéristiques du design

✨ **Interface Moderne et Épurée**
- Design minimaliste et professionnel
- Palette de couleurs cohérente (Bleu/Indigo)
- Animations fluides et subtiles
- Typographie élégante avec Geist Sans & Mono

🎯 **Expérience Utilisateur Optimale**
- Navigation intuitive
- Feedback visuel immédiat
- États de chargement clairs
- Messages d'erreur explicites

📱 **Responsive Design**
- Adapté à tous les écrans (mobile, tablette, desktop)
- Composants flexibles et adaptatifs
- Grid system moderne

♿ **Accessibilité**
- Composants Radix UI conformes ARIA
- Navigation au clavier
- Contraste de couleurs optimisé
- Labels et descriptions appropriés

## 🏗️ Architecture

### Technologies

- **Framework**: Next.js 14 (App Router)
- **UI Library**: shadcn/ui
- **Styling**: Tailwind CSS
- **Icons**: Lucide React
- **Fonts**: Geist Sans & Geist Mono
- **State Management**: React Context API
- **HTTP Client**: Axios
- **Type Safety**: TypeScript

### Structure des composants

```
src/
├── app/                      # Pages (App Router)
│   ├── login/               # Page de connexion
│   ├── register/            # Page d'inscription
│   ├── dashboard/           # Tableau de bord principal
│   ├── bookings/            # Gestion des réservations
│   └── layout.tsx           # Layout global
├── components/              
│   └── ui/                  # Composants shadcn/ui
│       ├── button.tsx
│       ├── card.tsx
│       ├── input.tsx
│       ├── label.tsx
│       ├── badge.tsx
│       └── avatar.tsx
├── contexts/                # Contexts React
│   └── AuthContext.tsx     # Context d'authentification
├── services/                # Services API
│   └── api.ts              # Client Axios
├── types/                   # Types TypeScript
│   └── index.ts            # Types globaux
└── lib/                     # Utilitaires
    └── utils.ts            # Fonction cn() pour Tailwind
```

## 🎨 Système de Design

### Couleurs

Le thème utilise une palette Bleu/Indigo moderne:

- **Primary**: `hsl(221.2, 83.2%, 53.3%)` - Bleu vif
- **Secondary**: `hsl(210, 40%, 96.1%)` - Gris clair
- **Accent**: Gradients Bleu → Indigo
- **Destructive**: Rouge pour actions dangereuses
- **Muted**: Textes secondaires

### Typographie

- **Font principale**: Geist Sans (moderne, lisible)
- **Font mono**: Geist Mono (pour le code)
- **Hiérarchie**: 
  - Titres: Font-bold, tailles xl-4xl
  - Corps: Font-normal, taille base
  - Labels: Font-medium, taille sm

### Espacements

- Container: `max-w-7xl` avec padding responsive
- Cards: Padding `p-6`
- Spacing: Scale de 4px (4, 8, 12, 16, 24, 32...)

### Animations

- **Fade-in**: Entrée douce des éléments
- **Slide-in**: Déplacement latéral
- **Hover states**: Effets au survol
- **Loading states**: Spinners et skeleton loaders

## 🧩 Composants Principaux

### Button
- Variantes: default, destructive, outline, ghost, link
- Tailles: sm, default, lg, icon
- États: normal, hover, disabled, loading

### Card
- Structure: Header, Content, Footer
- Shadow et border subtils
- Hover effects
- Responsive padding

### Input
- Border et ring au focus
- Placeholder stylisé
- États disabled et error
- Intégration avec Label

### Badge
- Variantes colorées
- Tailles adaptatives
- Pour statuts et tags

### Avatar
- Fallback avec initiales
- Gradients personnalisables
- Rond parfait

## 🚀 Démarrage

### Installation des dépendances

```bash
npm install
```

### Lancer en développement

```bash
npm run dev
```

L'application sera accessible sur http://localhost:3000

### Build de production

```bash
npm run build
npm start
```

## 📄 Pages

### Login (`/login`)
- Authentification JWT
- Validation des champs
- Feedback d'erreur
- Lien vers inscription
- Design élégant avec fond gradienté

### Register (`/register`)
- Formulaire d'inscription complet
- Validation côté client
- Confirmation de mot de passe
- Liste des avantages
- Design cohérent avec login

### Dashboard (`/dashboard`)
- Vue d'ensemble des sessions
- Cards avec informations détaillées
- Réservation en un clic
- Navigation intuitive
- Header sticky avec actions rapides

### Bookings (`/bookings`)
- Liste des réservations
- Statuts visuels (badges colorés)
- Annulation facile
- Filtres et tri
- Vue détaillée par booking

## 🎯 Bonnes Pratiques Implémentées

### Code Quality

✅ **TypeScript strict** - Type safety complet
✅ **ESLint configuré** - Code consistant
✅ **Composants modulaires** - Réutilisabilité
✅ **Props typées** - Documentation implicite

### Performance

✅ **Code splitting** - Chargement optimisé
✅ **Lazy loading** - Images et composants
✅ **Memoization** - Éviter re-renders inutiles
✅ **Optimized builds** - Next.js optimization

### UX/UI

✅ **Loading states** - Feedback immédiat
✅ **Error handling** - Messages clairs
✅ **Success feedback** - Confirmation visuelle
✅ **Responsive design** - Mobile-first

### Accessibilité

✅ **Semantic HTML** - Structure correcte
✅ **ARIA labels** - Screen readers
✅ **Keyboard navigation** - Tab index
✅ **Color contrast** - WCAG AA

## 🎨 Personnalisation

### Modifier les couleurs

Éditez `src/app/globals.css`:

```css
:root {
  --primary: 221.2 83.2% 53.3%;  /* Votre couleur */
  --secondary: 210 40% 96.1%;    /* Votre couleur */
  /* ... autres variables */
}
```

### Ajouter des composants shadcn

```bash
# Exemple: ajouter le composant Dialog
npx shadcn-ui@latest add dialog
```

### Personnaliser Tailwind

Éditez `tailwind.config.js`:

```js
module.exports = {
  theme: {
    extend: {
      colors: {
        // Vos couleurs personnalisées
      },
    },
  },
}
```

## 📚 Ressources

- [shadcn/ui Documentation](https://ui.shadcn.com)
- [Next.js Documentation](https://nextjs.org/docs)
- [Tailwind CSS](https://tailwindcss.com)
- [Radix UI](https://www.radix-ui.com)
- [Lucide Icons](https://lucide.dev)

## 🐛 Troubleshooting

### Les composants ne s'affichent pas correctement

Vérifiez que Tailwind CSS est bien configuré:
```bash
npm run dev
# Inspectez les classes dans le navigateur
```

### Erreurs TypeScript

```bash
# Vérifier les types
npm run lint
```

### Problèmes de fonts

Les fonts Geist sont chargées automatiquement. Si elles ne s'affichent pas:
1. Vérifiez `layout.tsx`
2. Vérifiez l'import de `geist/font`

## 💡 Améliorations Futures

### Court terme
- [ ] Dark mode complet
- [ ] Animations plus poussées
- [ ] Toasts pour notifications
- [ ] Skeleton loaders partout

### Moyen terme
- [ ] Thème customizable
- [ ] Plus de variantes de composants
- [ ] Storybook pour documentation
- [ ] Tests E2E avec Cypress

### Long terme
- [ ] PWA capabilities
- [ ] Offline mode
- [ ] Multi-langue
- [ ] Advanced animations (Framer Motion)

---

**Design créé avec ❤️ utilisant shadcn/ui et Next.js 14**
